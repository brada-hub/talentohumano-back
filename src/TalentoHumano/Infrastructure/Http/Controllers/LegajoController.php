<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\TalentoHumano\Infrastructure\Persistence\Models\LegajoDocumentoModel;

class LegajoController extends Controller
{
    public function index($id_empleado): JsonResponse
    {
        $empleado = EmpleadoModel::with(['persona.documentos', 'contratos'])->find($id_empleado);
        if (!$empleado) {
            return ApiResponse::error('Empleado no encontrado', 404);
        }

        $legajoDocs = LegajoDocumentoModel::where('id_empleado', $id_empleado)->get();

        $personaDocs = collect($empleado->persona?->documentos ?? [])->map(function ($documento) use ($id_empleado) {
            return [
                'id_documento' => 'persona_' . $documento->id,
                'id_empleado' => $id_empleado,
                'nombre_archivo' => $documento->nombre_archivo,
                'ruta_archivo' => $documento->ruta_archivo,
                'tipo_mime' => $this->resolveMimeType($documento->formato),
                'tamanio' => null,
                'categoria' => 'Identidad',
                'estado' => 'Registrado',
                'observaciones' => 'Documento personal: ' . strtoupper((string) $documento->tipo),
                'created_at' => $documento->created_at,
                'updated_at' => $documento->updated_at,
            ];
        });

        $docs = $legajoDocs
            ->map(fn (LegajoDocumentoModel $documento) => $documento->toArray())
            ->concat($personaDocs)
            ->sortByDesc('created_at')
            ->values();

        $categoryDefinitions = $this->buildCategoryDefinitions($empleado->contratos->isNotEmpty());
        $categoryStats = $this->buildCategoryStats($docs, $categoryDefinitions);
        $summary = $this->buildSummary($docs, $categoryStats);

        return ApiResponse::success([
            'documentos' => $docs->values()->all(),
            'resumen' => $summary,
            'categorias' => $categoryStats->values()->all(),
        ], 'Legajo documents');
    }

    public function upload(Request $request, $id_empleado): JsonResponse
    {
        $request->validate([
            'file'      => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'categoria' => 'required|string'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            
            // Guardar en storage/app/public/legajos/{id_empleado}
            $path = $file->store("public/legajos/{$id_empleado}");
            
            // Generar URL pública
            $publicUrl = Storage::url($path);

            $doc = LegajoDocumentoModel::create([
                'id_empleado'    => $id_empleado,
                'nombre_archivo' => $originalName,
                'ruta_archivo'   => $publicUrl,
                'tipo_mime'      => $file->getMimeType(),
                'tamanio'        => $file->getSize(),
                'categoria'      => $request->categoria,
                'estado'         => 'Pendiente'
            ]);

            return ApiResponse::created($doc, 'Documento subido correctamente');
        }

        return ApiResponse::error('No se recibió ningún archivo', 400);
    }

    public function destroy($id_documento): JsonResponse
    {
        if (str_starts_with((string) $id_documento, 'persona_')) {
            return ApiResponse::error('Los documentos personales se gestionan desde el perfil del funcionario', 422);
        }

        $doc = LegajoDocumentoModel::find($id_documento);
        if ($doc) {
            // Eliminar archivo físico
            $storagePath = str_replace('/storage', 'public', $doc->ruta_archivo);
            Storage::delete($storagePath);
            
            $doc->delete();
            return ApiResponse::success(null, 'Documento eliminado');
        }
        return ApiResponse::error('Documento no encontrado', 404);
    }

    private function resolveMimeType(?string $formato): string
    {
        $extension = strtolower((string) $formato);

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }

    private function buildCategoryDefinitions(bool $hasContracts): array
    {
        return [
            'Identidad' => [
                'required' => true,
                'description' => 'Carnet de identidad y documentos personales básicos.',
            ],
            'Contrato firmado' => [
                'required' => $hasContracts,
                'description' => 'Contrato laboral firmado que respalda la relación vigente o histórica.',
            ],
            'Contrato generado' => [
                'required' => false,
                'description' => 'Versión generada automáticamente desde la plantilla institucional.',
            ],
            'Contrato' => [
                'required' => false,
                'description' => 'Otros contratos o respaldos contractuales manuales.',
            ],
            'Educación' => [
                'required' => false,
                'description' => 'Respaldos académicos y certificados del funcionario.',
            ],
            'Memorándum' => [
                'required' => false,
                'description' => 'Memorándums, instructivos o comunicaciones laborales.',
            ],
            'Otros' => [
                'required' => false,
                'description' => 'Documentos complementarios del expediente laboral.',
            ],
        ];
    }

    private function buildCategoryStats(Collection $docs, array $definitions): Collection
    {
        $grouped = $docs->groupBy(fn ($doc) => $doc['categoria'] ?? 'Otros');

        $base = collect($definitions)->map(function (array $definition, string $name) use ($grouped) {
            $items = $grouped->get($name, collect());
            $count = $items->count();
            $present = $count > 0;

            return [
                'nombre' => $name,
                'required' => (bool) ($definition['required'] ?? false),
                'description' => $definition['description'] ?? null,
                'count' => $count,
                'present' => $present,
                'status' => ($definition['required'] ?? false)
                    ? ($present ? 'Completo' : 'Pendiente')
                    : ($present ? 'Registrado' : 'Opcional'),
            ];
        });

        $dynamic = $grouped
            ->keys()
            ->filter(fn ($name) => !array_key_exists((string) $name, $definitions))
            ->map(function ($name) use ($grouped) {
                $items = $grouped->get($name, collect());

                return [
                    'nombre' => $name,
                    'required' => false,
                    'description' => 'Categoría adicional registrada en el legajo.',
                    'count' => $items->count(),
                    'present' => $items->isNotEmpty(),
                    'status' => $items->isNotEmpty() ? 'Registrado' : 'Opcional',
                ];
            });

        return $base
            ->concat($dynamic)
            ->sortBy([
                ['required', 'desc'],
                ['nombre', 'asc'],
            ])
            ->values();
    }

    private function buildSummary(Collection $docs, Collection $categories): array
    {
        $required = $categories->where('required', true);
        $requiredTotal = $required->count();
        $requiredCompleted = $required->where('present', true)->count();
        $requiredMissing = max($requiredTotal - $requiredCompleted, 0);
        $coverage = $requiredTotal > 0
            ? (int) round(($requiredCompleted / $requiredTotal) * 100)
            : 100;

        $status = 'Completo';
        if ($coverage < 100) {
            $status = $coverage >= 50 ? 'En proceso' : 'Crítico';
        }

        $generatedSignedCount = $docs->filter(function ($doc) {
            return in_array(($doc['categoria'] ?? ''), ['Contrato generado', 'Contrato firmado'], true);
        })->count();

        return [
            'total_documentos' => $docs->count(),
            'generated_signed_count' => $generatedSignedCount,
            'categorias_activas' => $docs->pluck('categoria')->filter()->unique()->count(),
            'required_total' => $requiredTotal,
            'required_completed' => $requiredCompleted,
            'required_missing' => $requiredMissing,
            'coverage_percentage' => $coverage,
            'status' => $status,
        ];
    }
}
