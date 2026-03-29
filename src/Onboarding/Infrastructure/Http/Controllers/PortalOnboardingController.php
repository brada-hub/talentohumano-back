<?php

namespace Src\Onboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Src\Onboarding\Application\Register\CompleteOnboardingHandler;
use Src\Onboarding\Domain\Repositories\OnboardingRepositoryInterface;
use Src\Shared\Infrastructure\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
            return ApiResponse::error('Token inválido o expirado', 404);
        }

        return ApiResponse::success([
            'valido' => true,
            'id_persona' => $otoken->personaId(),
            'token' => $token
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
            return ApiResponse::unauthorized('Datos no coinciden con nuestros registros.');
        }

        return ApiResponse::success([
            'success' => true,
            'session_key' => $request->token ?: 'new_session_' . \Illuminate\Support\Str::random(10),
            'datos_precargados' => $persona,
            'estado' => $persona['estado_onboarding'] ?? 'sin_iniciar'
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
        return ApiResponse::success([], 'Formación académica guardada temporalmente');
    }

    public function completar(Request $request): JsonResponse
    {
        try {
            $token = $request->input('persona.session_key') ?? $request->input('token', '');
            
            $this->completeHandler->handle($request->all(), $token);

            return ApiResponse::success([], 'Registro completado con éxito');
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            \Log::error('Error en completar registro: ' . $e->getMessage());
            return ApiResponse::error('Error crítico al procesar el registro.', 500);
        }
    }
}
