<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta creada - Baifa</title>
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
                            <span style="font-size: 13px; color: #22c55e; margin-left: 8px; font-weight: 600;">Acceso</span>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 36px;">
                            <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 16px;">¡Tu cuenta en Baifa ha sido creada!</h1>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 16px;">
                                Hola <strong>{{ $userName }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 20px;">
                                Un administrador te ha registrado en la plataforma Baifa con el perfil de <strong>{{ $roleName }}</strong>.
                            </p>
                            
                            <!-- Credential Box -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                                <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: 600; color: #0f172a;">
                                    Tus credenciales de acceso:
                                </p>
                                <table style="width: 100%; font-size: 14px; color: #334155;">
                                    <tr>
                                        <td style="padding: 4px 0; width: 140px; color: #64748b;">Correo:</td>
                                        <td style="padding: 4px 0; font-weight: 600;">{{ $userEmail }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748b;">Rol asignado:</td>
                                        <td style="padding: 4px 0; font-weight: 600; color: #16a34a;">{{ $roleName }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0; color: #64748b;">Contraseña inicial:</td>
                                        <td style="padding: 4px 0; font-family: monospace; font-size: 15px; font-weight: 700; color: #0f172a;">{{ $initialPassword }}</td>
                                    </tr>
                                </table>
                            </div>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin-bottom: 24px;">
                                Te recomendamos cambiar tu contraseña una vez que inicies sesión en la plataforma.
                            </p>

                            <div style="text-align: center; margin: 32px 0;">
                                <a href="http://localhost:3000/login" style="display: inline-block; background-color: #16a34a; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600; padding: 12px 32px; border-radius: 8px; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.25);">Iniciar sesión</a>
                            </div>
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
