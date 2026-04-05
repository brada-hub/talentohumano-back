<?php

namespace Src\Onboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Src\Onboarding\Application\Register\CompleteOnboardingHandler;
use Src\Onboarding\Domain\Repositories\OnboardingRepositoryInterface;
use Src\Shared\Infrastructure\Http\ApiResponse;

final class PortalOnboardingController extends Controller
{
    public function __construct(
        private readonly OnboardingRepositoryInterface $repo,
        private readonly CompleteOnboardingHandler $completeHandler
    ) {}

    public function validarToken($token): JsonResponse
    {
        $otoken = $this->repo->findByToken($token);

        if (!$otoken || !$otoken->canBeUsed()) {
            return ApiResponse::error('Token invalido o expirado', 404);
        }

        return ApiResponse::success([
            'valido' => true,
            'id_persona' => $otoken->personaId(),
            'token' => $token,
        ]);
    }

    public function verificar(Request $request): JsonResponse
    {
        $request->validate([
            'ci' => 'required',
            'fecha_nacimiento' => 'required|date',
        ]);

        $persona = $this->repo->findPersonaByCiAndBirthDate(
            $request->ci,
            $request->fecha_nacimiento
        );

        if (!$persona) {
            return ApiResponse::success([
                'success' => true,
                'session_key' => 'new_session_' . \Illuminate\Support\Str::random(10),
                'datos_precargados' => null,
                'estado' => 'sin_iniciar',
            ]);
        }

        return ApiResponse::success([
            'success' => true,
            'session_key' => $request->token ?: 'new_session_' . \Illuminate\Support\Str::random(10),
            'datos_precargados' => $persona,
            'estado' => $persona['estado_onboarding'] ?? 'sin_iniciar',
        ]);
    }

    public function guardarPersonal(Request $request): JsonResponse
    {
        $this->repo->saveFullOnboardingData(['persona' => $request->all()]);
        return ApiResponse::success([], 'Datos personales guardados temporalmente');
    }

    public function guardarAcademico(Request $request): JsonResponse
    {
        $this->repo->saveFullOnboardingData(['academico' => [$request->all()]]);
        return ApiResponse::success([], 'Formacion academica guardada temporalmente');
    }

    public function completar(Request $request): JsonResponse
    {
        Log::info('Iniciando proceso de completar registro masivo.');

        try {
            $token = (string) ($request->input('token') ?? $request->input('persona.session_key') ?? '');

            $this->completeHandler->handle($request->all(), $token);

            return ApiResponse::success([], 'Registro completado con exito');
        } catch (\InvalidArgumentException $e) {
            Log::warning('Validacion fallida en completar registro: ' . $e->getMessage());
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            Log::error('Error critico en completar registro: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return ApiResponse::error('Error critico al procesar el registro.', 500);
        }
    }

    public function mostrarArchivo(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $cleanPath = str_replace('storage/', '', $filename);
        $path = storage_path('app/public/' . $cleanPath);

        if (!file_exists($path)) {
            abort(404, "Archivo no encontrado en: $path");
        }

        return response()->file($path);
    }
}
