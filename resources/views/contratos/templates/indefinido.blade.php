@php
    $trabajador = $trabajador ?? [];
    $contrato = $contrato ?? [];
    $empresa = $empresa ?? [];
    $gramatica = $gramatica ?? \Src\TalentoHumano\Domain\Support\ContratoGrammar::fromSexo($trabajador['sexo'] ?? null);
    $funciones = $funciones ?? [];
    $herederos = $herederos ?? [];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Trabajo Indefinido</title>
    <style>
        @page {
            margin: 2.3cm 2.2cm 2cm 2.2cm;
            size: letter;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.15;
            color: #000;
            margin: 0;
        }
        .document-start { margin-top: 3cm; }
        .contract-title {
            text-align: center;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 14px 0;
            text-transform: uppercase;
        }
        p {
            margin: 0 0 6px 0;
            text-align: justify;
        }
        .signature-block {
            width: 100%;
            margin-top: 60px;
            border-collapse: collapse;
        }
        .signature-block td {
            width: 50%;
            vertical-align: top;
            text-align: left;
            padding: 0 12px;
            line-height: 1.6;
        }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="document-start">
        <p class="contract-title">CONTRATO DE TRABAJO</p>

        <p>Conste por el presente documento un <span class="bold">CONTRATO DE TRABAJO INDEFINIDO,</span> descrito al tenor de las cláusulas y condiciones que se detallan a continuación:</p>

        <p><span class="bold">CLÁUSULA PRIMERA.- (PARTES INTERVINIENTES).-</span> Intervienen en la suscripción de éste documento:</p>
        <p><span class="bold">1.1.-</span> La <span class="bold">{{ $empresa['razon_social'] ?? 'ASOCIACIÓN CIVIL UNIVERSIDAD TÉCNICA PRIVADA COSMOS – UNITEPC' }}</span>, institución dedicada a la educación superior, con personería jurídica debidamente reconocida por el Estado Boliviano, con domicilio principal en {{ $empresa['domicilio_legal'] ?? 'la Av. Blanco Galindo Km 7 ½, Zona Florida Norte de la ciudad de Cochabamba' }}, representada legalmente por el <span class="bold">{{ $empresa['representante_legal'] ?? 'Lic. Eduardo E. Mancilla Heredia' }}</span>, quien para fines de la suscripción de este documento actúa mediante {{ $empresa['apoderado_tratamiento'] ?? 'el' }} <span class="bold">{{ $empresa['apoderado_actual'] ?? 'Lic. Brayan Cabeño Zambrana' }}</span>, con C.I. {{ $empresa['apoderado_ci'] ?? '8004816' }} {{ $empresa['apoderado_expedido'] ?? 'Cbba.' }}, institución que para efectos del presente contrato se denominará la <span class="bold">UNITEPC</span>.</p>
        <p><span class="bold">1.2.- {{ $trabajador['nombre_completo'] ?? 'NOMBRE COMPLETO DEL TRABAJADOR' }}</span>, de {{ $trabajador['edad'] ?? '__' }} años, hábil por derecho, con C.I. No. {{ $trabajador['ci'] ?? '________' }} expedida en {{ $trabajador['expedido'] ?? '________' }}, estado civil {{ $trabajador['estado_civil'] ?? '________' }}, de nacionalidad {{ $trabajador['nacionalidad'] ?? 'boliviana' }}, con domicilio en {{ $trabajador['domicilio'] ?? '________' }}, quien para efectos del presente contrato se denominará {{ $gramatica['articulo'] }} <span class="bold">{{ $gramatica['denominacion'] }}</span>.</p>

        <p><span class="bold">CLÁUSULA SEGUNDA.- (OBJETO).-</span> El objeto del presente contrato es la suscripción de un contrato de trabajo indefinido de conformidad a la Ley General del Trabajo y demás normas aplicables. El cargo que desempeñará {{ $gramatica['el_trabajador'] }} en la <span class="bold">UNIVERSIDAD</span> es el de <span class="bold">{{ $contrato['cargo'] ?? '________' }}</span> bajo la dependencia de {{ $contrato['dependencia'] ?? '________' }}.</p>

        <p><span class="bold">CLÁUSULA TERCERA.- (VIGENCIA DEL CONTRATO).-</span> El presente contrato se pacta por <span class="bold">tiempo indefinido</span>, computable a partir del {{ $contrato['fecha_inicio_literal'] ?? '________' }}. Sin embargo, podrá concluir por las causales establecidas en la Ley General del Trabajo y disposiciones conexas.</p>

        <p><span class="bold">CLÁUSULA CUARTA.- (NATURALEZA DEL CONTRATO).-</span> El presente contrato es de naturaleza enteramente laboral en {{ $contrato['lugar_trabajo'] ?? '________' }}, razón por la cual se regirá conforme a las normas laborales vigentes.</p>

        <p><span class="bold">CLÁUSULA QUINTA.- (JORNADA LABORAL Y HORARIO DE TRABAJO).-</span> {{ ucfirst($gramatica['el_trabajador']) }} desarrollará sus actividades bajo jornada tiempo completo, por lo cual se encuentra {{ $gramatica['obligado'] }} a cumplir una carga horaria de {{ $contrato['carga_horaria_literal'] ?? '________' }} horas semanales, para tal efecto se regirá al siguiente horario:</p>
        @foreach (($contrato['horarios'] ?? []) as $index => $horario)
            <p><span class="bold">5.{{ $index + 1 }}.-</span> {{ $horario }}</p>
        @endforeach
        @if (!empty($contrato['nota_horaria']))
            <p>{{ $contrato['nota_horaria'] }}</p>
        @endif

        <p><span class="bold">CLÁUSULA SEXTA.- (REMUNERACIÓN).-</span> La <span class="bold">UNIVERSIDAD</span> pagará a favor de {{ $gramatica['articulo'] }} <span class="bold">{{ $gramatica['denominacion'] }}</span> la suma de <span class="bold">{{ $contrato['salario_numeral_formateado'] ?? '________' }} {{ $contrato['salario_literal_parentesis'] ?? '' }}</span>. {{ $contrato['remuneracion_detalle'] ?? '' }}</p>
        <p>{{ $contrato['referida_suma_texto'] ?? '' }}</p>

        <p><span class="bold">CLÁUSULA SÉPTIMA.- (DERECHOS DEL TRABAJADOR).-</span> {{ ucfirst($gramatica['el_trabajador']) }} tiene los derechos reconocidos por la normativa laboral vigente y por las políticas institucionales de la UNITEPC.</p>

        <p><span class="bold">CLÁUSULA OCTAVA.- (OBLIGACIONES DE LAS PARTES).-</span> Las partes se comprometen al cumplimiento estricto de las obligaciones legales, reglamentarias e institucionales inherentes a la relación laboral.</p>

        <p><span class="bold">CLÁUSULA NOVENA.- (FUNCIONES ESPECÍFICAS DEL {{ $gramatica['denominacion'] }}).-</span> {{ ucfirst($gramatica['el_trabajador']) }} se compromete en función al cargo que ocupa a cumplir con las siguientes obligaciones específicas:</p>
        @foreach ($funciones as $index => $funcion)
            <p><span class="bold">9.{{ $index + 1 }}.-</span> {{ $funcion }}</p>
        @endforeach

        <p><span class="bold">CLÁUSULA DÉCIMA.- (RESPONSABILIDAD).-</span> {{ ucfirst($gramatica['el_trabajador']) }} será responsable de la información, documentación y bienes que le sean conferidos para el desempeño de sus funciones.</p>
        <p><span class="bold">CLÁUSULA DÉCIMO PRIMERA.- (CONFIDENCIALIDAD).-</span> Toda la información a la que tenga acceso {{ $gramatica['el_trabajador'] }} durante la vigencia del presente contrato será considerada confidencial.</p>
        <p><span class="bold">CLÁUSULA DÉCIMO SEGUNDA.- (TRANSFERENCIAS Y COMISIONES).-</span> La UNITEPC podrá transferir {{ $gramatica['al_trabajador'] }} o encomendarle comisiones conforme a normativa vigente y necesidad institucional.</p>
        <p><span class="bold">CLÁUSULA DÉCIMO TERCERA.- (DOCUMENTOS).-</span> Forman parte indisoluble del presente contrato de trabajo los documentos personales del {{ $gramatica['denominacion'] }}, tales como el currículo vitae y documento de identidad.</p>

        <p><span class="bold">CLÁUSULA DÉCIMO CUARTA.- (INSCRIPCIÓN DE HEREDEROS).-</span> De conformidad al Art. 7 inciso h) del Decreto Reglamentario de la Ley General del Trabajo, {{ $gramatica['el_trabajador'] }} pone en conocimiento de la UNITEPC el nombre y edades de sus herederos:</p>
        @forelse ($herederos as $index => $heredero)
            <p><span class="bold">14.{{ $index + 1 }}.- {{ $heredero['parentesco'] ?? 'Beneficiario' }}:</span> {{ $heredero['nombre'] ?? '________' }}, Edad: {{ $heredero['edad'] ?? '__' }} años, C.I. {{ trim(($heredero['ci'] ?? '') . ' ' . ($heredero['expedido'] ?? '')) }}.</p>
        @empty
            <p><span class="bold">14.1.-</span> Sin herederos registrados.</p>
        @endforelse

        <p><span class="bold">CLÁUSULA DÉCIMO QUINTA.- (CAUSALES DE RESOLUCIÓN).-</span> Podrá resolverse el presente contrato por las causales previstas en la Ley General del Trabajo y demás normativa aplicable.</p>
        <p><span class="bold">CLÁUSULA DÉCIMO SEXTA.- (BUENA FE CONTRACTUAL).-</span> Las partes actuarán bajo el principio de buena fe contractual durante la ejecución del presente documento.</p>
        <p><span class="bold">CLÁUSULA DÉCIMO SÉPTIMA.- (MATERIAL DE TRABAJO).-</span> La UNITEPC proveerá a {{ $gramatica['el_trabajador'] }} los implementos necesarios para el desempeño de sus funciones.</p>
        <p><span class="bold">CLÁUSULA DÉCIMO OCTAVA.- (ACEPTACIÓN).-</span> En señal de conformidad con todas las cláusulas precedentes, las partes suscriben el presente documento.</p>

        <p><span class="bold">{{ $contrato['ciudad_firma'] ?? '________' }}, {{ $contrato['fecha_firma_literal'] ?? '________' }}</span></p>

        <table class="signature-block">
            <tr>
                <td>
                    <br><br><br>
                    <span class="bold">UNITEPC</span><br>
                    {{ $empresa['representante_legal'] ?? 'Lic. Eduardo E. Mancilla Heredia' }} – Representante Legal<br>
                    Representado por:<br>
                    {{ $empresa['apoderado_actual'] ?? 'Lic. Brayan Cabeño Zambrana' }}<br>
                    C.I. {{ trim(($empresa['apoderado_ci'] ?? '') . ' ' . ($empresa['apoderado_expedido'] ?? '')) }}
                </td>
                <td>
                    <br><br><br>
                    <span class="bold">{{ $gramatica['denominacion'] }}</span><br>
                    Nombre:<br>
                    C.I:
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
