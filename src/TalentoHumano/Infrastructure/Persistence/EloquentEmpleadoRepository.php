<?php

namespace Src\TalentoHumano\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ContratoModel;

final class EloquentEmpleadoRepository implements EmpleadoRepositoryInterface
{
    public function findAllActive(): array
    {
        return EmpleadoModel::with([
            'persona',
            'persona.sexo',
            'contratoActivo',
            'contratoActivo.cargo',
            'contratoActivo.area',
            'contratoActivo.sede',
        ])
        ->where('estado_laboral', 'Activo')
        ->get()
        ->toArray();
    }

    public function findByIdWithDetails(int $id): ?array
    {
        $employee = EmpleadoModel::with([
            'persona',
            'persona.sexo',
            'persona.nacionalidad',
            'persona.pais',
            'persona.ciudad',
            'persona.ciudad.departamento',
            'persona.expedido',
            'caja',
            'pensiones',
            'contratoActivo.cargo',
            'contratoActivo.area',
            'contratoActivo.sede',
            'contratos.tipo',
            'contratos.area',
            'contratos.cargo',
            'contratos.sede',
            // CV Normalizado
            'persona.formacionPregrado.depto',
            'persona.formacionPostgrado.depto',
            'persona.experienciaDocente.depto',
            'persona.experienciaProfesional.depto',
            'persona.capacitaciones.depto',
            'persona.produccionIntelectual.depto',
            'persona.reconocimientos',
            'persona.idiomas',
        ])->find($id);

        return $employee ? $employee->toArray() : null;
    }

    public function isPersonAlreadyEmployee(string $personaId): bool
    {
        return EmpleadoModel::where('id_persona', $personaId)->exists();
    }

    public function createEmployeeWithContract(array $personaData, array $empleadoData, ?array $contratoData): array
    {
        return DB::transaction(function () use ($personaData, $empleadoData, $contratoData) {
            
            // 1. Upsert Persona
            /** @var PersonaModel $persona */
            $persona = PersonaModel::where('ci', $personaData['ci'])->first();
            $pData = [
                'primer_apellido'     => $personaData['primer_apellido'] ?? null,
                'segundo_apellido'    => $personaData['segundo_apellido'] ?? null,
                'nombres'             => $personaData['nombres'] ?? null,
                'ci'                  => $personaData['ci'],
                'complemento'         => $personaData['complemento'] ?? null,
                'id_ci_expedido'      => $personaData['id_ci_expedido'] ?? 1,
                'id_sexo'             => $personaData['id_sexo'] ?? 1,
                'celular_personal'    => $personaData['celular_personal'] ?? '',
                'correo_personal'     => $personaData['correo_personal'] ?? '',
                'estado_civil'        => $personaData['estado_civil'] ?? '',
                'id_nacionalidad'     => $personaData['id_nacionalidad'] ?? 1,
                'direccion_domicilio' => $personaData['direccion_domicilio'] ?? '',
                'id_ciudad'           => $personaData['id_ciudad'] ?? 1,
                'id_pais'             => $personaData['id_pais'] ?? 2,
                'activo'              => true,
            ];

            if ($persona) {
                $persona->update($pData);
            } else {
                $persona = PersonaModel::create($pData);
            }

            // 2. Create Empleado
            $empleado = EmpleadoModel::create([
                'id_persona'            => $persona->id,
                'celular_institucional' => $empleadoData['celular_institucional'] ?? null,
                'correo_institucional'  => $empleadoData['correo_institucional'] ?? null,
                'id_caja'               => $empleadoData['id_caja'] ?? null,
                'nro_matricula_seguro'  => $empleadoData['nro_matricula_seguro'] ?? null,
                'id_entidad_pensiones'  => $empleadoData['id_entidad_pensiones'] ?? null,
                'nro_nua_cua'           => $empleadoData['nro_nua_cua'] ?? null,
                'estado_laboral'        => 'Activo',
            ]);

            // 3. Create Contrato
            if (!empty($contratoData) && isset($contratoData['id_tipo_contrato'], $contratoData['id_area'], $contratoData['id_cargo'])) {
                ContratoModel::create([
                    'id_empleado'      => $empleado->id_empleado,
                    'id_tipo_contrato' => $contratoData['id_tipo_contrato'],
                    'id_area'          => $contratoData['id_area'],
                    'id_cargo'         => $contratoData['id_cargo'],
                    'salario'          => $contratoData['salario'] ?? null,
                    'fecha_inicio'     => $contratoData['fecha_inicio'] ?? null,
                    'fecha_fin'        => $contratoData['fecha_fin'] ?? null,
                    'id_sede'          => $contratoData['id_sede'] ?? null,
                    'estado_contrato'  => 'Activo',
                ]);
            }

            return $empleado->load(['persona', 'contratoActivo.cargo', 'contratoActivo.area'])->toArray();
        });
    }

