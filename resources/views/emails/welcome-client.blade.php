<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a Baifa</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f5f7; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 560px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 28px 36px; text-align: left;">
                            <span style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">Baifa</span>
                            <span style="font-size: 13px; color: #22c55e; margin-left: 8px; font-weight: 600;">Bienvenida</span>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 36px;">
                            <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 16px;">¡Bienvenido a la plataforma Baifa!</h1>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 16px;">
                                Hola <strong>{{ $userName }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 20px;">
                                Tu registro en el sistema se ha completado con éxito. Tu cuenta ha sido habilitada con el perfil de <strong>Cliente</strong>.
                            </p>
                            
                            <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 16px; border-radius: 6px; margin-bottom: 24px;">
                                <p style="margin: 0; font-size: 14px; color: #166534; font-weight: 500;">
                                    Detalles de tu cuenta:
                                </p>
                                <p style="margin: 6px 0 0 0; font-size: 13px; color: #15803d;">
                                    • Correo electrónico: <strong>{{ $userEmail }}</strong><br>
                                    • Perfil asignado: <strong>Cliente</strong>
                                </p>
                            </div>

                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px;">
                                Ya puedes acceder al portal para consultar tus pedidos, gestionar tu catálogo y dar seguimiento a tus servicios.
                            </p>

                            <div style="text-align: center; margin: 32px 0;">
                                <a href="http://localhost:3000/login" style="display: inline-block; background-color: #16a34a; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600; padding: 12px 32px; border-radius: 8px; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.25);">Iniciar sesión</a>
                            </div>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin-bottom: 0;">
                                Si tienes alguna duda o inconveniente, nuestro equipo de soporte está siempre disponible para asistirte.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 36px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="font-size: 12px; color: #94a3b8; margin: 0;">
                                &copy; {{ date('Y') }} Baifa. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
