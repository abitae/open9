<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablece tu contraseña</title>
</head>
<body style="margin:0;padding:0;background:#0b1120;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:600px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border-radius:16px;overflow:hidden;">
            <div style="background:#0077ff;padding:28px 32px;">
                <h1 style="margin:0;color:#ffffff;font-size:20px;">Restablece tu contraseña</h1>
                <p style="margin:6px 0 0;color:#e0ecff;font-size:13px;">Hola, {{ $client->name }}.</p>
            </div>

            <div style="padding:24px 32px;">
                <p style="font-size:14px;line-height:1.6;color:#334155;">
                    Recibimos una solicitud para cambiar la contraseña de tu cuenta. El enlace caduca en 60 minutos.
                </p>

                <p style="margin:24px 0;text-align:center;">
                    <a href="{{ $resetUrl }}" style="display:inline-block;background:#0077ff;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:8px;font-size:14px;font-weight:bold;">
                        Elegir nueva contraseña
                    </a>
                </p>

                <p style="font-size:13px;line-height:1.6;color:#64748b;">
                    Si no pediste este cambio, puedes ignorar este mensaje. Tu contraseña actual seguirá igual.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
