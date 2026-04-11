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
    <title>Contrato de Trabajo</title>
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
        .document-start {
            margin-top: 3cm;
        }
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
        .bold {
            font-weight: bold;
        }
        .underline {
            text-decoration: underline;
        }
        .bold-underline {
            font-weight: bold;
            text-decoration: underline;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="document-start">
        <p class="contract-title">CONTRATO DE  TRABAJO</p>

        <p>Conste por el presente documento un <span class="bold">CONTRATO DE  TRABAJO A PLAZO FIJO,</span> descrito al tenor de las cláusulas y condiciones que se detallan a continuación:</p>

        <p><span class="bold">CLÁUSULA PRIMERA.- (PARTES INTERVINIENTES).-</span> Intervienen en la suscripción de éste documento:</p>

        <p><span class="bold">1.1.-</span> La ASOCIACIÓN CIVIL UNIVERSIDAD TÉCNICA PRIVADA COSMOS – UNITEPC<span class="bold">, institución dedicada a la educación superior, con personería jurídica debidamente reconocida por el Estado Boliviano, con domicilio principal en {{ $empresa['domicilio_legal'] ?? 'la Av. Blanco Galindo Km 7 ½, Zona Florida Norte de la ciudad de Cochabamba' }}, representada legalmente por el </span>LIC. EDUARDO ENRIQUE MANCILLA HEREDIA<span class="bold">, mayor de edad, hábil por derecho, con C.I. No. 3593611 con Q.R. en su calidad de </span>REPRESENTANTE LEGAL, <span class="bold">conforme consta en Testimonio Poder No. 198/2024 de fecha 23 de Abril de 2024 otorgado ante Notaría de Fe Pública No. 14 del Distrito Judicial de Cochabamba, a cargo de Jorge A. Loayza Molina, quien para fines de la suscripción de este documento actúa mediante {{ $empresa['apoderado_tratamiento'] ?? 'la' }} {{ $empresa['apoderado_actual'] ?? 'Dra. Isabella Vargas Guzmán' }}, mayor de edad, hábil por derecho, titular de la Cédula de Identidad No. {{ $empresa['apoderado_ci'] ?? '10825189' }} expedida en {{ $empresa['apoderado_expedido'] ?? 'el Beni' }}, tal cual consta en Testimonio Poder No. {{ $empresa['apoderado_poder_nro'] ?? '299/2.024' }} de fecha {{ $empresa['apoderado_poder_fecha'] ?? '11 Junio del 2024' }} otorgado ante Notaría de Fe Pública No. {{ $empresa['apoderado_notaria_nro'] ?? '14' }} del Distrito Judicial de {{ $empresa['apoderado_notaria_distrito'] ?? 'Cochabamba' }}, a cargo de {{ $empresa['apoderado_notaria_cargo'] ?? 'Jorge A. Loayza Molina' }}, institución que para efectos del presente contrato se denominará la </span>UNITEPC.</p>

        <p><span class="bold">1.2.- {{ $trabajador['nombre_completo'] ?? 'NOMBRE COMPLETO DEL TRABAJADOR' }}</span>, de {{ $trabajador['edad'] ?? '__' }} años, hábil por derecho, con C.I.  No. {{ $trabajador['ci'] ?? '________' }} expedida en {{ $trabajador['expedido'] ?? '________' }}, estado civil {{ $trabajador['estado_civil'] ?? '________' }}, de nacionalidad {{ $trabajador['nacionalidad'] ?? 'boliviana' }}, con domicilio en {{ $trabajador['domicilio'] ?? '________' }}{{ !empty($trabajador['departamento_domicilio']) ? ' del departamento de ' . $trabajador['departamento_domicilio'] : '' }}, quien para efectos del presente contrato se denominará {{ $gramatica['articulo'] }} <span class="bold">{{ $gramatica['denominacion'] }}.</span></p>

        <p>Concurren a este acto en pleno uso de todas sus facultades legales e intelectuales, y sin que medie ninguno de los vicios del consentimiento como son: el error, el dolo o la violencia.</p>

        <p><span class="bold">CLÁUSULA SEGUNDA.- (OBJETO).-</span> El objeto del presente contrato es la suscripción de un contrato a plazo fijo de conformidad a lo dispuesto por la Ley General del Trabajo, el Decreto Supremo Reglamentario Nº 224 del 23 de agosto de 1943, Decreto Ley Nº 16187 de 16 de febrero de 1979, así como también la Resolución Administrativa No. 650/2007 de fecha de 27 de Abril de 2017 del Viceministerio de Trabajo, Desarrollo Laboral y Cooperativas Artículo primero, inciso 2 apartado c). Por lo cual para la visación del contrato respectivo conforme dicha Resolución bastará la presentación de la Resolución del Honorable Consejo Universitario del inicio de la Gestión Académica I y II {{ $contrato['gestion_academica'] ?? '2026' }}, conforme calendario académico aprobado. El cargo que desempeñará {{ $gramatica['el_trabajador'] }} en la <span class="bold">UNIVERSIDAD</span> es el de <span class="bold">{{ $contrato['cargo'] ?? '________' }}</span> bajo la dependencia {{ $contrato['dependencia'] ?? '________' }}.</p>

        <p><span class="bold">CLÁUSULA TERCERA.- (VIGENCIA DEL CONTRATO).-</span> El presente contrato se pacta por un plazo de {{ $contrato['duracion_literal'] ?? '________' }}, computable a partir del {{ $contrato['fecha_inicio_literal'] ?? '________' }} hasta el {{ $contrato['fecha_fin_literal'] ?? '________' }} Sin embargo, podrá concluir antes del plazo en caso de que la <span class="bold">UNIVERSIDAD</span> constate unilateralmente que {{ $gramatica['el_trabajador'] }} incurra en alguna de las causales del Art. 16 de la Ley General del Trabajo y Art. 9 de  su Decreto Reglamentario y disposiciones legales conexas.</p>

        <p><span class="bold">CLÁUSULA CUARTA.- (NATURALEZA DEL CONTRATO).-</span> El presente contrato es de naturaleza enteramente laboral en {{ $contrato['lugar_trabajo'] ?? '________' }}, razón por la cual se regirá conforme a las normas laborales vigentes.</p>

        <p><span class="bold">CLÁUSULA QUINTA.-  (JORNADA LABORAL Y HORARIO DE TRABAJO).-</span> {{ ucfirst($gramatica['el_trabajador']) }} desarrollara sus actividades bajo jornada tiempo completo, por lo cual éste se encuentra obligado a cumplir una carga horaria de {{ $contrato['carga_horaria_literal'] ?? '________' }} horas semanales, para tal efecto se regirá al siguiente horario:</p>

        @foreach (($contrato['horarios'] ?? []) as $index => $horario)
            <p><span class="bold">5.{{ $index + 1 }}.-</span> {{ $horario }}</p>
        @endforeach

        @if (!empty($contrato['nota_horaria']))
            <p>{{ $contrato['nota_horaria'] }}</p>
        @endif

        <p>Los horarios establecidos serán controlados mediante el control biométrico dela <span class="bold">UNIVERSIDAD</span>, siendo obligación del <span class="bold">{{ $gramatica['denominacion'] }}</span> su uso al ingreso y salida de su jornada laboral.</p>
        <p>{{ ucfirst($gramatica['el_trabajador']) }} se compromete formalmente, mediante el presente instrumento a estar a plena disposición de la <span class="bold">UNITEPC</span> dentro de su horario de trabajo, durante la vigencia de éste contrato y según las necesidades de la <span class="bold">UNITEPC</span>, las cuales le serán informadas conforme a Derecho.</p>

        <p><span class="bold">CLÁUSULA SEXTA.- (REMUNERACIÓN).-</span> La <span class="bold">UNIVERSIDAD</span> pagará a favor de {{ $gramatica['articulo'] }} <span class="bold">{{ $gramatica['denominacion'] }}</span> la suma de <span class="bold">{{ $contrato['salario_numeral_formateado'] ?? '________' }} {{ $contrato['salario_literal_parentesis'] ?? '' }}</span>.{!! !empty($contrato['remuneracion_detalle']) ? ' ' . $contrato['remuneracion_detalle'] : ' Que será cancelado mes cumplido conforme el Art. 52 y 53 de la Ley General del Trabajo y D.S. 28699 Art. 6to.' !!}@if(!empty($contrato['bono_frontera_texto'])) {{ $contrato['bono_frontera_texto'] }}@endif @if(!empty($contrato['total_ganado_texto'])) {{ $contrato['total_ganado_texto'] }}@endif</p>

        @if(!empty($contrato['referida_suma_texto']))
            <p>{{ $contrato['referida_suma_texto'] }}</p>
        @else
            <p>La referida suma de dinero será pagada en la moneda señalada y hasta el día quince (15) de cada mes vencido, mediante depósito bancario, en una cuenta bancaria y {{ $gramatica['titular_cuenta'] }}.</p>
        @endif

        <p>El salario establecido, reconoce todos y cada uno de los derechos reconocidos por las normas laborales vigentes relacionadas a la naturaleza del presente contrato; así mismo se aclara, que del salario antes mencionado se deberán efectuar las retenciones correspondientes a la Seguridad Social y a los impuestos de ley.</p>
        <p>No se considera como trabajos ni horas extraordinarias, aquellas actividades que {{ $gramatica['el_trabajador'] }} realice para subsanar faltas, atrasos o errores imputables a su persona, ni aquellas actividades extracurriculares a las que {{ $gramatica['el_trabajador'] }} deba asistir por consecuencia del cargo que desempeña, pudiendo tratarse de actividades, educativas y/o de recreación que realice la <span class="bold">UNIVERSIDAD.</span></p>

        <p><span class="bold">CLÁUSULA SÉPTIMA.-  (DERECHOS DEL TRABAJADOR).-</span>  {{ ucfirst($gramatica['el_trabajador']) }}, con carácter enunciativo y no limitativo, tiene los siguientes derechos:</p>
        <p><span class="bold">7.1.-</span>  A percibir su salario con regularidad, conforme a ley.</p>
        <p><span class="bold">7.2.-</span> A ser {{ $gramatica['incorporado'] }} al régimen de Seguridad Social, según establece el Código de Seguridad Social y disposiciones conexas.</p>
        <p><span class="bold">7.3.-</span> A recibir trato respetuoso por parte de sus superiores y de todo trabajador dependiente de la <span class="bold">UNIVERSIDAD</span>.</p>
        <p><span class="bold">7.4.-</span>  Al ejercicio pleno de sus derechos constitucionales, laborales y sociales.</p>

        <p><span class="bold">CLÁUSULA OCTAVA.- (OBLIGACIONES DE LAS PARTES).-</span>  Las partes se comprometen y obligan a:</p>
        <p><span class="bold">8.1.- UNITEPC.-</span></p>
        <p><span class="bold">8.1.1.-</span> Pagar el salario {{ $gramatica['de_trabajador'] }} de manera puntual, así como cualquier otro pago que por derecho le correspondiera.</p>
        <p><span class="bold">8.1.2.-</span> Asumir las obligaciones patronales ante las AFP´S y Cajas de Salud que correspondan.</p>
        <p><span class="bold">8.1.3.-</span> Cuando corresponda dotar del material de trabajo a favor {{ $gramatica['de_trabajador'] }}.</p>
        <p><span class="bold">8.1.4.-</span> Cumplir con todas las obligaciones patronales conforme la Ley General del Trabajo, Decreto Reglamentario y demás normativa pertinente.</p>

        <p><span class="bold">8.2.- {{ $gramatica['denominacion'] }}.-</span></p>
        <p><span class="bold">8.2.1.-</span> {{ ucfirst($gramatica['el_trabajador']) }}, se compromete expresamente a no vulnerar lo establecido en el Art. 16 de la Ley General del Trabajo y 9 de su Reglamento, en estricta aplicación al alcance de trabajo que la <span class="bold">UNITEPC</span> le asigne.</p>
        <p><span class="bold">8.2.2.-</span> Conocer y acatar las instrucciones internas impartidas por la <span class="bold">UNITEPC</span>.</p>
        <p><span class="bold">8.2.3.-</span>  Ejecutar las labores que se le encomienden, siempre que sean compatibles con sus aptitudes, estado y condición, con la mayor eficiencia, disciplina, responsabilidad, cuidado, dedicación y esmero, en la forma, tiempo y lugar convenidos, concentrando la atención en la labor que está realizando, a fin de que la misma resulte de la mejor calidad posible.</p>
        <p><span class="bold">8.2.4.-</span> Cumplir con las disposiciones legales vigentes referentes a Higiene, Seguridad y Salud Ocupacional.</p>
        <p><span class="bold">8.2.5.-</span> Conservar buena conducta moral, y actuar dentro del marco del respeto con sus superiores, compañeros de trabajo, plantel administrativo, plantel docente, estudiantes y demás personas que forman parte de la <span class="bold">UNIVERSIDAD.</span></p>
        <p><span class="bold">8.2.6.-</span> Acatar la prohibición de venta de productos de cualquier tipo a los estudiantes, docentes o trabajadores en general de la <span class="bold">UNIVERSIDAD.</span></p>
        <p><span class="bold">8.2.7.-</span> Contar con los correspondientes Títulos Profesionales, de acuerdo a la normativa legal vigente en Bolivia.</p>
        <p><span class="bold">8.2.8.-</span> Cumplir puntualmente los horarios y calendarios previamente establecidos.</p>
        <p><span class="bold">8.2.9.-</span> Abstenerse de divulgar o atribuirse autoridad sobre ciertas políticas y decisiones netamente de jerárquicas superiores de la <span class="bold">UNIVERSIDAD.</span></p>
        <p><span class="bold">8.2.10.-</span> Apoyar a la institución en el proceso de la implementación de políticas y decisiones administrativas que beneficien la resolución de problemas.</p>
        <p><span class="bold">8.2.12.-</span> Cumplir con la jornada laboral asignada.</p>
        <p><span class="bold">8.2.13.-</span> Asistir a talleres de desarrollo profesional cuando se requiera su presencia.</p>
        <p><span class="bold">8.2.14.-</span> Mantener el custodio y responsabilidad sobre libros, materiales, muebles y equipos asignados.</p>
        <p><span class="bold">8.2.15.-</span>  Asistir a las reuniones Administrativas  y demás  reuniones programadas.</p>
        <p><span class="bold">8.2.16.-</span>  Presentar periódicamente a las autoridades de su dependencia un informe de sus actividades.</p>
        <p><span class="bold">8.2.17.-</span> Responsabilizarse por toda la información y documentación que le sea conferida con la finalidad de cumplir con las labores encomendadas a su cargo, debiendo devolver de forma inmediata, toda aquella documentación a la que pudiera tener acceso en el desarrollo de sus actividades, una vez el mismo sea notificado por escrito con el requerimiento de devolución de documentación. Se aclara que {{ $gramatica['el_trabajador'] }} no podrá guardar ninguna copia de la documentación que le sea confiada.</p>
        <p><span class="bold">8.2.18.-</span> Cumplir estrictamente las órdenes, instrucciones que le impartan sus inmediatos superiores, incluyendo las correcciones que correspondan, con responsabilidad.</p>

        <p><span class="bold">CLÁUSULA NOVENA.- (FUNCIONES ESPECÍFICAS DEL {{ $gramatica['denominacion'] }}).-</span> {{ ucfirst($gramatica['el_trabajador']) }} se compromete en función al cargo que ocupa a cumplir con las siguientes obligaciones específicas:</p>
        @foreach ($funciones as $index => $funcion)
            <p><span class="bold">9.{{ $index + 1 }}.-</span> {{ $funcion }}</p>
        @endforeach

        <p><span class="bold">CLÁUSULA DÉCIMA.- (RESPONSABILIDAD).-</span> El <span class="bold">{{ $gramatica['denominacion'] }}</span>, será responsable de toda la información y documentación que le sean  conferidas a su persona con la finalidad de cumplir con las labores encomendadas a su cargo. De igual manera, el <span class="bold">{{ $gramatica['denominacion'] }}</span>, se obliga a mantener en buen estado de conservación, todos los ambientes de la <span class="bold">UNIVERSIDAD</span>, en los que desempeña sus funciones en los horarios señalados para tal efecto, así como también los materiales que le sean entregados y puestos a su cargo.  En caso de que exista daño culposo o doloso por parte {{ $gramatica['de_trabajador'] }} y sea éste comprobado, {{ $gramatica['el_trabajador'] }} será {{ $gramatica['retirado'] }} de sus funciones sin goce de desahucio e indemnización, tal como lo dispone el Art. 16 de L.G.T. y el Art. 9 de su Decreto Reglamentario, sin perjuicio de iniciar la acción civil o penal que corresponda.</p>

        <p><span class="bold">CLÁUSULA DÉCIMO PRIMERA.- (CONFIDENCIALIDAD).-</span> Todas las actividades relacionadas con la ejecución del presente acuerdo serán tratadas confidencialmente por las partes, por lo cual el <span class="bold">{{ $gramatica['denominacion'] }}</span>, se compromete a no revelar, ni suministrar copia de todo o en parte de cualquiera de la información que se le proporcione o a la que se tuviere acceso sin el previo consentimiento expreso y por escrito de la <span class="bold">UNITEPC.</span></p>
        <p>Se aclara que la información confidencial objeto de este contrato representa toda la información no pública de propiedad de la <span class="bold">UNITEPC</span>, incluyendo reportes y análisis, datos técnicos y económicos, estudios, proyecciones, secretos estratégicos institucionales, "know - how", estrategia de investigaciones, información contractual o financiera o cualquier otra información escrita  y oral relativa a la <span class="bold">UNITEPC</span> y a la que el <span class="bold">{{ $gramatica['denominacion'] }}</span> tuviera acceso durante el periodo de vigencia del presente contrato, la cual será considerada confidencial, excepto si expresamente se estipula lo contrario. La información confidencial puede encontrarse en cualquier formato, como ser textos escritos, documentos digitales y datos contenidos en otros medios.  En caso de infidencia probada, será despedido con la pérdida de su desahucio e indemnización tal lo cual lo establece el Art. 16 inc. b) de la Ley General del Trabajo.</p>

        <p><span class="bold">CLÁUSULA DÉCIMO SEGUNDA.- (TRANSFERENCIAS Y COMISIONES).-</span> La <span class="bold">UNITEPC</span>, podrá transferir {{ $gramatica['al_trabajador'] }} de una sección a otra, manteniendo su remuneración y derechos laborales, previa conformidad de éste y con el aviso correspondiente conforme a derecho. De igual manera el <span class="bold">{{ $gramatica['denominacion'] }}</span>, podrá ser enviado en comisión, a cualquier sección, distrito o ciudad del país, previo su consentimiento, para cumplir funciones sean estas de representación, de gestión u otras, en este supuesto la <span class="bold">UNITEPC</span> cubrirá los costos de transporte y viáticos, de acuerdo con la normativa vigente.</p>

        <p><span class="bold">CLÁUSULA DÉCIMO TERCERA.- (DOCUMENTOS).-</span> Forman parte indisoluble del presente contrato de trabajo, los documentos personales del <span class="bold">{{ $gramatica['denominacion'] }}</span>, tales como: el currículo vitae y documento de identidad.</p>

        <p><span class="bold">CLÁUSULA DÉCIMO CUARTA.- (INSCRIPCIÓN DE HEREDEROS).-</span> De conformidad al Art. 7 inciso <span class="bold">h)</span> del Decreto Reglamentario de la Ley General del Trabajo, el <span class="bold">{{ $gramatica['denominacion'] }}</span> pone en conocimiento de la <span class="bold">UNITEPC</span> el nombre y edades de sus herederos para los efectos de las disposiciones concernientes a la reparación de los riegos profesionales:</p>
        @forelse ($herederos as $index => $heredero)
            <p><span class="bold">14.{{ $index + 1 }}.- {{ $heredero['parentesco'] ?? 'Beneficiario' }}:</span> {{ $heredero['nombre'] ?? '________' }}, <span class="bold">Edad</span>: {{ $heredero['edad'] ?? '__' }} años, <span class="bold">C.I.</span> {{ trim(($heredero['ci'] ?? '') . ' ' . ($heredero['expedido'] ?? '')) }}.</p>
        @empty
            <p><span class="bold">14.1.-</span> Sin herederos registrados.</p>
        @endforelse
        <p>En caso de que por causales establecidas por Ley los herederos directos del <span class="bold">{{ $gramatica['denominacion'] }}</span> cambiaren, éste se compromete a dar aviso oportuno a la <span class="bold">UNITEPC</span> para los fines del artículo antes citado.</p>

        <p><span class="bold">CLÁUSULA DÉCIMO QUINTA.- (CAUSALES DE RESOLUCIÓN).-</span> Podrá resolverse el presente contrato por las siguientes causales:</p>
        <p><span class="bold">15.1.-</span> Por incurrir en cualquiera de las causales establecidas en el Art. 16 de la Ley General del Trabajo, concordante con el Art. 9 del Decreto Reglamentario de la Ley General del Trabajo.</p>

        <p><span class="bold">CLÁUSULA DÉCIMO SEXTA.- (PRINCIPIO DE BUENA FE CONTRACTUAL).-</span> Se establece el principio de "Buena Fe Contractual", como concepto jurídico fundamental que debe ser admitido como presupuesto inexcusable e ineludible por las partes en el desarrollo y cumplimiento del presente contrato.  El Principio de "Buena Fe Contractual" se traduce en el cumplimiento honesto, escrupuloso y firme de todas las obligaciones de carácter individual o colectivo, cimentado en la relación laboral la seguridad jurídica necesaria.</p>

        <p><span class="bold">CLÁUSULA DÉCIMO SÉPTIMA.- (DEL MATERIAL DE TRABAJO).-</span> La <span class="bold">UNITEPC</span> proveerá {{ $gramatica['al_trabajador'] }} de los implementos necesarios para el desempeño de sus funciones, quedando expresamente entendido que dichos materiales son propiedad de la <span class="bold">UNITEPC</span>, y por tanto no constituirán provecho, beneficio o ventaja alguna para el <span class="bold">{{ $gramatica['denominacion'] }}</span> ni se considerarán como parte integrante de su salario. Por su parte, el <span class="bold">{{ $gramatica['denominacion'] }}</span> se obliga a cuidar dichos implementos con la mayor diligencia posible, y a impedir su uso por parte de terceros. Asimismo, en caso de despido o retiro, el <span class="bold">{{ $gramatica['denominacion'] }}</span> devolverá a la <span class="bold">UNITEPC</span> todos los elementos, implementos, herramientas y equipos que se le hayan entregado para el desempeño de sus labores, en el mismo estado en que los recibe, salvo el normal desgaste por el uso y/o transcurso del tiempo.</p>

        <p><span class="bold">CLÁUSULA DÉCIMO OCTAVA.- (ACEPTACIÓN).-</span> En señal de conformidad con todas y cada una de las cláusulas y condiciones que se detallan precedentemente, las partes intervinientes, cuyas generales se detallan en la cláusula primera, suscriben el presente documento, comprometiéndose a su fiel y estricto cumplimiento en toda forma de derecho.</p>

        <p><span class="bold">{{ $contrato['ciudad_firma'] ?? '________' }}, {{ $contrato['fecha_firma_literal'] ?? '________' }}</span></p>

        <table class="signature-block">
            <tr>
                <td>
                    <br><br><br>
                    <span class="bold">UNITEPC</span><br>
                    {{ $empresa['representante_legal'] ?? 'Lic. Eduardo E. Mancilla Heredia' }} – Representante Legal<br>
                    Representado por:<br>
                    {{ $empresa['apoderado_actual'] ?? 'Isabella Vargas Guzmán' }}<br>
                    C.I. No. {{ $empresa['apoderado_ci'] ?? '10825189 Be' }}
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
