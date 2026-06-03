<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo mensaje de contacto</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f3fa; margin: 0; padding: 32px 16px; }
        .wrapper { max-width: 560px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .header { background: #00346f; padding: 32px; text-align: center; }
        .header img { height: 36px; }
        .header h1 { color: #ffffff; font-size: 18px; margin: 12px 0 0; font-weight: 600; letter-spacing: -0.01em; }
        .body { padding: 32px; }
        .field { margin-bottom: 20px; }
        .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 4px; }
        .value { font-size: 15px; color: #1e293b; line-height: 1.6; }
        .message-box { background: #f9f9ff; border-left: 3px solid #00346f; border-radius: 0 8px 8px 0; padding: 16px 20px; margin-top: 4px; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 24px 0; }
        .footer { text-align: center; padding: 20px 32px; background: #f9f9ff; border-top: 1px solid #e2e8f0; }
        .footer p { font-size: 12px; color: #94a3b8; margin: 0; }
        .reply-btn { display: inline-block; margin-top: 24px; padding: 12px 28px; background: #00346f; color: #ffffff; text-decoration: none; border-radius: 50px; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>Nuevo mensaje de contacto</h1>
            </div>
            <div class="body">
                <div class="field">
                    <div class="label">Nombre</div>
                    <div class="value">{{ $data['name'] }}</div>
                </div>
                <div class="field">
                    <div class="label">Email</div>
                    <div class="value">{{ $data['email'] }}</div>
                </div>
                @if(!empty($data['phone']))
                <div class="field">
                    <div class="label">Teléfono</div>
                    <div class="value">{{ $data['phone'] }}</div>
                </div>
                @endif
                <div class="field">
                    <div class="label">Asunto</div>
                    <div class="value">{{ $data['subject'] }}</div>
                </div>
                <hr class="divider">
                <div class="field">
                    <div class="label">Mensaje</div>
                    <div class="message-box value">{{ $data['message'] }}</div>
                </div>
                <div style="text-align:center">
                    <a href="mailto:{{ $data['email'] }}" class="reply-btn">Responder a {{ $data['name'] }}</a>
                </div>
            </div>
            <div class="footer">
                <p>Mensaje recibido desde el formulario de contacto de agcassessors.com</p>
                <p style="margin-top:4px">{{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
