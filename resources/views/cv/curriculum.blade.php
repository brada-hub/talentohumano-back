<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CV Normalizado - {{ $persona->primer_apellido }} {{ $persona->nombres }}</title>
    <style>
        @page {
            size: 216mm 330mm;
            margin: 1.8cm 1.7cm 1.8cm 1.9cm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10.5px;
            color: #1a1a1a;
            line-height: 1.35;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        table { width: 100%; border-collapse: collapse; }
        .page { width: 100%; }
        .header-table, .plain-table { border: none; margin-bottom: 12px; }
        .header-table td, .plain-table td { border: none; padding: 0; }
        .header-title { text-align: center; color: #4A148C; }
        .header-title .line-1 { font-size: 15px; font-weight: bold; text-decoration: underline; letter-spacing: 0.4px; }
        .header-title .line-2 { margin-top: 4px; font-size: 12px; font-weight: bold; letter-spacing: 0.8px; }
        .header-title .line-3 { margin-top: 8px; font-size: 13px; font-weight: bold; text-decoration: underline; letter-spacing: 0.5px; }
        .logo-box { width: 110px; }
        .logo-box img { width: 100px; height: auto; display: block; }
        .photo-wrap { width: 125px; margin-left: auto; }
        .photo-caption { margin-bottom: 5px; font-size: 8px; font-weight: bold; color: #4A148C; text-align: center; text-transform: uppercase; letter-spacing: 0.4px; }
        .photo-box { width: 105px; height: 132px; margin: 0 auto; border: 1.5px solid #4A148C; background: #f4f4f4; overflow: hidden; text-align: center; position: relative; }
        .photo-box img { width: 100%; height: 100%; display: block; object-fit: contain; }
        .no-photo { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%; font-size: 8px; color: #666; font-style: italic; line-height: 1.2; }
        .section-title { margin-top: 16px; margin-bottom: 8px; padding: 4px 8px; border: 1px solid #4A148C; background: #f3eff8; color: #4A148C; font-size: 10px; font-weight: bold; text-transform: uppercase; text-align: center; letter-spacing: 0.5px; page-break-after: avoid; }
        .sub-title { margin-top: 8px; margin-bottom: 6px; font-size: 9.5px; font-weight: bold; text-transform: uppercase; color: #4A148C; text-decoration: underline; page-break-after: avoid; }
        .data-table, .record-table, .languages-table { margin-bottom: 10px; border: 1.4px solid #4A148C; }
        .data-table td, .record-table td, .languages-table td, .languages-table th { border: 1px solid #4A148C; padding: 6px 7px; vertical-align: top; }
        .lbl { width: 18%; background: #fbf9fe; color: #4A148C; font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; }
        .val { background: #fff; color: #000; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .val-normal { text-transform: none; font-weight: normal; }
        .record-block { page-break-inside: avoid; margin-bottom: 12px; }
        .record-title { width: 16%; background: #f6f1fb; color: #4A148C; font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; text-align: center; }
        .record-main { font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .record-meta-label { background: #fbf9fe; color: #4A148C; font-size: 8px; font-weight: bold; text-transform: uppercase; text-align: center; }
        .record-meta-value { background: #fff; font-size: 9.5px; font-weight: bold; text-align: center; text-transform: uppercase; }
        .record-note { background: #fff; font-size: 9px; font-style: italic; text-transform: none; line-height: 1.35; }
        .evidence-cell { width: 70px; text-align: center; padding: 4px !important; }
        .evidence-cell img { width: 48px; height: 48px; display: block; margin: 0 auto 3px; }
        .evidence-text { font-size: 7px; color: #4A148C; font-weight: bold; line-height: 1.1; text-transform: uppercase; }
        .languages-table th { background: #fbf9fe; color: #4A148C; font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; }
        .footer-table { margin-top: 22px; border: none; }
        .footer-table td { border: none; text-align: center; vertical-align: bottom; }
        .signature-line { width: 160px; margin: 0 auto 6px; border-top: 1px solid #4A148C; }
        .footer-label { color: #4A148C; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .footer-date { margin-top: 40px; color: #4A148C; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .footer-brand { margin-top: 18px; text-align: center; font-size: 7px; color: #8c8c8c; font-style: italic; text-transform: uppercase; letter-spacing: 0.5px; }
        .annex-image { display: block; max-width: 100%; max-height: 285mm; width: auto; height: auto; margin: 0 auto; border: 1px solid #4A148C; object-fit: contain; }
    </style>
</head>
<body>
@php
    $escudoPath = public_path('unitepc_escudo.png');
    $escudoBase64 = file_exists($escudoPath) ? base64_encode(file_get_contents($escudoPath)) : '';
    $fotoBase64 = '';
    if (!empty($persona->foto)) {
        $cleanFoto = str_replace('/storage/', '', $persona->foto);
        $paths = [public_path('storage/' . $cleanFoto), storage_path('app/public/' . $cleanFoto), storage_path('app/' . $cleanFoto)];
        foreach ($paths as $imgPath) {
            if (file_exists($imgPath)) { $fotoBase64 = base64_encode(file_get_contents($imgPath)); break; }
        }
    }
    $fmt = function ($date) { return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : '---'; };
    $fullName = trim(($persona->primer_apellido ?? '') . ' ' . ($persona->segundo_apellido ?? '') . ' ' . ($persona->nombres ?? ''));
    $sexoRaw = strtoupper(trim($persona->sexo->sexo ?? $persona->sexo->nombre ?? ''));
    $sexoPdf = str_contains($sexoRaw, 'MASCUL') ? 'MASCULINO' : (str_contains($sexoRaw, 'FEMEN') ? 'FEMENINO' : '---');
@endphp

<div class="page">
    <table class="header-table">
        <tr>
            <td colspan="3" class="header-title">
                <div class="line-1">UNIVERSIDAD TECNICA PRIVADA COSMOS</div>
                <div class="line-2">UNITEPC</div>
                <div class="line-3">CURRICULUM VITAE</div>
            </td>
        </tr>
        <tr>
            <td style="width: 22%; vertical-align: middle;">
                <div class="logo-box">
                    @if($escudoBase64)
                        <img src="data:image/png;base64,{{ $escudoBase64 }}" alt="Escudo UNITEPC">
                    @endif
                </div>
            </td>
            <td style="width: 56%; text-align: center; vertical-align: middle;">&nbsp;</td>
            <td style="width: 22%; vertical-align: top;">
                <div class="photo-wrap">
                    <div class="photo-caption">Fotografia personal</div>
                    <div class="photo-box">
                        @if($fotoBase64)
                            <img src="data:image/jpeg;base64,{{ $fotoBase64 }}" alt="Fotografia">
                        @else
                            <div class="no-photo">FOTOGRAFIA<br>PERSONAL</div>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">I. Datos Personales</div>
    <table class="data-table">
        <tr><td class="lbl">Nombre completo</td><td class="val" colspan="3">{{ $fullName ?: '---' }}</td></tr>
        <tr><td class="lbl">Documento de identidad</td><td class="val">{{ $persona->ci }} {{ $persona->expedido->sigla ?? '' }}</td><td class="lbl">Sexo</td><td class="val">{{ $sexoPdf }}</td></tr>
        <tr><td class="lbl">Fecha de nacimiento</td><td class="val">{{ $fmt($persona->fecha_nacimiento) }}</td><td class="lbl">Nacionalidad</td><td class="val">{{ $persona->nacionalidad->nombre ?? $persona->nacionalidad->gentilicio ?? $persona->pais->nombre ?? '---' }}</td></tr>
        <tr><td class="lbl">Pais</td><td class="val">{{ $persona->pais->nombre ?? '---' }}</td><td class="lbl">Telefono personal</td><td class="val">{{ $persona->celular_personal ?: '---' }}</td></tr>
        <tr><td class="lbl">Ciudad / sede de origen</td><td class="val" colspan="3">{{ $persona->expedido->nombre ?? ($persona->ciudad->departamento->nombre ?? '---') }} / {{ $persona->ciudad->nombre ?? '---' }}</td></tr>
        <tr><td class="lbl">Direccion de domicilio</td><td class="val" colspan="3">{{ $persona->direccion_domicilio ?: '---' }}</td></tr>
        <tr><td class="lbl">Correo electronico personal</td><td class="val val-normal" colspan="3">{{ $persona->correo_personal ?: '---' }}</td></tr>
    </table>

    <div class="section-title">II. Formacion Academica</div>
    <div class="sub-title">A. Estudios de pregrado</div>
    @if(count($persona->formacionPregrado) > 0)
        @foreach($persona->formacionPregrado as $i => $m)
            <div class="record-block">
                <table class="record-table">
                    <tr><td class="record-title">{{ $i + 1 }}</td><td class="record-main" colspan="5">{{ $m->carrera ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Institucion</td><td class="record-meta-value" colspan="2">{{ $m->institucion ?? '---' }}</td><td class="record-meta-label">Nivel academico</td><td class="record-meta-value" colspan="2">{{ $m->nivel ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Departamento</td><td class="record-meta-value">{{ $m->depto->nombre ?? '---' }}</td><td class="record-meta-label">Pais</td><td class="record-meta-value">{{ $m->depto->pais->nombre ?? '---' }}</td><td class="record-meta-label">Fecha diploma</td><td class="record-meta-value">{{ $fmt($m->fecha_diploma) }}</td></tr>
                    <tr>
                        <td class="record-meta-label">Fecha titulo</td><td class="record-meta-value">{{ $fmt($m->fecha_titulo) }}</td>
                        <td class="record-meta-label">Diploma academico</td>
                        <td class="evidence-cell">@if($m->archivo_diploma)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(48)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_diploma))) }}" alt="QR diploma"><div class="evidence-text">Ver respaldo</div>@else<div class="evidence-text">Sin respaldo</div>@endif</td>
                        <td class="record-meta-label">Titulo provision nacional</td>
                        <td class="evidence-cell">@if($m->archivo_titulo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(48)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_titulo))) }}" alt="QR titulo"><div class="evidence-text">Ver respaldo</div>@else<div class="evidence-text">Sin respaldo</div>@endif</td>
                    </tr>
                </table>
            </div>
        @endforeach
    @else
        <table class="record-table"><tr><td class="val">SIN REGISTROS DE PREGRADO</td></tr></table>
    @endif

    <div class="sub-title">B. Estudios de postgrado</div>
    @if(count($persona->formacionPostgrado) > 0)
        @foreach($persona->formacionPostgrado as $i => $m)
            <div class="record-block">
                <table class="record-table">
                    <tr><td class="record-title">{{ $i + 1 }}</td><td class="record-main" colspan="5">{{ $m->nombre_programa ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Tipo</td><td class="record-meta-value">{{ $m->tipo ?? '---' }}</td><td class="record-meta-label">Institucion</td><td class="record-meta-value" colspan="3">{{ $m->institucion ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Departamento</td><td class="record-meta-value">{{ $m->depto->nombre ?? '---' }}</td><td class="record-meta-label">Pais</td><td class="record-meta-value">{{ $m->depto->pais->nombre ?? '---' }}</td><td class="record-meta-label">Fecha certificacion</td><td class="record-meta-value">{{ $fmt($m->fecha_certificacion ?? $m->fecha_emision ?? $m->fecha_diploma) }}</td></tr>
                    <tr><td class="record-meta-label">Respaldo de postgrado</td><td class="evidence-cell" colspan="5">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(48)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}" alt="QR posgrado"><div class="evidence-text">Ver respaldo</div>@else<div class="evidence-text">Sin respaldo</div>@endif</td></tr>
                </table>
            </div>
        @endforeach
    @else
        <table class="record-table"><tr><td class="val">SIN REGISTROS DE POSGRADO</td></tr></table>
    @endif

    <div class="section-title">III. Trayectoria Academica y Profesional</div>
    <div class="sub-title">A. Docencia universitaria</div>
    @if(count($persona->experienciaDocente) > 0)
        @foreach($persona->experienciaDocente as $i => $m)
            <div class="record-block">
                <table class="record-table">
                    <tr><td class="record-title">{{ $i + 1 }}</td><td class="record-main" colspan="5">{{ $m->institucion ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Carrera / facultad</td><td class="record-meta-value" colspan="2">{{ $m->carrera ?? '---' }}</td><td class="record-meta-label">Gestion / periodo</td><td class="record-meta-value" colspan="2">{{ $m->gestion_periodo ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Departamento</td><td class="record-meta-value">{{ $m->depto->nombre ?? '---' }}</td><td class="record-meta-label">Pais</td><td class="record-meta-value">{{ $m->depto->pais->nombre ?? '---' }}</td><td class="record-meta-label">Respaldo</td><td class="evidence-cell">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(48)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}" alt="QR docencia"><div class="evidence-text">Ver respaldo</div>@else<div class="evidence-text">Sin respaldo</div>@endif</td></tr>
                    <tr><td class="record-meta-label">Asignaturas desarrolladas</td><td class="record-note" colspan="5">{{ $m->materia ?? $m->asignaturas ?? '---' }}</td></tr>
                </table>
            </div>
        @endforeach
    @else
        <table class="record-table"><tr><td class="val">SIN EXPERIENCIA DOCENTE REGISTRADA</td></tr></table>
    @endif

    <div class="sub-title">B. Ejercicio profesional</div>
    @if(count($persona->experienciaProfesional) > 0)
        @foreach($persona->experienciaProfesional as $i => $m)
            <div class="record-block">
                <table class="record-table">
                    <tr><td class="record-title">{{ $i + 1 }}</td><td class="record-main" colspan="5">{{ $m->empresa ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Cargo desempenado</td><td class="record-meta-value" colspan="2">{{ $m->cargo ?? '---' }}</td><td class="record-meta-label">Periodo</td><td class="record-meta-value" colspan="2">{{ $fmt($m->fecha_inicio) }} a {{ $m->fecha_fin ? $fmt($m->fecha_fin) : 'ACTUAL' }}</td></tr>
                    <tr><td class="record-meta-label">Departamento</td><td class="record-meta-value">{{ $m->depto->nombre ?? '---' }}</td><td class="record-meta-label">Pais</td><td class="record-meta-value">{{ $m->depto->pais->nombre ?? '---' }}</td><td class="record-meta-label">Respaldo</td><td class="evidence-cell">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(48)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}" alt="QR profesional"><div class="evidence-text">Ver respaldo</div>@else<div class="evidence-text">Sin respaldo</div>@endif</td></tr>
                </table>
            </div>
        @endforeach
    @else
        <table class="record-table"><tr><td class="val">SIN EXPERIENCIA PROFESIONAL REGISTRADA</td></tr></table>
    @endif

    <div class="section-title">IV. Capacitaciones y Cursos Recientes</div>
    @if(count($persona->capacitaciones) > 0)
        @foreach($persona->capacitaciones as $i => $m)
            <div class="record-block">
                <table class="record-table">
                    <tr><td class="record-title">{{ $i + 1 }}</td><td class="record-main" colspan="5">{{ $m->nombre_curso ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Institucion</td><td class="record-meta-value" colspan="2">{{ $m->institucion ?: '---' }}</td><td class="record-meta-label">Horas</td><td class="record-meta-value">{{ $m->horas_academicas ?? $m->carga_horaria ?? 'N/R' }}</td><td class="evidence-cell" rowspan="2">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(48)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}" alt="QR capacitacion"><div class="evidence-text">Ver respaldo</div>@else<div class="evidence-text">Sin respaldo</div>@endif</td></tr>
                    <tr><td class="record-meta-label">Ubicacion</td><td class="record-meta-value" colspan="2">{{ $m->depto->nombre ?? '---' }} / {{ $m->depto->pais->nombre ?? '---' }}</td><td class="record-meta-label">Fecha</td><td class="record-meta-value">{{ $fmt($m->fecha) }}</td></tr>
                </table>
            </div>
        @endforeach
    @else
        <table class="record-table"><tr><td class="val">SIN CAPACITACIONES REGISTRADAS</td></tr></table>
    @endif

    <div class="section-title">V. Produccion Intelectual</div>
    @if(count($persona->produccionIntelectual) > 0)
        @foreach($persona->produccionIntelectual as $i => $m)
            <div class="record-block">
                <table class="record-table">
                    <tr><td class="record-title">{{ $i + 1 }}</td><td class="record-main" colspan="5">{{ $m->titulo ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Tipo</td><td class="record-meta-value">{{ $m->tipo_produccion ?? $m->tipo ?? '---' }}</td><td class="record-meta-label">Editorial / medio</td><td class="record-meta-value" colspan="3">{{ $m->editorial ?: '---' }}</td></tr>
                    <tr><td class="record-meta-label">Ubicacion</td><td class="record-meta-value" colspan="2">{{ $m->depto->nombre ?? '---' }} / {{ $m->depto->pais->nombre ?? '---' }}</td><td class="record-meta-label">Fecha / ano</td><td class="record-meta-value">{{ $m->fecha ? $fmt($m->fecha) : ($m->anio ?: '---') }}</td><td class="evidence-cell">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(48)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}" alt="QR produccion"><div class="evidence-text">Ver respaldo</div>@else<div class="evidence-text">Sin respaldo</div>@endif</td></tr>
                </table>
            </div>
        @endforeach
    @else
        <table class="record-table"><tr><td class="val">SIN PRODUCCION INTELECTUAL REGISTRADA</td></tr></table>
    @endif

    <div class="section-title">VI. Reconocimientos y Distinciones</div>
    @if(count($persona->reconocimientos) > 0)
        @foreach($persona->reconocimientos as $i => $m)
            <div class="record-block">
                <table class="record-table">
                    <tr><td class="record-title">{{ $i + 1 }}</td><td class="record-main" colspan="3">{{ $m->titulo_premio ?? '---' }}</td></tr>
                    <tr><td class="record-meta-label">Institucion otorgante</td><td class="record-meta-value">{{ $m->institucion_otorgante ?: '---' }}</td><td class="record-meta-label">Fecha</td><td class="record-meta-value">{{ $fmt($m->fecha) }}</td></tr>
                    <tr><td class="record-meta-label">Respaldo</td><td class="evidence-cell" colspan="3">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(48)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}" alt="QR reconocimiento"><div class="evidence-text">Ver respaldo</div>@else<div class="evidence-text">Sin respaldo</div>@endif</td></tr>
                </table>
            </div>
        @endforeach
    @else
        <table class="record-table"><tr><td class="val">SIN RECONOCIMIENTOS REGISTRADOS</td></tr></table>
    @endif

    <div class="section-title">VII. Conocimiento de Idiomas</div>
    <table class="languages-table">
        <tr><th style="width: 25%;">Idioma</th><th style="width: 25%;">Lectura</th><th style="width: 25%;">Escritura</th><th style="width: 25%;">Habla</th></tr>
        @if(count($persona->idiomas) > 0)
            @foreach($persona->idiomas as $m)
                <tr><td class="val">{{ $m->idioma }}</td><td class="val">{{ $m->nivel_lee }}</td><td class="val">{{ $m->nivel_escritura }}</td><td class="val">{{ $m->nivel_habla }}</td></tr>
            @endforeach
        @else
            <tr><td class="val" colspan="4">SIN IDIOMAS REGISTRADOS</td></tr>
        @endif
    </table>

    <table class="footer-table">
        <tr>
            <td style="width: 35%;"><div class="signature-line"></div><div class="footer-label">Firma del interesado</div></td>
            <td style="width: 30%;">@if($qrCode)<img src="data:image/svg+xml;base64,{{ base64_encode($qrCode) }}" style="width: 95px; height: 95px;" alt="QR verificacion">@endif<div class="footer-label">Verificacion digital</div></td>
            <td style="width: 35%;"><div class="footer-date">Fecha de emision: {{ now()->format('d/m/Y') }}</div></td>
        </tr>
    </table>
    <div class="footer-brand">SIGETH - UNITEPC</div>
</div>

@if(count($adjuntos) > 0)
    <div style="page-break-before: always;">
        <div class="section-title">Anexos y Documentacion de Respaldo</div>
        @foreach($adjuntos as $adj)
            @if($adj['type'] === 'image' && file_exists($adj['path']))
                <div class="record-block" style="margin-bottom: 20px;">
                    <div class="sub-title">{{ $adj['label'] }}</div>
                    <img src="{{ $adj['path'] }}" alt="{{ $adj['label'] }}" class="annex-image">
                </div>
            @endif
        @endforeach
    </div>
@endif
</body>
</html>
