<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CV Normalizado - {{ $persona->primer_apellido }} {{ $persona->nombres }}</title>
    <style>
        @page {
            size: 216mm 330mm;
            margin: 2cm 2cm 2cm 2.5cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.5;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            border: 1.5px solid #4A148C;
        }
        td, th {
            border: 1px solid #4A148C;
            padding: 6px 8px;
            vertical-align: middle;
            text-align: center;
        }
        .section-header {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #4A148C;
            color: #4A148C;
            padding: 4px;
            margin-top: 25px;
            margin-bottom: 10px;
            page-break-after: avoid;
        }
        .lbl {
            background-color: #fff;
            font-size: 9px;
            color: #4A148C;
            font-weight: bold;
            text-align: center;
        }
        .val {
            background-color: #ffffff;
            text-transform: uppercase;
            color: #000;
            font-weight: bold;
            text-align: center;
        }
        .val-bold {
            background-color: #ffffff;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 11px;
            color: #000;
        }
        .header-table {
            margin-bottom: 25px;
            border: none !important;
        }
        .header-table td {
            border: none !important;
            padding: 0;
        }
        .univ-name {
            font-size: 14px;
            text-decoration: underline;
            font-weight: bold;
            color: #4A148C;
        }
        .photo-box {
            border: 1.5px solid #4A148C;
            width: 95px;
            height: 120px;
            text-align: center;
            margin-left: auto;
            background-color: #eee;
            overflow: hidden;
            position: relative;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .no-photo {
            font-size: 8px;
            color: #666;
            font-style: italic;
            line-height: 12px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
        }
        .sub-title {
            font-size: 10px;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
            color: #4A148C;
            page-break-after: avoid;
        }
        .registro-block {
            page-break-inside: avoid;
            margin-bottom: 15px;
        }
        .qr-cell {
            width: 50px;
            padding: 2px !important;
        }
        .qr-cell img {
            width: 45px;
            height: 45px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ENCABEZADO --}}
    <table class="header-table">
        <tr>
            <td colspan="3" style="text-align: center; vertical-align: middle; padding-bottom: 15px; color: #4A148C;">
                <div class="univ-name">UNIVERSIDAD TÉCNICA PRIVADA COSMOS "UNITEPC"</div>
                <div style="margin-top: 4px; font-weight: bold; font-size: 11px;">
                    DIRECCIÓN DE PLANIFICACIÓN Y EVALUACIÓN INSTITUCIONAL
                </div>
                <div style="margin-top: 8px; font-size: 13px; text-decoration: underline; font-weight: bold;">
                    CURRÍCULUM VITAE NORMALIZADO
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 20%; text-align: left; vertical-align: middle;">
                @php
                    $escudoPath = public_path('unitepc_escudo.png');
                    $escudoBase64 = file_exists($escudoPath) ? base64_encode(file_get_contents($escudoPath)) : '';
                @endphp
                @if($escudoBase64)
                    <img src="data:image/png;base64,{{ $escudoBase64 }}" style="width: 110px; height: auto;">
                @endif
            </td>
            <td style="width: 60%;"></td>
            <td style="width: 20%; text-align: right; vertical-align: middle;">
                <div class="photo-box">
                    @php
                        $fotoBase64 = '';
                        if (!empty($persona->foto)) {
                            $cleanFoto = str_replace('/storage/', '', $persona->foto);
                            $paths = [ public_path('storage/' . $cleanFoto), storage_path('app/public/' . $cleanFoto), storage_path('app/' . $cleanFoto) ];
                            foreach ($paths as $path) { if (file_exists($path)) { $fotoBase64 = base64_encode(file_get_contents($path)); break; } }
                        }
                    @endphp
                    @if($fotoBase64)
                        <img src="data:image/jpeg;base64,{{ $fotoBase64 }}">
                    @else
                        <div class="no-photo">FOTOGRAFÍA<br>PERSONAL</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- I. DATOS PERSONALES --}}
    <table style="margin-top: 10px;">
        <tr><td colspan="4" class="section-header" style="margin-top: 0; margin-bottom: 0;">I. DATOS PERSONALES</td></tr>
        <tr><td class="lbl">Primer Apellido</td><td class="lbl">Segundo Apellido</td><td class="lbl">Nombres</td><td class="lbl">N° Documento (CI)</td></tr>
        <tr><td class="val-bold">{{ $persona->primer_apellido }}</td><td class="val">{{ $persona->segundo_apellido ?: '--' }}</td><td class="val-bold">{{ $persona->nombres }}</td><td class="val">{{ $persona->ci }} {{ $persona->expedido->sigla ?? '' }}</td></tr>
        <tr><td class="lbl">Sexo</td><td class="lbl" colspan="2">Fecha de nacimiento (Día/Mes/Año)</td><td class="lbl">Nacionalidad</td></tr>
        <tr><td class="val">{{ ($persona->sexo->nombre ?? '') == 'Masculino' ? 'MASCULINO' : 'FEMENINO' }}</td><td class="val" colspan="2"><strong>{{ $persona->fecha_nacimiento ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y') : '---' }}</strong></td><td class="val">{{ $persona->nacionalidad->nombre ?? 'BOLIVIANA' }}</td></tr>
        <tr><td class="lbl" colspan="2">Dirección de Domicilio</td><td class="lbl">Ciudad / Sede de Origen</td><td class="lbl">País</td></tr>
        <tr><td class="val" colspan="2">{{ $persona->direccion_domicilio ?: 'N/R' }}</td><td class="val">{{ $persona->expedido->nombre ?? ($persona->ciudad->departamento->nombre ?? 'N/R') }} / {{ $persona->ciudad->nombre ?? 'COCHABAMBA' }}</td><td class="val">{{ $persona->pais->nombre ?? 'BOLIVIA' }}</td></tr>
        <tr><td class="lbl" colspan="4">Dirección Electrónica Institucional / Personal</td></tr>
        <tr><td class="val" colspan="4" style="text-transform: none;">{{ $persona->correo_personal ?: '---' }}</td></tr>
    </table>

    {{-- II. FORMACIÓN ACADÉMICA --}}
    <div class="section-header">II. FORMACIÓN ACADÉMICA</div>
    <span class="sub-title">A. ESTUDIOS DE PREGRADO (LICENCIATURAS / TÉCNICOS)</span>
    @if(count($persona->formacionPregrado) > 0)
        @foreach($persona->formacionPregrado as $i => $m)
            <div class="registro-block">
                <table>
                    <tr><td class="lbl" style="width: 40%;">{{ $i+1 }}. Carrera / Profesión</td><td class="lbl" style="width: 15%;">Fecha Diploma</td><td class="lbl" style="width: 15%;">Fecha Título</td><td class="lbl" style="width: 15%;">Diploma</td><td class="lbl" style="width: 15%;">Título P.N.</td></tr>
                    <tr>
                        <td class="val-bold">{{ $m->carrera ?? '--' }}</td>
                        <td class="val">{{ $m->fecha_diploma ? \Carbon\Carbon::parse($m->fecha_diploma)->format('d/m/Y') : '---' }}</td>
                        <td class="val-bold">{{ $m->fecha_titulo ? \Carbon\Carbon::parse($m->fecha_titulo)->format('d/m/Y') : '---' }}</td>
                        <td class="qr-cell">@if($m->archivo_diploma)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(45)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_diploma))) }}">@endif</td>
                        <td class="qr-cell">@if($m->archivo_titulo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(45)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_titulo))) }}">@endif</td>
                    </tr>
                    <tr><td class="lbl">Universidad / Institución</td><td class="lbl">Grado Académico</td><td class="lbl" colspan="3">Ubicación (Departamento / País)</td></tr>
                    <tr><td class="val">{{ $m->institucion ?? '--' }}</td><td class="val">{{ $m->nivel ?? '---' }}</td><td class="val" colspan="3">{{ $m->depto->nombre ?? 'N/R' }} / {{ $m->depto->pais->nombre ?? 'BOLIVIA' }}</td></tr>
                </table>
            </div>
        @endforeach
    @endif

    <span class="sub-title" style="margin-top: 15px;">B. ESTUDIOS DE POSGRADO</span>
    @if(count($persona->formacionPostgrado) > 0)
        @foreach($persona->formacionPostgrado as $i => $m)
            <div class="registro-block">
                <table>
                    <tr><td class="lbl" style="width: 45%;">{{ $i+1 }}. Nombre del Programa</td><td class="lbl" style="width: 25%;">Fecha Certificación</td><td class="lbl" style="width: 15%;">Tipo</td><td class="lbl" style="width: 15%;">Respaldo</td></tr>
                    <tr>
                        <td class="val-bold">{{ $m->nombre_programa ?? '--' }}</td>
                        <td class="val">{{ $m->fecha_certificacion ? \Carbon\Carbon::parse($m->fecha_certificacion)->format('d/m/Y') : ($m->fecha_diploma ? \Carbon\Carbon::parse($m->fecha_diploma)->format('d/m/Y') : '---') }}</td>
                        <td class="val">{{ $m->tipo ?? '---' }}</td>
                        <td class="qr-cell">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(45)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}">@endif</td>
                    </tr>
                    <tr><td class="lbl" colspan="2">Universidad / Institución</td><td class="lbl" colspan="2">Ubicación (Departamento / País)</td></tr>
                    <tr><td class="val" colspan="2">{{ $m->institucion ?? '--' }}</td><td class="val" colspan="2">{{ $m->depto->nombre ?? 'N/R' }} / {{ $m->depto->pais->nombre ?? 'BOLIVIA' }}</td></tr>
                </table>
            </div>
        @endforeach
    @endif

    {{-- III. TRAYECTORIA ACADEMICA Y PROFESIONAL --}}
    <div class="section-header">III. TRAYECTORIA ACADEMICA Y PROFESIONAL</div>
    <span class="sub-title">A. DOCENCIA UNIVERSITARIA</span>
    @if(count($persona->experienciaDocente) > 0)
        @foreach($persona->experienciaDocente as $i => $m)
            <div class="registro-block">
                <table>
                    <tr><td class="lbl" style="width: 35%;">{{ $i+1 }}. Universidad</td><td class="lbl" style="width: 20%;">Carrera / Facultad</td><td class="lbl" style="width: 20%;">Ubicación</td><td class="lbl" style="width: 13%;">Gestión</td><td class="lbl" style="width: 12%;">Resp.</td></tr>
                    <tr>
                        <td class="val-bold">{{ $m->institucion ?? '--' }}</td>
                        <td class="val">{{ $m->carrera ?? '---' }}</td>
                        <td class="val">{{ $m->depto->nombre ?? '---' }} / {{ $m->depto->pais->nombre ?? 'BOLIVIA' }}</td>
                        <td class="val">{{ $m->gestion_periodo ?? '---' }}</td>
                        <td class="qr-cell">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(45)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}">@endif</td>
                    </tr>
                    <tr><td class="lbl" colspan="5">Asignaturas Desarrolladas</td></tr>
                    <tr><td class="val" colspan="5" style="text-transform: none; font-weight: normal; font-style: italic;">{{ $m->materia ?? $m->asignaturas ?? '---' }}</td></tr>
                </table>
            </div>
        @endforeach
    @endif

    <span class="sub-title" style="margin-top: 15px;">B. EJERCICIO PROFESIONAL</span>
    @if(count($persona->experienciaProfesional) > 0)
        @foreach($persona->experienciaProfesional as $i => $m)
            <div class="registro-block">
                <table>
                    <tr><td class="lbl" style="width: 30%;">{{ $i+1 }}. Empresa / Institución</td><td class="lbl" style="width: 20%;">Cargo</td><td class="lbl" style="width: 20%;">Ubicación</td><td class="lbl" style="width: 20%;">Periodo</td><td class="lbl" style="width: 10%;">Resp.</td></tr>
                    <tr>
                        <td class="val-bold">{{ $m->empresa ?? '--' }}</td>
                        <td class="val">{{ $m->cargo ?? '--' }}</td>
                        <td class="val">{{ $m->depto->nombre ?? '---' }} / {{ $m->depto->pais->nombre ?? 'BOLIVIA' }}</td>
                        <td class="val" style="font-size: 8px;">{{ $m->fecha_inicio ? \Carbon\Carbon::parse($m->fecha_inicio)->format('d/m/Y') : '---' }} a {{ $m->fecha_fin ? \Carbon\Carbon::parse($m->fecha_fin)->format('d/m/Y') : 'ACTUAL' }}</td>
                        <td class="qr-cell">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(45)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}">@endif</td>
                    </tr>
                </table>
            </div>
        @endforeach
    @endif

    {{-- IV. CAPACITACIONES --}}
    <div class="section-header">IV. CAPACITACIONES Y CURSOS RECIENTES</div>
    @if(count($persona->capacitaciones) > 0)
        @foreach($persona->capacitaciones as $i => $m)
            <div class="registro-block">
                <table>
                    <tr><td class="lbl" style="width: 35%;">{{ $i+1 }}. Nombre del Curso / Taller</td><td class="lbl" style="width: 25%;">Institución</td><td class="lbl" style="width: 20%;">Ubicación</td><td class="lbl" style="width: 10%;">Hrs.</td><td class="lbl" style="width: 10%;">Resp.</td></tr>
                    <tr>
                        <td class="val-bold">{{ $m->nombre_curso ?? '--' }}</td>
                        <td class="val">{{ $m->institucion ?: '---' }}</td>
                        <td class="val">{{ $m->depto->nombre ?? '---' }} / {{ $m->depto->pais->nombre ?? 'BOLIVIA' }}</td>
                        <td class="val">{{ $m->horas_academicas ?? $m->carga_horaria ?? 'N/R' }}</td>
                        <td class="qr-cell">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(45)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}">@endif</td>
                    </tr>
                    <tr><td class="lbl" colspan="5">Fecha de Emisión / Certificación</td></tr>
                    <tr><td class="val" colspan="5">{{ $m->fecha ? \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') : '---' }}</td></tr>
                </table>
            </div>
        @endforeach
    @endif

    {{-- V. PRODUCCION INTELECTUAL --}}
    <div class="section-header">V. PRODUCCIÓN INTELECTUAL (LIBROS / ARTÍCULOS)</div>
    @if(count($persona->produccionIntelectual) > 0)
        @foreach($persona->produccionIntelectual as $i => $m)
            <div class="registro-block">
                <table style="table-layout: fixed;">
                    <tr>
                        <th class="lbl" style="width: 30%;">{{ $i+1 }}. Título de la Obra</th>
                        <th class="lbl" style="width: 10%;">Tipo</th>
                        <th class="lbl" style="width: 20%;">Editorial / Medio</th>
                        <th class="lbl" style="width: 20%;">Ubicación (Depto/País)</th>
                        <th class="lbl" style="width: 12%;">Fecha / Año</th>
                        <th class="lbl" style="width: 8%;">Resp.</th>
                    </tr>
                    <tr>
                        <td class="val-bold" style="font-size: 8px;">{{ $m->titulo ?? '--' }}</td>
                        <td class="val" style="font-size: 8px;">{{ $m->tipo_produccion ?? $m->tipo ?? '---' }}</td>
                        <td class="val" style="font-size: 8px;">{{ $m->editorial ?: '---' }}</td>
                        <td class="val" style="font-size: 8px;">{{ $m->depto->nombre ?? '---' }} / {{ $m->depto->pais->nombre ?? 'ARGENTINA' }}</td>
                        <td class="val" style="font-size: 8px;">{{ $m->fecha ? \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') : ($m->anio ?: '---') }}</td>
                        <td class="qr-cell">@if($m->archivo_respaldo)<img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(45)->margin(0)->generate(config('app.url').'/'.str_replace('/storage/','',$m->archivo_respaldo))) }}">@endif</td>
                    </tr>
                </table>
            </div>
        @endforeach
    @endif

    {{-- VI. RECONOCIMIENTOS --}}
    <div class="section-header">VI. RECONOCIMIENTOS Y DISTINCIONES</div>
    @if(count($persona->reconocimientos) > 0)
        @foreach($persona->reconocimientos as $i => $m)
            <div class="registro-block">
                <table>
                    <tr><td class="lbl" style="width: 45%;">{{ $i+1 }}. Título o Mérito Obtenido</td><td class="lbl" style="width: 35%;">Institución Otorgante</td><td class="lbl" style="width: 20%;">Fecha / Gestión</td></tr>
                    <tr><td class="val-bold">{{ $m->titulo_premio ?? '--' }}</td><td class="val">{{ $m->institucion_otorgante ?: '---' }}</td><td class="val">{{ $m->fecha ? \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') : '---' }}</td></tr>
                </table>
            </div>
        @endforeach
    @endif

    {{-- VII. IDIOMAS --}}
    <div class="section-header">VII. CONOCIMIENTO DE IDIOMAS</div>
    <table>
        <tr><td class="lbl">Idioma</td><td class="lbl">Lectura</td><td class="lbl">Escritura</td><td class="lbl">Habla</td></tr>
        @if(count($persona->idiomas) > 0)
            @foreach($persona->idiomas as $m)
                <tr><td class="val">{{ $m->idioma }}</td><td class="val">{{ $m->nivel_lee }}</td><td class="val">{{ $m->nivel_escritura }}</td><td class="val">{{ $m->nivel_habla }}</td></tr>
            @endforeach
        @endif
    </table>

    {{-- FIRMAS Y QR FINAL --}}
    <div class="footer-qr-container">
        <table style="border: none !important; margin-top: 30px;">
            <tr>
                <td style="width: 35%; border: none !important;">
                    <div style="border-top: 1px solid #4A148C; width: 160px; margin: 0 auto; margin-top: 60px;"></div>
                    <div style="font-weight: bold; font-size: 9pt; color: #4A148C;">FIRMA DEL INTERESADO</div>
                </td>
                <td style="width: 30%; border: none !important;">
                    <img src="data:image/svg+xml;base64,{{ base64_encode($qrCode) }}" style="width: 100px; height: 100px;">
                    <div style="font-size: 7pt; color: #4A148C; font-weight: bold; margin-top: 5px;">VERIFICACIÓN DIGITAL</div>
                </td>
                <td style="width: 35%; border: none !important;">
                    <div style="margin-top: 60px; font-weight: bold; font-size: 9pt; color: #4A148C;">FECHA DE EMISIÓN: {{ now()->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
        <div style="text-align: center; font-size: 7px; color: #aaa; margin-top: 40px; font-style: italic; text-transform: uppercase;">SIGETH - UNITEPC</div>
    </div>

</div>

    @if(count($adjuntos) > 0)
        <div style="page-break-before: always;">
            <div class="section-header">ANEXOS Y DOCUMENTACIÓN DE RESPALDO</div>
            @foreach($adjuntos as $adj)
                @if($adj['type'] === 'image' && file_exists($adj['path']))
                    <div class="registro-block" style="page-break-inside: avoid; margin-bottom: 25px;">
                        <div class="sub-title">{{ $adj['label'] }}</div>
                        <img src="{{ $adj['path'] }}" style="max-width: 100%; height: auto; border: 1px solid #4A148C;">
                    </div>
                @endif
            @endforeach
        </div>
    @endif

</body>
</html>
