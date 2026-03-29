<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;

class CvController extends Controller
{
    /**
     * Genera y descarga el CV completo en PDF
     */
    public function descargar($personaId)
    {
        $persona = PersonaModel::with([
            'sexo', 'nacionalidad', 'ciudad', 'ciudad.departamento', 'pais', 'expedido', 'documentos',
            'formacionPregrado.depto', 'formacionPostgrado.depto',
            'experienciaDocente.depto', 'experienciaProfesional.depto',
            'capacitaciones.depto', 'produccionIntelectual.depto',
            'reconocimientos', 'idiomas',
        ])->find($personaId);

        if (!$persona) {
            abort(404, 'Persona no encontrada');
        }

        // Obtener cargo actual si es empleado
        $empleado = EmpleadoModel::where('id_persona', $persona->id)
            ->with(['contratoActivo.cargo', 'caja', 'pensiones'])
            ->first();

        $cargo = null;
        if ($empleado && $empleado->contratoActivo && $empleado->contratoActivo->cargo) {
            $cargo = $empleado->contratoActivo->cargo->nombre_cargo;
        }

        // Generar QR de verificación
        $qrCode = QrCode::format('svg')->size(120)->errorCorrection('H')->generate(config('app.url') . '/api/v1/cv/verificar/' . $persona->id);

        $adjuntosRaw = $this->recolectarAdjuntos($persona);
        $adjuntosValidados = [];
        $pdfsParaFusion = [];

        foreach ($adjuntosRaw as $adj) {
            if ($adj['type'] === 'image') {
                $adjuntosValidados[] = $adj;
            } elseif ($adj['type'] === 'pdf' && !empty($adj['path']) && file_exists($adj['path'])) {
                try {
                    $fpdi = new \setasign\Fpdi\Tcpdf\Fpdi();
                    $fpdi->setSourceFile($adj['path']);
                    $pdfsParaFusion[] = $adj['path'];
                } catch (\Throwable $e) {
                    // PDF incompatible/comprimido v1.5+, se incluye como texto de advertencia
                    $adjuntosValidados[] = $adj;
                }
            }
        }

        $pdf = Pdf::loadView('cv.curriculum', [
            'persona'  => $persona,
            'empleado' => $empleado,
            'cargo'    => $cargo,
            'qrCode'   => $qrCode,
            'adjuntos' => $adjuntosValidados,
        ]);

        $pdf->setPaper('letter', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        $merger = new \iio\libmergepdf\Merger;
        $merger->addRaw($pdf->output());

        foreach ($pdfsParaFusion as $pdfPath) {
            try { $merger->addFile($pdfPath); } catch (\Throwable $e) {}
        }

        try {
            $finalPdf = $merger->merge();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Merge final falló: " . $e->getMessage());
            $finalPdf = $pdf->output(); // Fallback si algo catastrofico pasa con fpdi/merge
        }
        $filename = 'CV_' . str_replace(' ', '_', $persona->primer_apellido . '_' . $persona->nombres) . '.pdf';

        return response($finalPdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Vista previa del CV en el navegador
     */
    public function preview($personaId)
    {
        $persona = PersonaModel::with([
            'sexo', 'nacionalidad', 'ciudad', 'ciudad.departamento', 'pais', 'expedido', 'documentos',
            'formacionPregrado.depto', 'formacionPostgrado.depto',
            'experienciaDocente.depto', 'experienciaProfesional.depto',
            'capacitaciones.depto', 'produccionIntelectual.depto',
            'reconocimientos', 'idiomas',
        ])->find($personaId);

        if (!$persona) {
            abort(404, 'Persona no encontrada');
        }

        $empleado = EmpleadoModel::where('id_persona', $persona->id)
            ->with(['contratoActivo.cargo', 'caja', 'pensiones'])
            ->first();

        $cargo = null;
        if ($empleado && $empleado->contratoActivo && $empleado->contratoActivo->cargo) {
            $cargo = $empleado->contratoActivo->cargo->nombre_cargo;
        }

        $qrCode = QrCode::format('svg')->size(120)->errorCorrection('H')->generate(config('app.url') . '/api/v1/cv/verificar/' . $persona->id);

        $adjuntosRaw = $this->recolectarAdjuntos($persona);
        $adjuntosValidados = [];
        $pdfsParaFusion = [];

        foreach ($adjuntosRaw as $adj) {
            if ($adj['type'] === 'image') {
                $adjuntosValidados[] = $adj;
            } elseif ($adj['type'] === 'pdf' && !empty($adj['path']) && file_exists($adj['path'])) {
                try {
                    $fpdi = new \setasign\Fpdi\Tcpdf\Fpdi();
                    $fpdi->setSourceFile($adj['path']);
                    $pdfsParaFusion[] = $adj['path'];
                } catch (\Throwable $e) {
                    $adjuntosValidados[] = $adj;
                }
            }
        }

        $pdf = Pdf::loadView('cv.curriculum', [
            'persona'  => $persona,
            'empleado' => $empleado,
            'cargo'    => $cargo,
            'qrCode'   => $qrCode,
            'adjuntos' => $adjuntosValidados,
        ]);

        $pdf->setPaper('letter', 'portrait');
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isHtml5ParserEnabled', true);

        $merger = new \iio\libmergepdf\Merger;
        $merger->addRaw($pdf->output());

        foreach ($pdfsParaFusion as $pdfPath) {
            try { $merger->addFile($pdfPath); } catch (\Throwable $e) {}
        }

        try {
            $finalPdf = $merger->merge();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Merge final falló preview: " . $e->getMessage());
            $finalPdf = $pdf->output(); 
        }

        return response($finalPdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="CV_Preview.pdf"');
    }

    /**
     * Endpoint de verificación QR
     */
    public function verificar($personaId)
    {
        $persona = PersonaModel::with(['sexo', 'expedido'])->find($personaId);

        if (!$persona) {
            return response()->json(['valido' => false, 'message' => 'Persona no encontrada'], 404);
        }

        $empleado = EmpleadoModel::where('id_persona', $persona->id)
            ->with(['contratoActivo.cargo', 'contratoActivo.area'])
            ->first();

        return response()->json([
            'valido' => true,
            'persona' => [
                'nombre_completo' => $persona->primer_apellido . ' ' . $persona->segundo_apellido . ' ' . $persona->nombres,
                'ci' => $persona->ci,
                'expedido' => $persona->expedido->nombre ?? '-',
            ],
            'empleado' => $empleado ? [
                'cargo' => $empleado->contratoActivo->cargo->nombre_cargo ?? '-',
                'area' => $empleado->contratoActivo->area->nombre_area ?? '-',
                'estado' => $empleado->estado_laboral,
            ] : null,
            'verificado_en' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Recolecta todos los archivos adjuntos de la persona
     */
    private function recolectarAdjuntos(PersonaModel $persona): array
    {
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
            foreach ($persona->formacionPregrado as $i => $fp) {
                if ($fp->archivo_diploma) {
                    $path = public_path(str_replace('/storage/', 'storage/', $fp->archivo_diploma));
                    $ext = strtolower(pathinfo($fp->archivo_diploma, PATHINFO_EXTENSION));
                    $adjuntos[] = [
                        'label' => 'Diploma Pregrado: ' . ($fp->carrera ?? 'N/A'),
                        'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf',
                        'path' => $path,
                        'filename' => basename($fp->archivo_diploma),
                        'original_path' => $fp->archivo_diploma,
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
                        'original_path' => $fp->archivo_titulo,
                    ];
                }
            }
        }

        // Formación Postgrado - Respaldo
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
                        'original_path' => $fpo->archivo_respaldo,
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
                        'original_path' => $ep->archivo_respaldo,
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
                        'original_path' => $ed->archivo_respaldo,
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
                        'original_path' => $cap->archivo_respaldo,
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
                        'original_path' => $pi->archivo_respaldo,
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
                        'original_path' => $idi->archivo_respaldo,
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
                        'original_path' => $rec->archivo_respaldo,
                    ];
                }
            }
        }

        return $adjuntos;
    }
}