    public function getDashboardStats(): array
    {
        $total = EmpleadoModel::where('estado_laboral', 'Activo')->count();

        // Por género
        $genderStats = DB::table('empleados')
            ->join('personas', 'empleados.id_persona', '=', 'personas.id')
            ->join('sexo', 'personas.id_sexo', '=', 'sexo.id_sexo')
            ->select('sexo.sexo as label', DB::raw('count(*) as value'))
            ->where('empleados.estado_laboral', 'Activo')
            ->groupBy('sexo.sexo')
            ->get();

        // Por Sede
        $sedeStats = DB::table('contratos')
            ->join('sedes', 'contratos.id_sede', '=', 'sedes.id_sede')
            ->select('sedes.nombre as label', DB::raw('count(*) as value'))
            ->where('contratos.estado_contrato', 'Activo')
            ->groupBy('sedes.nombre')
            ->get();

        // Por Área
        $areaStats = DB::table('contratos')
            ->join('areas', 'contratos.id_area', '=', 'areas.id_area')
            ->select('areas.nombre_area as label', DB::raw('count(*) as value'))
            ->where('contratos.estado_contrato', 'Activo')
            ->groupBy('areas.nombre_area')
            ->orderBy('value', 'desc')
            ->limit(5)
            ->get();

        return [
            'total_active' => $total,
            'genders'      => $genderStats,
            'sedes'        => $sedeStats,
            'areas'        => $areaStats,
            'recent'       => EmpleadoModel::with(['persona', 'contratoActivo.cargo'])->latest()->limit(5)->get()->toArray(),
        ];
    }

    public function findPersonaCvDetails(string $personaId): ?array
    {
        $persona = PersonaModel::with([
            'sexo', 'nacionalidad', 'ciudad', 'ciudad.departamento', 'pais', 'expedido', 'documentos',
            'formacionPregrado.depto', 'formacionPostgrado.depto',
            'experienciaDocente.depto', 'experienciaProfesional.depto',
            'capacitaciones.depto', 'produccionIntelectual.depto',
            'reconocimientos', 'idiomas',
        ])->find($personaId);

        if (!$persona) return null;

        $empleado = EmpleadoModel::where('id_persona', $persona->id)
            ->with(['contratoActivo.cargo', 'contratoActivo.area', 'caja', 'pensiones'])
            ->first();

        // Returning the eloquent models array-ified isn't strictly DDD but serves the view efficiently.
        // We will keep the object structure for internal passing to keep relations accessible.
        // Wait! The view generator uses $persona->primer_apellido, $empleado->contratoActivo... 
        // We must return objects here, or change the view. Let's return the standard array and let the PDF Service handle it, or we can just return the Model if we bend the rules for PDF templating.
        // Bending rules for PDF template rendering (passing models directly) avoids breaking the `cv/curriculum.blade.php`.
        return [
            'persona' => $persona,
            'empleado' => $empleado,
        ];
    }

