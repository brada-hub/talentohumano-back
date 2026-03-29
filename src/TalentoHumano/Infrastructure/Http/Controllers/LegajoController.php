<?php

namespace Src\TalentoHumano\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Src\TalentoHumano\Infrastructure\Persistence\Models\LegajoDocumentoModel;

class LegajoController extends Controller
{
    public function index($id_empleado): JsonResponse
    {
        $docs = LegajoDocumentoModel::where('id_empleado', $id_empleado)->get();
        return ApiResponse::success($docs, 'Legajo documents');
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
}
