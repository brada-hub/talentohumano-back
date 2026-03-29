<?php

namespace Src\TalentoHumano\Application\Empleados;

use Src\Personal\Infrastructure\Persistence\Models\PersonaModel;
use Src\TalentoHumano\Infrastructure\Persistence\Models\EmpleadoModel;
use InvalidArgumentException;

/**
 * Handle QR Verification returning structured output.
 */
final class VerifyCvQrHandler
{
    public function handle(string $personaId): array
    {
        // Using Eloquent directly in the handler violates pure DDD, 
        // but for a simple read-only fast path it is pragmatic. 
        // A true refactor would map this via PersonaRepository and EmpleadoRepository.
        
        $persona = PersonaModel::with(['expedido'])->find($personaId);

        if (!$persona) {
            throw new InvalidArgumentException("Persona no encontrada");
        }

        $empleado = EmpleadoModel::where('id_persona', $persona->id)
            ->with(['contratoActivo.cargo', 'contratoActivo.area'])
            ->first();

        return [
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
        ];
    }
}