    public function getAttachments(array $personaDetails): array
    {
        /** @var PersonaModel $persona */
        $persona = $personaDetails['persona'];
        $adjuntos = [];

        // Documentos personales (CI escaneado, etc.)
        if ($persona->documentos) {
            foreach ($persona->documentos as $doc) {
                $path = $doc->ruta_archivo ? public_path(str_replace('/storage/', 'storage/', $doc->ruta_archivo)) : null;
                $isImage = in_array(strtolower($doc->formato ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                
                $adjuntos[] = [
                    'label' => 'Documento: ' . strtoupper($doc->tipo ?? 'Otro'),
                    'type' => $isImage ? 'image' : 'pdf',
                    'path' => $path,
                    'filename' => $doc->nombre_archivo,
                    'original_path' => $doc->ruta_archivo,
                ];
            }
        }

        // Formación Pregrado - Diplomas y Títulos
        if ($persona->formacionPregrado) {
            foreach ($persona->formacionPregrado as $fp) {
                if ($fp->archivo_diploma) {
                    $path = public_path(str_replace('/storage/', 'storage/', $fp->archivo_diploma));
                    $ext = strtolower(pathinfo($fp->archivo_diploma, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Diploma Pregrado: ' . ($fp->carrera ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($fp->archivo_diploma),
                    ];
                }
                if ($fp->archivo_titulo) {
                    $path = public_path(str_replace('/storage/', 'storage/', $fp->archivo_titulo));
                    $ext = strtolower(pathinfo($fp->archivo_titulo, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Título en Provisión: ' . ($fp->carrera ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($fp->archivo_titulo),
                    ];
                }
            }
        }

        // Formación Postgrado
        if ($persona->formacionPostgrado) {
            foreach ($persona->formacionPostgrado as $fpo) {
                if ($fpo->archivo_respaldo) {
                    $path = public_path(str_replace('/storage/', 'storage/', $fpo->archivo_respaldo));
                    $ext = strtolower(pathinfo($fpo->archivo_respaldo, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Respaldo Postgrado: ' . ($fpo->nombre_programa ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($fpo->archivo_respaldo),
                    ];
                }
            }
        }

        // Experiencia Profesional
        if ($persona->experienciaProfesional) {
            foreach ($persona->experienciaProfesional as $ep) {
                if ($ep->archivo_respaldo) {
                    $path = public_path(str_replace('/storage/', 'storage/', $ep->archivo_respaldo));
                    $ext = strtolower(pathinfo($ep->archivo_respaldo, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Respaldo Exp. Profesional: ' . ($ep->cargo ?? 'N/A') . ' - ' . ($ep->empresa ?? ''),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($ep->archivo_respaldo),
                    ];
                }
            }
        }

        // Experiencia Docente
        if ($persona->experienciaDocente) {
            foreach ($persona->experienciaDocente as $ed) {
                if ($ed->archivo_respaldo) {
                    $path = public_path(str_replace('/storage/', 'storage/', $ed->archivo_respaldo));
                    $ext = strtolower(pathinfo($ed->archivo_respaldo, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Respaldo Exp. Docente: ' . ($ed->institucion ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($ed->archivo_respaldo),
                    ];
                }
            }
        }

        // Capacitaciones
        if ($persona->capacitaciones) {
            foreach ($persona->capacitaciones as $cap) {
                if ($cap->archivo_respaldo) {
                    $path = public_path(str_replace('/storage/', 'storage/', $cap->archivo_respaldo));
                    $ext = strtolower(pathinfo($cap->archivo_respaldo, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Respaldo Capacitación: ' . ($cap->nombre_curso ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($cap->archivo_respaldo),
                    ];
                }
            }
        }

        // Producción Intelectual
        if ($persona->produccionIntelectual) {
            foreach ($persona->produccionIntelectual as $pi) {
                if ($pi->archivo_respaldo) {
                    $path = public_path(str_replace('/storage/', 'storage/', $pi->archivo_respaldo));
                    $ext = strtolower(pathinfo($pi->archivo_respaldo, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Respaldo Producción: ' . ($pi->titulo ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($pi->archivo_respaldo),
                    ];
                }
            }
        }

        // Idiomas
        if ($persona->idiomas) {
            foreach ($persona->idiomas as $idi) {
                if ($idi->archivo_respaldo) {
                    $path = public_path(str_replace('/storage/', 'storage/', $idi->archivo_respaldo));
                    $ext = strtolower(pathinfo($idi->archivo_respaldo, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Respaldo Idioma: ' . ($idi->idioma ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($idi->archivo_respaldo),
                    ];
                }
            }
        }

        // Reconocimientos
        if ($persona->reconocimientos) {
            foreach ($persona->reconocimientos as $rec) {
                if ($rec->archivo_respaldo) {
                    $path = public_path(str_replace('/storage/', 'storage/', $rec->archivo_respaldo));
                    $ext = strtolower(pathinfo($rec->archivo_respaldo, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Respaldo Reconocimiento: ' . ($rec->titulo_premio ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($rec->archivo_respaldo),
                    ];
                }
            }
        }

        return $adjuntos;
    }
}
