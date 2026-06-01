<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; margin: 0; padding: 32px 16px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .header { background: #00346f; padding: 32px; color: #fff; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0; font-size: 14px; opacity: 0.85; }
        .body { padding: 32px; }
        .field { margin-bottom: 20px; }
        .field label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; margin-bottom: 4px; }
        .field value { display: block; font-size: 15px; color: #1e293b; white-space: pre-wrap; word-break: break-word; }
        .divider { height: 1px; background: #e2e8f0; margin: 24px 0; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; background: #00346f/10; background-color: #dbeafe; color: #1d4ed8; font-size: 12px; font-weight: 600; }
        .footer { background: #f8fafc; padding: 20px 32px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Nueva candidatura recibida</h1>
        <p>AGC Assessors — Trabaja con nosotros</p>
    </div>
    <div class="body">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
            <div class="field">
                <label>Nombre</label>
                <value>{{ $application->name }}</value>
            </div>
            <div class="field">
                <label>Apellidos</label>
                <value>{{ $application->last_name }}</value>
            </div>
        </div>

        <div class="field">
            <label>Email</label>
            <value><a href="mailto:{{ $application->email }}" style="color:#00346f;">{{ $application->email }}</a></value>
        </div>

        @if($application->phone)
        <div class="field">
            <label>Teléfono</label>
            <value>{{ $application->phone }}</value>
        </div>
        @endif

        <div class="field">
            <label>Área de interés</label>
            <value><span class="badge">{{ $application->department }}</span></value>
        </div>

        <div class="divider"></div>

        <div class="field">
            <label>Mensaje / Motivación</label>
            <value>{{ $application->message }}</value>
        </div>

        @if($application->cv_path)
        <div class="field">
            <label>CV adjunto</label>
            <value>✓ Se ha adjuntado el archivo CV a este correo.</value>
        </div>
        @endif

        <div class="divider"></div>

        <div class="field">
            <label>Fecha de envío</label>
            <value>{{ $application->created_at?->format('d/m/Y H:i') }}</value>
        </div>

        <div class="field">
            <label>IP del solicitante</label>
            <value>{{ $application->ip_address ?? '—' }}</value>
        </div>

    </div>
    <div class="footer">
        Este correo fue generado automáticamente por el formulario de candidaturas de AGC Assessors.
    </div>
</div>
</body>
</html>
