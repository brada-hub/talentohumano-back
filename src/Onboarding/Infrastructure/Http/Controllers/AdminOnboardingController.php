<?php

namespace Src\Onboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Src\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTokenModel;

class AdminOnboardingController extends Controller
{
    /**
     * Generar un nuevo token para un registro
     */
    public function generarToken(Request $request)
    {
        $tokenString = Str::random(64);
        
        $token = OnboardingTokenModel::create([
            'token' => $tokenString,
            'id_persona' => $request->id_persona, // Opcional
            'activo' => true,
            'created_by' => auth('sanctum')->id()
        ]);

        return response()->json([
            'success' => true,
            'token' => $token->token,
            'url' => config('app.frontend_url', 'http://localhost:9001') . '/#/portal/token/' . $token->token
        ]);
    }

    /**
     * Listado de perfiles pendientes
     */
    public function pendientes()
    {
        // Lógica para listar a los que tienen estado_onboarding = 'completado'
    }

    public function getStatus()
    {
        $enabled = \Illuminate\Support\Facades\DB::table('_configs')
            ->where('key', 'onboarding_enabled')
            ->value('value');
            
        return response()->json(['enabled' => $enabled == '1']);
    }

    public function toggleStatus(Request $request)
    {
        \Illuminate\Support\Facades\DB::table('_configs')
            ->where('key', 'onboarding_enabled')
            ->update([
                'value' => $request->enabled ? '1' : '0',
                'updated_at' => now()
            ]);

        return response()->json(['success' => true]);
    }
}
