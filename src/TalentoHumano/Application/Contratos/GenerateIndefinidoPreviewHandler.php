<?php

namespace Src\TalentoHumano\Application\Contratos;

use Barryvdh\DomPDF\Facade\Pdf;
use Src\TalentoHumano\Domain\Repositories\EmpleadoRepositoryInterface;

final class GenerateIndefinidoPreviewHandler
{
    public function __construct(
        private readonly BuildIndefinidoTemplateDataHandler $builder,
        private readonly EmpleadoRepositoryInterface $repo
    ) {
    }

    public function handle(int $empleadoId, array $overrides = [], ?int $contratoId = null): array
    {
        $payload = $this->builder->handle($empleadoId, $overrides, $contratoId);
        $html = view('contratos.templates.indefinido', $payload)->render();

        return [
            'payload' => $payload,
            'html' => $html,
        ];
    }

    public function renderPdf(int $empleadoId, array $overrides = [], ?int $contratoId = null): array
    {
        $payload = $this->builder->handle($empleadoId, $overrides, $contratoId);

        $pdf = Pdf::loadView('contratos.templates.indefinido', $payload);
        $pdf->setPaper('letter');

        $filename = 'Contrato_Indefinido_' . str_replace(' ', '_', $payload['trabajador']['nombre_completo'] ?? 'preview') . '.pdf';
        $pdfBinary = $pdf->output();
        $resolvedContratoId = $contratoId ?? data_get($payload, 'contrato.id_contrato');

        $persisted = null;

        if ($resolvedContratoId) {
            $persisted = $this->repo->persistGeneratedContract(
                $empleadoId,
                (int) $resolvedContratoId,
                $payload,
                $pdfBinary,
                $filename
            );
        }

        return [
            'pdf_binary' => $pdfBinary,
            'filename' => $filename,
            'payload' => $payload,
            'persisted' => $persisted,
        ];
    }
}
