<?php

namespace Src\TalentoHumano\Infrastructure\Persistence;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\ContratoModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\LegajoDocumentoModel;

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
            'persona.departamento',
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
                $path = $doc->ruta_archivo ? storage_path('app/public/' . str_replace('/storage/', '', $doc->ruta_archivo)) : null;
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
                    $path = storage_path('app/public/' . str_replace('/storage/', '', $fp->archivo_diploma));
                    $ext = strtolower(pathinfo($fp->archivo_diploma, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Diploma Pregrado: ' . ($fp->carrera ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($fp->archivo_diploma),
                    ];
                }
                if ($fp->archivo_titulo) {
                    $path = storage_path('app/public/' . str_replace('/storage/', '', $fp->archivo_titulo));
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
                    $path = storage_path('app/public/' . str_replace('/storage/', '', $fpo->archivo_respaldo));
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
                    $path = storage_path('app/public/' . str_replace('/storage/', '', $ep->archivo_respaldo));
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
                    $path = storage_path('app/public/' . str_replace('/storage/', '', $ed->archivo_respaldo));
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
                    $path = storage_path('app/public/' . str_replace('/storage/', '', $cap->archivo_respaldo));
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
                    $path = storage_path('app/public/' . str_replace('/storage/', '', $pi->archivo_respaldo));
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
                    $path = storage_path('app/public/' . str_replace('/storage/', '', $idi->archivo_respaldo));
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
                    $path = storage_path('app/public/' . str_replace('/storage/', '', $rec->archivo_respaldo));
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

    public function updateEmployee(int $idEmpleado, array $data): array
    {
        return DB::transaction(function () use ($idEmpleado, $data) {
            $empleado = EmpleadoModel::findOrFail($idEmpleado);
            $persona = $empleado->persona;

            // 1. Actualizar Persona
            if (isset($data['persona'])) {
                $pData = $data['persona'];
                
                // Si la foto es un base64 (viene del componente de edición)
                if (isset($pData['foto']) && str_starts_with($pData['foto'], 'data:')) {
                    $pData['foto'] = $this->saveBase64Foto($pData['foto'], $persona->id);
                }

                $persona->update($pData);
            }

            // 2. Actualizar Empleado
            if (isset($data['empleado'])) {
                $eData = $data['empleado'];
                $empleado->update($eData);
            }

            return $this->findByIdWithDetails($idEmpleado);
        });
    }

    public function findContratoPreviewData(int $empleadoId, ?int $contratoId = null): ?array
    {
        $empleado = EmpleadoModel::with([
            'persona',
            'persona.sexo',
            'persona.nacionalidad',
            'persona.departamento',
            'persona.ciudad',
            'persona.expedido',
            'beneficiarios.parentesco',
            'beneficiarios.expedido',
            'contratoActivo.tipo',
            'contratoActivo.area',
            'contratoActivo.cargo',
            'contratoActivo.sede',
            'contratos.tipo',
            'contratos.area',
            'contratos.cargo',
            'contratos.sede',
        ])->find($empleadoId);

        if (!$empleado) {
            return null;
        }

        $contrato = $contratoId
            ? $empleado->contratos->firstWhere('id_contrato', $contratoId)
            : $empleado->contratoActivo;

        if (!$contrato) {
            return null;
        }

        return [
            'empleado' => $empleado->toArray(),
            'contrato' => $contrato->toArray(),
        ];
    }

    public function persistGeneratedContract(int $empleadoId, int $contratoId, array $payload, string $pdfBinary, string $filename): array
    {
        return DB::transaction(function () use ($empleadoId, $contratoId, $payload, $pdfBinary, $filename) {
            $empleado = EmpleadoModel::findOrFail($empleadoId);
            $contrato = ContratoModel::where('id_empleado', $empleadoId)->findOrFail($contratoId);

            ContratoModel::where('id_empleado', $empleadoId)
                ->where('id_contrato', '!=', $contratoId)
                ->where('estado_contrato', 'Activo')
                ->update(['estado_contrato' => 'Vencido']);

            $contrato->update([
                'salario' => data_get($payload, 'contrato.salario_numeral') ?: $contrato->salario,
                'fecha_inicio' => data_get($payload, 'contrato.fecha_inicio') ?: $contrato->fecha_inicio,
                'fecha_fin' => data_get($payload, 'contrato.fecha_fin') ?: $contrato->fecha_fin,
                'estado_contrato' => 'Activo',
            ]);

            $empleado->update([
                'estado_laboral' => 'Activo',
            ]);

            $safeName = Str::slug(pathinfo($filename, PATHINFO_FILENAME));
            $finalName = $safeName . '_' . now()->format('Ymd_His') . '.pdf';
            $storagePath = "legajos/{$empleadoId}/contratos/{$finalName}";

            Storage::disk('public')->put($storagePath, $pdfBinary);

            $observaciones = "Contrato generado automáticamente para el contrato #{$contratoId}";

            LegajoDocumentoModel::where('id_empleado', $empleadoId)
                ->where('categoria', 'Contrato generado')
                ->where('observaciones', $observaciones)
                ->get()
                ->each(function (LegajoDocumentoModel $documentoAnterior) {
                    $oldPath = ltrim(str_replace('/storage/', '', (string) $documentoAnterior->ruta_archivo), '/');
                    if ($oldPath !== '') {
                        Storage::disk('public')->delete($oldPath);
                    }
                    $documentoAnterior->delete();
                });

            $documento = LegajoDocumentoModel::create([
                'id_empleado' => $empleadoId,
                'nombre_archivo' => $finalName,
                'ruta_archivo' => Storage::url($storagePath),
                'tipo_mime' => 'application/pdf',
                'tamanio' => strlen($pdfBinary),
                'categoria' => 'Contrato generado',
                'estado' => 'Generado',
                'observaciones' => $observaciones,
            ]);

            return [
                'contrato' => $contrato->fresh()->toArray(),
                'documento' => $documento->toArray(),
            ];
        });
    }

    public function createContrato(int $empleadoId, array $data): array
    {
        return DB::transaction(function () use ($empleadoId, $data) {
            $empleado = EmpleadoModel::with('contratos')->findOrFail($empleadoId);

            $estado = $this->normalizeContratoState(
                $data['estado_contrato'] ?? 'Activo',
                $data['fecha_inicio'] ?? null,
                $data['fecha_fin'] ?? null,
            );

            if ($estado === 'Activo') {
                ContratoModel::where('id_empleado', $empleadoId)
                    ->where('estado_contrato', 'Activo')
                    ->update(['estado_contrato' => 'Vencido']);

                $empleado->update(['estado_laboral' => 'Activo']);
            }

            $contrato = ContratoModel::create([
                'id_empleado' => $empleadoId,
                'id_tipo_contrato' => $data['id_tipo_contrato'],
                'id_area' => $data['id_area'],
                'id_cargo' => $data['id_cargo'],
                'salario' => $data['salario'] ?? null,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'id_sede' => $data['id_sede'],
                'estado_contrato' => $estado,
            ]);

            return $contrato->load(['tipo', 'area', 'cargo', 'sede'])->toArray();
        });
    }

    public function updateContrato(int $empleadoId, int $contratoId, array $data): array
    {
        return DB::transaction(function () use ($empleadoId, $contratoId, $data) {
            $empleado = EmpleadoModel::findOrFail($empleadoId);
            $contrato = ContratoModel::where('id_empleado', $empleadoId)->findOrFail($contratoId);

            $estado = $this->normalizeContratoState(
                $data['estado_contrato'] ?? $contrato->estado_contrato,
                $data['fecha_inicio'] ?? $contrato->fecha_inicio?->toDateString(),
                $data['fecha_fin'] ?? $contrato->fecha_fin?->toDateString(),
            );

            if ($estado === 'Activo') {
                ContratoModel::where('id_empleado', $empleadoId)
                    ->where('id_contrato', '!=', $contratoId)
                    ->where('estado_contrato', 'Activo')
                    ->update(['estado_contrato' => 'Vencido']);

                $empleado->update(['estado_laboral' => 'Activo']);
            }

            $contrato->update([
                'id_tipo_contrato' => $data['id_tipo_contrato'],
                'id_area' => $data['id_area'],
                'id_cargo' => $data['id_cargo'],
                'salario' => $data['salario'] ?? null,
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'id_sede' => $data['id_sede'],
                'estado_contrato' => $estado,
            ]);

            if ($estado !== 'Activo' && !$this->hasActiveContrato($empleadoId)) {
                $empleado->update(['estado_laboral' => 'Inactivo']);
            }

            return $contrato->load(['tipo', 'area', 'cargo', 'sede'])->toArray();
        });
    }

    public function finalizeContrato(int $empleadoId, int $contratoId, ?string $fechaFin = null): array
    {
        return DB::transaction(function () use ($empleadoId, $contratoId, $fechaFin) {
            $empleado = EmpleadoModel::findOrFail($empleadoId);
            $contrato = ContratoModel::where('id_empleado', $empleadoId)->findOrFail($contratoId);

            $contrato->update([
                'fecha_fin' => $fechaFin ?: now()->toDateString(),
                'estado_contrato' => 'Finalizado',
            ]);

            if (!$this->hasActiveContrato($empleadoId)) {
                $empleado->update(['estado_laboral' => 'Inactivo']);
            }

            return $contrato->load(['tipo', 'area', 'cargo', 'sede'])->toArray();
        });
    }

    public function getContratoVersiones(int $empleadoId, int $contratoId): array
    {
        ContratoModel::where('id_empleado', $empleadoId)->findOrFail($contratoId);

        $prefix = "Contrato firmado #{$contratoId} v";

        return LegajoDocumentoModel::where('id_empleado', $empleadoId)
            ->where('categoria', 'Contrato firmado')
            ->where('observaciones', 'like', $prefix . '%')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (LegajoDocumentoModel $documento) {
                $version = 1;
                if (preg_match('/v(\d+)/', (string) $documento->observaciones, $matches) === 1) {
                    $version = (int) $matches[1];
                }

                return array_merge($documento->toArray(), [
                    'version' => $version,
                ]);
            })
            ->all();
    }

    public function uploadContratoFirmado(int $empleadoId, int $contratoId, array $fileData): array
    {
        return DB::transaction(function () use ($empleadoId, $contratoId, $fileData) {
            ContratoModel::where('id_empleado', $empleadoId)->findOrFail($contratoId);

            /** @var UploadedFile $file */
            $file = $fileData['file'];
            $extension = strtolower($file->getClientOriginalExtension());
            $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));

            $currentVersion = LegajoDocumentoModel::where('id_empleado', $empleadoId)
                ->where('categoria', 'Contrato firmado')
                ->where('observaciones', 'like', "Contrato firmado #{$contratoId} v%")
                ->count();

            $nextVersion = $currentVersion + 1;
            $finalName = "{$baseName}_v{$nextVersion}_" . now()->format('Ymd_His') . ".{$extension}";
            $storagePath = $file->storeAs("public/legajos/{$empleadoId}/contratos/firmados", $finalName);

            $documento = LegajoDocumentoModel::create([
                'id_empleado' => $empleadoId,
                'nombre_archivo' => $file->getClientOriginalName(),
                'ruta_archivo' => Storage::url($storagePath),
                'tipo_mime' => $file->getMimeType(),
                'tamanio' => $file->getSize(),
                'categoria' => 'Contrato firmado',
                'estado' => 'Vigente',
                'observaciones' => "Contrato firmado #{$contratoId} v{$nextVersion}",
            ]);

            return array_merge($documento->toArray(), [
                'version' => $nextVersion,
            ]);
        });
    }

    private function normalizeContratoState(?string $estado, ?string $fechaInicio, ?string $fechaFin): string
    {
        $estado = $estado ?: 'Borrador';

        if (in_array($estado, ['Borrador', 'Finalizado'], true)) {
            return $estado;
        }

        if ($fechaFin) {
            try {
                if (\Carbon\Carbon::parse($fechaFin)->lt(now()->startOfDay())) {
                    return 'Vencido';
                }
            } catch (\Throwable $exception) {
                return $estado;
            }
        }

        return $estado;
    }

    private function saveBase64Foto(string $b64, string $idPersona): string
    {
        $parts = explode(',', $b64);
        if (count($parts) < 2) return $b64;

        $fileData = base64_decode($parts[1]);
        $fileName = "foto_{$idPersona}_".time().".png";
        $filePath = "documentos/{$fileName}";

        Storage::disk('public')->put($filePath, $fileData);
        return "/storage/{$filePath}";
    }

    private function hasActiveContrato(int $empleadoId): bool
    {
        return ContratoModel::where('id_empleado', $empleadoId)
            ->where('estado_contrato', 'Activo')
            ->exists();
    }
}
