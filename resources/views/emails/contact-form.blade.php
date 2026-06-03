<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta web — {{ $data['name'] }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            padding: 40px 16px;
            color: #1e293b;
        }
        .wrapper { max-width: 580px; margin: 0 auto; }

        /* Header */
        .header {
            background: #00346f;
            border-radius: 16px 16px 0 0;
            padding: 28px 36px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .header-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .header-icon svg { width: 22px; height: 22px; fill: #ffffff; }
        .header-text h1 { color: #ffffff; font-size: 17px; font-weight: 700; line-height: 1.3; }
        .header-text p  { color: rgba(255,255,255,0.65); font-size: 13px; margin-top: 2px; }

        /* Sender pill */
        .sender-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: none;
            padding: 16px 36px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #00346f, #00B4D8);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 16px; font-weight: 700;
            flex-shrink: 0;
        }
        .sender-info strong { display: block; font-size: 14px; color: #1e293b; font-weight: 600; }
        .sender-info a { font-size: 13px; color: #00346f; text-decoration: none; }

        /* Body */
        .body {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: none;
            padding: 32px 36px;
        }

        /* Fields */
        .fields-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .field {
            padding: 14px 18px;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
        }
        .field:nth-child(2n) { border-right: none; }
        .field:nth-last-child(-n+2) { border-bottom: none; }
        .field.full {
            grid-column: 1 / -1;
            border-right: none;
        }
        .field-label {
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: #94a3b8; margin-bottom: 4px;
        }
        .field-value { font-size: 14px; color: #1e293b; line-height: 1.5; }
        .field-value a { color: #00346f; text-decoration: none; }

        /* Message */
        .message-section { margin-top: 4px; }
        .message-label {
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: #94a3b8; margin-bottom: 10px;
        }
        .message-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #00346f;
            border-radius: 0 10px 10px 0;
            padding: 18px 20px;
            font-size: 15px;
            color: #334155;
            line-height: 1.75;
            white-space: pre-wrap;
        }

        /* CTA */
        .cta { text-align: center; margin-top: 28px; }
        .reply-btn {
            display: inline-block;
            padding: 13px 32px;
            background: #00346f;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .reply-hint {
            font-size: 12px; color: #94a3b8; margin-top: 8px;
        }

        /* Footer */
        .footer {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 16px 16px;
            padding: 18px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .footer-left { font-size: 12px; color: #94a3b8; }
        .footer-left strong { color: #64748b; }
        .footer-right { font-size: 12px; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <div class="header-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/>
            </svg>
        </div>
        <div class="header-text">
            <h1>Nueva consulta desde el formulario web</h1>
            <p>{{ now()->format('d \d\e F \d\e Y, H:i') }}</p>
        </div>
    </div>

    {{-- Sender pill --}}
    <div class="sender-pill">
        <div class="avatar">{{ strtoupper(substr($data['name'], 0, 1)) }}</div>
        <div class="sender-info">
            <strong>{{ $data['name'] }}</strong>
            <a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a>
        </div>
    </div>

    {{-- Body --}}
    <div class="body">

        {{-- Metadata grid --}}
        <div class="fields-grid">
            <div class="field">
                <div class="field-label">Asunto</div>
                <div class="field-value">{{ $data['subject'] }}</div>
            </div>
            @if(!empty($data['phone']))
            <div class="field">
                <div class="field-label">Teléfono</div>
                <div class="field-value">
                    <a href="tel:{{ $data['phone'] }}">{{ $data['phone'] }}</a>
                </div>
            </div>
            @else
            <div class="field">
                <div class="field-label">Teléfono</div>
                <div class="field-value" style="color:#94a3b8">No facilitado</div>
            </div>
            @endif
            <div class="field full">
                <div class="field-label">Email de contacto</div>
                <div class="field-value">
                    <a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a>
                </div>
            </div>
        </div>

        {{-- Message --}}
        <div class="message-section">
            <div class="message-label">Mensaje</div>
            <div class="message-box">{{ $data['message'] }}</div>
        </div>

        {{-- CTA --}}
        <div class="cta">
            <a href="mailto:{{ $data['email'] }}?subject=Re: {{ rawurlencode($data['subject']) }}" class="reply-btn">
                Responder a {{ $data['name'] }}
            </a>
            <p class="reply-hint">Al responder, el correo irá directamente a {{ $data['email'] }}</p>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">
            Recibido desde <strong>agcassessors.com</strong><br>formulario de contacto
        </div>
        <div class="footer-right">
            {{ now()->format('d/m/Y') }}<br>{{ now()->format('H:i') }} h
        </div>
    </div>

</div>
</body>
</html>
