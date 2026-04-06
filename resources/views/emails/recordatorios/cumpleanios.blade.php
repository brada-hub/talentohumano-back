<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $payload['asunto'] ?? 'Felicitacion institucional' }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f8fc;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:680px;margin:0 auto;padding:28px 16px;">
        <div style="background:linear-gradient(135deg,#6A37A3,#00A99D);border-radius:18px 18px 0 0;padding:28px 32px;color:#fff;text-align:center;">
            <div style="font-size:14px;letter-spacing:2px;font-weight:700;text-transform:uppercase;">UNITEPC</div>
            <div style="margin-top:8px;font-size:28px;font-weight:800;">Feliz Cumpleanios</div>
            <div style="margin-top:6px;font-size:14px;opacity:.92;">Sistema de Gestion de Talento Humano</div>
        </div>

        <div style="background:#ffffff;border-radius:0 0 18px 18px;padding:32px;border:1px solid #e5e7eb;border-top:none;">
            <p style="margin:0 0 18px;font-size:16px;">Estimado(a) <strong>{{ $payload['nombre_completo'] ?? 'funcionario(a)' }}</strong>:</p>

            <p style="margin:0 0 16px;font-size:15px;line-height:1.7;">
                La Universidad Tecnica Privada Cosmos <strong>UNITEPC</strong> le hace llegar una cordial felicitacion institucional
                por su cumpleanios, deseandole bienestar, salud y muchos exitos en su vida personal y profesional.
            </p>

            <div style="margin:24px 0;padding:18px 20px;background:#f8f5fc;border-left:4px solid #6A37A3;border-radius:10px;">
                <div style="font-size:14px;color:#6A37A3;font-weight:700;text-transform:uppercase;">Datos del recordatorio</div>
                <div style="margin-top:10px;font-size:14px;line-height:1.8;">
                    <div><strong>Fecha:</strong> {{ $payload['fecha_evento_legible'] ?? '---' }}</div>
                    @if(!empty($payload['cargo']))
                        <div><strong>Cargo:</strong> {{ $payload['cargo'] }}</div>
                    @endif
                    @if(!empty($payload['area']))
                        <div><strong>Area:</strong> {{ $payload['area'] }}</div>
                    @endif
                    @if(!empty($payload['sede']))
                        <div><strong>Sede:</strong> {{ $payload['sede'] }}</div>
                    @endif
                </div>
            </div>

            <p style="margin:0 0 10px;font-size:15px;line-height:1.7;">
                Reciba un cordial saludo de parte de Talento Humano y de toda la comunidad universitaria.
            </p>

            <p style="margin:24px 0 0;font-size:14px;color:#6b7280;">
                Este correo fue generado por SIGETH.
            </p>
        </div>
    </div>
</body>
</html>
