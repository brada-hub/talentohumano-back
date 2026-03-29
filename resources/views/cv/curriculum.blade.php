<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CV - {{ $persona->primer_apellido }} {{ $persona->nombres }}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #222;
            line-height: 1.4;
        }

        /* ─── ENCABEZADO ─── */
        .header {
            text-align: center;
            border-bottom: 3px solid #4A0E78;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 22pt;
            color: #4A0E78;
            letter-spacing: 3px;
            margin-bottom: 2px;
        }
        .header h2 {
            font-size: 11pt;
            color: #333;
            font-weight: normal;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .header h3 {
            font-size: 14pt;
            color: #4A0E78;
            letter-spacing: 2px;
        }

        /* ─── ESCUDO + FOTO ─── */
        .identity-bar {
            width: 100%;
            margin-bottom: 15px;
        }
        .identity-bar td {
            vertical-align: middle;
        }
        .escudo-img {
            width: 90px;
            height: auto;
        }
        .foto-img {
            width: 100px;
            height: 120px;
            object-fit: cover;
            border: 2px solid #4A0E78;
        }
        .foto-placeholder {
            width: 100px;
            height: 120px;
            border: 2px solid #4A0E78;
            background: #f0f0f0;
            text-align: center;
            line-height: 120px;
            color: #999;
            font-size: 8pt;
        }
        .identity-name {
            font-size: 14pt;
            font-weight: bold;
            color: #4A0E78;
        }
        .identity-cargo {
            font-size: 10pt;
            color: #666;
            font-style: italic;
        }

        /* ─── SECCIONES ─── */
        .section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .section-title {
            background: #4A0E78;
            color: #fff;
            padding: 5px 10px;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .section-subtitle {
            font-size: 9pt;
            color: #666;
            font-style: italic;
            margin-bottom: 4px;
            padding-left: 10px;
        }

        /* ─── TABLAS DE DATOS ─── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9pt;
        }
        .data-table th {
            background: #E8D5F5;
            color: #4A0E78;
            padding: 5px 6px;
            border: 1px solid #C9A0DC;
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 4px 6px;
            border: 1px solid #D0D0D0;
            text-align: center;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background: #FAFAFA;
        }

        /* ─── DATOS PERSONALES ─── */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .info-grid td {
            padding: 3px 6px;
            border: 1px solid #D0D0D0;
            font-size: 9pt;
        }
        .info-grid .label {
            background: #F3E8FC;
            color: #4A0E78;
            font-weight: bold;
            width: 25%;
            font-size: 8pt;
        }
        .info-grid .value {
            width: 25%;
        }

        /* ─── QR ─── */
        .qr-section {
            text-align: center;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .qr-section p {
            font-size: 8pt;
            color: #666;
            margin-top: 4px;
        }

        /* ─── ADJUNTOS ─── */
        .attachment-page {
            page-break-before: always;
        }
        .attachment-header {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            color: #4A0E78;
            margin-bottom: 10px;
            border-bottom: 2px solid #4A0E78;
            padding-bottom: 5px;
        }
        .attachment-item {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .attachment-label {
            font-size: 9pt;
            font-weight: bold;
            color: #4A0E78;
            margin-bottom: 3px;
        }
        .attachment-img {
            max-width: 100%;
            max-height: 700px;
            border: 1px solid #ccc;
        }

        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .small { font-size: 8pt; color: #666; }
        .no-data { color: #999; font-style: italic; }

        .footer-line {
            border-top: 2px solid #4A0E78;
            margin-top: 15px;
            padding-top: 5px;
            text-align: center;
            font-size: 7pt;
            color: #888;
        }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════ --}}
    {{-- ENCABEZADO --}}
    {{-- ═══════════════════════════════ --}}
    <div class="header">
        <h1>UNITEPC</h1>
        <h2>UNIVERSIDAD TÉCNICA PRIVADA COSMOS</h2>
        <h3>CURRICULUM VITAE NORMALIZADO</h3>
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- ESCUDO + NOMBRE + FOTO --}}
    {{-- ═══════════════════════════════ --}}
    <table class="identity-bar">
        <tr>
            <td style="width: 100px;">
                @if(file_exists(public_path('unitepc_escudo.png')))
                    <img src="{{ public_path('unitepc_escudo.png') }}" class="escudo-img" alt="Escudo UNITEPC">
                @endif
            </td>
            <td style="text-align: center;">
                <div class="identity-name" style="font-size: 11pt; color: #4A0E78; text-transform: uppercase;">
                    FOTOGRAFÍA<br>PERSONAL:
                </div>
            </td>
            <td style="width: 110px; text-align: right;">
                @if($persona->foto && file_exists(public_path(str_replace('/storage/', 'storage/', $persona->foto))))
                    <img src="{{ public_path(str_replace('/storage/', 'storage/', $persona->foto)) }}" class="foto-img" alt="Foto">
                @else
                    <div class="foto-placeholder">SIN FOTO</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ═══════════════════════════════ --}}
    {{-- I. DATOS PERSONALES --}}
    {{-- ═══════════════════════════════ --}}
    <div class="section">
        <div class="section-title">I. DATOS PERSONALES</div>
        <table class="info-grid">
            <tr>
                <td class="label">Primer Apellido</td>
                <td class="value">{{ $persona->primer_apellido ?? '-' }}</td>
                <td class="label">Segundo Apellido</td>
                <td class="value">{{ $persona->segundo_apellido ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nombres</td>
                <td class="value">{{ $persona->nombres ?? '-' }}</td>
                <td class="label">Cédula de Identidad</td>
                <td class="value">{{ $persona->ci ?? '-' }} {{ $persona->complemento ? '- '.$persona->complemento : '' }}</td>
            </tr>
            <tr>
                <td class="label">Expedido en</td>
                <td class="value">{{ $persona->expedido->nombre ?? '-' }}</td>
                <td class="label">Sexo</td>
                <td class="value">{{ $persona->sexo->sexo ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Fecha de Nacimiento</td>
                <td class="value">{{ $persona->fecha_nacimiento ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y') : '-' }}</td>
                <td class="label">Estado Civil</td>
                <td class="value">{{ $persona->estado_civil ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nacionalidad</td>
                <td class="value">{{ $persona->nacionalidad->gentilicio ?? '-' }}</td>
                <td class="label">País</td>
                <td class="value">{{ $persona->pais->nombre ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Ciudad de Residencia</td>
                <td class="value">{{ $persona->ciudad->nombre ?? '-' }}</td>
                <td class="label">Dirección</td>
                <td class="value">{{ $persona->direccion_domicilio ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Celular Personal</td>
                <td class="value">{{ $persona->celular_personal ?? '-' }}</td>
                <td class="label">Correo Personal</td>
                <td class="value">{{ $persona->correo_personal ?? '-' }}</td>
            </tr>
            @if($empleado)
            <tr>
                <td class="label">Celular Institucional</td>
                <td class="value">{{ $empleado->celular_institucional ?? '-' }}</td>
                <td class="label">Correo Institucional</td>
                <td class="value">{{ $empleado->correo_institucional ?? '-' }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- II. FORMACIÓN ACADÉMICA --}}
    {{-- ═══════════════════════════════ --}}
    <div class="section">
        <div class="section-title">II. FORMACIÓN ACADÉMICA</div>

        {{-- Pregrado --}}
        @if($persona->formacionPregrado && $persona->formacionPregrado->count())
            <div class="section-subtitle">(Títulos de Pregrado)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nivel Académico</th>
                        <th>Universidad / Institución</th>
                        <th>Carrera / Profesión</th>
                        <th>Fecha Diploma</th>
                        <th>Fecha Título</th>
                        <th>Departamento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->formacionPregrado as $fp)
                    <tr>
                        <td>{{ strtoupper($fp->nivel ?? '-') }}</td>
                        <td>{{ $fp->institucion ?? '-' }}</td>
                        <td>{{ $fp->carrera ?? '-' }}</td>
                        <td>{{ $fp->fecha_diploma ? \Carbon\Carbon::parse($fp->fecha_diploma)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $fp->fecha_titulo ? \Carbon\Carbon::parse($fp->fecha_titulo)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $fp->depto->nombre ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Postgrado --}}
        @if($persona->formacionPostgrado && $persona->formacionPostgrado->count())
            <div class="section-subtitle">(Formación de Postgrado)</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Programa</th>
                        <th>Institución</th>
                        <th>Fecha Diploma</th>
                        <th>Departamento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->formacionPostgrado as $fpo)
                    <tr>
                        <td>{{ strtoupper($fpo->tipo ?? '-') }}</td>
                        <td>{{ $fpo->nombre_programa ?? '-' }}</td>
                        <td>{{ $fpo->institucion ?? '-' }}</td>
                        <td>{{ $fpo->fecha_diploma ? \Carbon\Carbon::parse($fpo->fecha_diploma)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $fpo->depto->nombre ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if((!$persona->formacionPregrado || !$persona->formacionPregrado->count()) && (!$persona->formacionPostgrado || !$persona->formacionPostgrado->count()))
            <p class="no-data" style="padding: 5px 10px;">Sin registros de formación académica.</p>
        @endif
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- III. EXPERIENCIA PROFESIONAL --}}
    {{-- ═══════════════════════════════ --}}
    <div class="section">
        <div class="section-title">III. EXPERIENCIA PROFESIONAL</div>

        @if($persona->experienciaProfesional && $persona->experienciaProfesional->count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Empresa / Institución</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Departamento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->experienciaProfesional as $ep)
                    <tr>
                        <td>{{ $ep->cargo ?? '-' }}</td>
                        <td>{{ $ep->empresa ?? '-' }}</td>
                        <td>{{ $ep->fecha_inicio ? \Carbon\Carbon::parse($ep->fecha_inicio)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $ep->fecha_fin ? \Carbon\Carbon::parse($ep->fecha_fin)->format('d/m/Y') : 'Actual' }}</td>
                        <td>{{ $ep->depto->nombre ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data" style="padding: 5px 10px;">Sin registros de experiencia profesional.</p>
        @endif
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- IV. EXPERIENCIA DOCENTE --}}
    {{-- ═══════════════════════════════ --}}
    <div class="section">
        <div class="section-title">IV. EXPERIENCIA DOCENTE</div>

        @if($persona->experienciaDocente && $persona->experienciaDocente->count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Institución</th>
                        <th>Carrera</th>
                        <th>Asignaturas</th>
                        <th>Gestión / Periodo</th>
                        <th>Departamento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->experienciaDocente as $ed)
                    <tr>
                        <td>{{ $ed->institucion ?? '-' }}</td>
                        <td>{{ $ed->carrera ?? '-' }}</td>
                        <td class="text-left">{{ $ed->asignaturas ?? '-' }}</td>
                        <td>{{ $ed->gestion_periodo ?? '-' }}</td>
                        <td>{{ $ed->depto->nombre ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data" style="padding: 5px 10px;">Sin registros de experiencia docente.</p>
        @endif
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- V. CAPACITACIONES --}}
    {{-- ═══════════════════════════════ --}}
    <div class="section">
        <div class="section-title">V. CAPACITACIONES Y CURSOS</div>

        @if($persona->capacitaciones && $persona->capacitaciones->count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre del Curso</th>
                        <th>Institución</th>
                        <th>Fecha</th>
                        <th>Carga Horaria</th>
                        <th>Departamento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->capacitaciones as $cap)
                    <tr>
                        <td>{{ $cap->nombre_curso ?? '-' }}</td>
                        <td>{{ $cap->institucion ?? '-' }}</td>
                        <td>{{ $cap->fecha ? \Carbon\Carbon::parse($cap->fecha)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $cap->carga_horaria ?? 0 }} hrs</td>
                        <td>{{ $cap->depto->nombre ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data" style="padding: 5px 10px;">Sin registros de capacitaciones.</p>
        @endif
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- VI. PRODUCCIÓN INTELECTUAL --}}
    {{-- ═══════════════════════════════ --}}
    <div class="section">
        <div class="section-title">VI. PRODUCCIÓN INTELECTUAL</div>

        @if($persona->produccionIntelectual && $persona->produccionIntelectual->count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Título</th>
                        <th>Fecha</th>
                        <th>Editorial</th>
                        <th>Departamento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->produccionIntelectual as $pi)
                    <tr>
                        <td>{{ strtoupper($pi->tipo ?? '-') }}</td>
                        <td class="text-left">{{ $pi->titulo ?? '-' }}</td>
                        <td>{{ $pi->fecha ? \Carbon\Carbon::parse($pi->fecha)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $pi->editorial ?? '-' }}</td>
                        <td>{{ $pi->depto->nombre ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data" style="padding: 5px 10px;">Sin registros de producción intelectual.</p>
        @endif
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- VII. RECONOCIMIENTOS --}}
    {{-- ═══════════════════════════════ --}}
    <div class="section">
        <div class="section-title">VII. RECONOCIMIENTOS Y PREMIOS</div>

        @if($persona->reconocimientos && $persona->reconocimientos->count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Título / Premio</th>
                        <th>Institución Otorgante</th>
                        <th>Fecha</th>
                        <th>Lugar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->reconocimientos as $rec)
                    <tr>
                        <td>{{ $rec->titulo_premio ?? '-' }}</td>
                        <td>{{ $rec->institucion_otorgante ?? '-' }}</td>
                        <td>{{ $rec->fecha ? \Carbon\Carbon::parse($rec->fecha)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $rec->lugar ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data" style="padding: 5px 10px;">Sin registros de reconocimientos.</p>
        @endif
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- VIII. IDIOMAS --}}
    {{-- ═══════════════════════════════ --}}
    <div class="section">
        <div class="section-title">VIII. IDIOMAS</div>

        @if($persona->idiomas && $persona->idiomas->count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Idioma</th>
                        <th>Nivel Habla</th>
                        <th>Nivel Escritura</th>
                        <th>Nivel Lectura</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($persona->idiomas as $idi)
                    <tr>
                        <td>{{ $idi->idioma ?? '-' }}</td>
                        <td>{{ $idi->nivel_habla ?? '-' }}</td>
                        <td>{{ $idi->nivel_escritura ?? '-' }}</td>
                        <td>{{ $idi->nivel_lee ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="no-data" style="padding: 5px 10px;">Sin registros de idiomas.</p>
        @endif
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- QR DE VERIFICACIÓN --}}
    {{-- ═══════════════════════════════ --}}
    <div class="qr-section">
        <div style="border: 1px solid #ddd; display: inline-block; padding: 8px;">
            {!! $qrCode !!}
        </div>
        <p>Código QR de verificación</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }} | ID: {{ $persona->id }}</p>
    </div>

    <div class="footer-line">
        UNITEPC - Universidad Técnica Privada Cosmos | Sistema de Gestión de Talento Humano (SIGETH) | Documento generado automáticamente
    </div>

    {{-- ═══════════════════════════════ --}}
    {{-- PÁGINAS DE RESPALDOS (ADJUNTOS) --}}
    {{-- ═══════════════════════════════ --}}
    @if(count($adjuntos) > 0)
        <div class="attachment-page">
            <div class="attachment-header">DOCUMENTOS DE RESPALDO Y ANEXOS</div>

            @foreach($adjuntos as $adj)
                @if($adj['type'] === 'image')
                    <div class="attachment-item">
                        <div class="attachment-label">{{ $adj['label'] }}</div>
                        <img src="{{ $adj['path'] }}" class="attachment-img" alt="{{ $adj['label'] }}">
                    </div>
                @else
                    <div class="attachment-item">
                        <div class="attachment-label">{{ $adj['label'] }}</div>
                        <p class="small" style="padding: 3px; border: 1px dashed #6A37A3; background: #FFF4FA; color: #4A0E78;">
                            ⚠️ El PDF adjunto contiene compresión avanzada no soportada por el motor de fusión. <br>
                            📄 Archivo original conservado en el sistema: <strong>{{ $adj['filename'] }}</strong>
                        </p>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

</body>
</html>
