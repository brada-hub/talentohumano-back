<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: {{ ($format ?? 'pdf') === 'pdf' ? '1.7cm 1.5cm 1.8cm 1.5cm' : '1.4cm 1.2cm 1.4cm 1.2cm' }};
        }

        body {
            font-family: Arial, sans-serif;
            font-size: {{ ($format ?? 'pdf') === 'excel' ? '10px' : '11px' }};
            color: #1f2937;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .page {
            width: 100%;
        }

        .header-table,
        .summary-table,
        .report-table,
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td,
        .summary-table td,
        .report-table th,
        .report-table td,
        .footer-table td {
            vertical-align: top;
        }

        .header-table {
            margin-bottom: {{ ($format ?? 'pdf') === 'excel' ? '10px' : '14px' }};
        }

        .logo-cell {
            width: 110px;
        }

        .logo-cell img {
            width: 92px;
            height: auto;
            display: block;
        }

        .header-center {
            text-align: center;
            color: #4A148C;
        }

        .header-center .line-1 {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 0.4px;
        }

        .header-center .line-2 {
            margin-top: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .header-center .line-3 {
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 0.3px;
        }

        .header-center .line-4 {
            margin-top: 6px;
            font-size: 10px;
            color: #475569;
            letter-spacing: 0.3px;
        }

        .summary-table {
            margin-bottom: 14px;
            border: 1.4px solid #4A148C;
            {{ ($format ?? 'pdf') === 'excel' ? 'background: #fbf9fe;' : '' }}
        }

        .summary-table td {
            border: 1px solid #4A148C;
            padding: 7px 8px;
        }

        .summary-label {
            width: 18%;
            background: #f4eefb;
            color: #4A148C;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .summary-value {
            font-size: 10px;
            font-weight: bold;
        }

        .report-table {
            border: 1.4px solid #4A148C;
            {{ ($format ?? 'pdf') === 'excel' ? 'table-layout: fixed;' : '' }}
        }

        .report-table th,
        .report-table td {
            border: 1px solid #4A148C;
            padding: 7px 8px;
            line-height: 1.35;
        }

        .report-table th {
            background: #f4eefb;
            color: #4A148C;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            text-align: center;
        }

        .report-table td {
            font-size: {{ ($format ?? 'pdf') === 'excel' ? '9px' : '10px' }};
        }

        .report-table tbody tr:nth-child(even) td {
            background: #fbf9fe;
        }

        .empty {
            text-align: center;
            font-style: italic;
            color: #6b7280;
        }

        .footer-table {
            margin-top: 18px;
        }

        .footer-table td {
            font-size: 8px;
            color: #6b7280;
        }

        .footer-right {
            text-align: right;
        }

        .summary-banner {
            margin-bottom: 14px;
            padding: 10px 12px;
            border-left: 4px solid #0ea5e9;
            background: #eff6ff;
            color: #0f172a;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
@php
    $escudoPath = public_path('unitepc_escudo.png');
    $escudoBase64 = file_exists($escudoPath) ? base64_encode(file_get_contents($escudoPath)) : '';
@endphp

<div class="page">
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($escudoBase64)
                    <img src="data:image/png;base64,{{ $escudoBase64 }}" alt="Escudo UNITEPC">
                @endif
            </td>
            <td class="header-center">
                <div class="line-1">Universidad Tecnica Privada Cosmos</div>
                <div class="line-2">UNITEPC - Sistema de Gestion de Talento Humano</div>
                <div class="line-3">{{ $title }}</div>
                <div class="line-4">{{ $subtitle ?? 'Reporte institucional' }}</div>
            </td>
            <td class="logo-cell"></td>
        </tr>
    </table>

    @if(!empty($summary))
        <div class="summary-banner">{{ $summary }}</div>
    @endif

    <table class="summary-table">
        <tr>
            <td class="summary-label">Fecha de emision</td>
            <td class="summary-value">{{ $generatedAt }}</td>
            <td class="summary-label">Total de registros</td>
            <td class="summary-value">{{ $recordsCount }}</td>
        </tr>
        <tr>
            <td class="summary-label">Filtros aplicados</td>
            <td class="summary-value" colspan="3">{{ $filtersLabel }}</td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
        <tr>
            @foreach($headers as $header)
                <th>{{ $header }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{!! nl2br(e((string) $cell)) !!}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ max(count($headers), 1) }}" class="empty">No existen registros para los filtros seleccionados.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td>Reporte institucional generado por SIGETH</td>
            <td class="footer-right">UNITEPC</td>
        </tr>
    </table>
</div>
</body>
</html>
