<?php

namespace Src\TalentoHumano\Application\Contratos;

use InvalidArgumentException;
use Src\TalentoHumano\Domain\Support\ContratoGrammar;

final class BuildIndefinidoTemplateDataHandler extends AbstractBuildContratoTemplateDataHandler
{
    public function handle(int $empleadoId, array $overrides = [], ?int $contratoId = null): array
    {
        $data = $this->repo->findContratoPreviewData($empleadoId, $contratoId);

        if (!$data) {
            throw new InvalidArgumentException('No se encontró información suficiente para generar el contrato.');
        }

        $empleado = $data['empleado'];
        $contrato = $data['contrato'];
        $persona = $empleado['persona'] ?? [];

        $sexo = $persona['sexo']['sexo'] ?? null;
        $gramatica = ContratoGrammar::fromSexo($sexo);

        $fechaInicio = $this->toCarbon(data_get($overrides, 'contrato.fecha_inicio') ?? $contrato['fecha_inicio'] ?? null);
        $fechaFirma = $this->toCarbon(data_get($overrides, 'contrato.fecha_firma') ?? data_get($overrides, 'contrato.fecha_inicio') ?? $contrato['fecha_inicio'] ?? null);

        $base = $this->buildBasePayload(
            $persona,
            $empleado,
            $contrato,
            $gramatica,
            $fechaInicio,
            null,
            $fechaFirma,
            $overrides
        );

        $base['contrato']['tipo_documento'] = 'indefinido';
        $base['contrato']['titulo_documento'] = 'CONTRATO DE TRABAJO INDEFINIDO';
        $base['contrato']['duracion_literal'] = 'tiempo indefinido';
        $base['contrato']['fecha_fin'] = null;
        $base['contrato']['fecha_fin_literal'] = null;
        $base['contrato']['remuneracion_detalle'] = data_get($overrides, 'contrato.remuneracion_detalle')
            ?: 'La referida suma de dinero será pagada en la moneda señalada y establecido por ley, mediante depósito bancario, en una cuenta bancaria y cuyo titular es ' . $gramatica['el_trabajador'] . '.';

        return $this->mergeRecursiveDistinct($base, $overrides);
    }
}
