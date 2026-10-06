<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a BaiFa</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 580px; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 26px 36px; text-align: left;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <span style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">BaiFa</span>
                                        <span style="font-size: 13px; font-weight: 600; color: #94a3b8; margin-left: 6px;">POWER</span>
                                    </td>
                                    <td align="right">
                                        <span style="background-color: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">
                                            Registro Exitoso
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Main Body -->
                    <tr>
                        <td style="padding: 36px;">
                            <h1 style="font-size: 21px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 14px; line-height: 1.3;">
                                ¡Bienvenido a la plataforma BaiFa!
                            </h1>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 18px;">
                                Estimado/a <strong>{{ $userName }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 24px;">
                                Te confirmamos que el registro de tu cuenta como <strong>Cliente Empresarial</strong> se ha completado de forma exitosa en el sistema de rastreo y gestión de generadores industriales <strong>BaiFa Power</strong>.
                            </p>
                            
                            <!-- Information Card -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                                    Detalles de la Cuenta y Empresa
                                </div>
                                <table role="presentation" width="100%" style="font-size: 14px; color: #334155; border-collapse: collapse;">
                                    @if(!empty($companyName))
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b; width: 140px;">Empresa:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0f172a;">{{ $companyName }}</td>
                                    </tr>
                                    @endif
                                    @if(!empty($rif))
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">RIF / ID Fiscal:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0f172a;">{{ $rif }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Contacto:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0f172a;">{{ $userName }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Correo electrónico:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0f172a;">{{ $userEmail }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Perfil asignado:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #16a34a;">Cliente Empresarial</td>
                                    </tr>
                                    @if(!empty($initialPassword))
                                    <tr>
                                        <td style="padding: 8px 0; color: #64748b;">Contraseña inicial:</td>
                                        <td style="padding: 8px 0;">
                                            <span style="display: inline-block; background-color: #e2e8f0; color: #0f172a; font-family: ui-monospace, Menlo, Monaco, Consolas, monospace; font-size: 14px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                                {{ $initialPassword }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            @if(!empty($initialPassword))
                            <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px; margin-bottom: 24px;">
                                <p style="margin: 0; font-size: 13px; color: #92400e; line-height: 1.5;">
                                    <strong>Recomendación de seguridad:</strong> Tu cuenta fue creada con una clave provisional. Te recomendamos cambiarla al ingresar a tu portal.
                                </p>
                            </div>
                            @endif

                            <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 16px; border-radius: 6px; margin-bottom: 24px;">
                                <p style="margin: 0 0 6px 0; font-size: 14px; font-weight: 600; color: #166534;">
                                    Servicios disponibles desde tu portal:
                                </p>
                                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #15803d; line-height: 1.6;">
                                    <li>Seguimiento y rastreo en tiempo real de tus generadores eléctricos asignados.</li>
                                    <li>Notificaciones automáticas en cada punto de control y actualización de ruta.</li>
                                    <li>Fichas técnicas de activos y confirmación de recepción e instalación.</li>
                                </ul>
                            </div>

                            <div style="text-align: center; margin: 32px 0 24px 0;">
                                <a href="{{ $loginUrl }}" style="display: inline-block; background-color: #16a34a; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600; padding: 13px 36px; border-radius: 8px; box-shadow: 0 3px 6px rgba(22, 163, 74, 0.3);">
                                    Acceder a mi Portal
                                </a>
                            </div>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin-bottom: 0; text-align: center;">
                                Si tienes alguna duda o requieres asistencia técnica, nuestro equipo está a tu disposición en <a href="mailto:chirinosjuane@gmail.com" style="color: #16a34a; text-decoration: none;">chirinosjuane@gmail.com</a>.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 22px 36px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="font-size: 12px; color: #94a3b8; margin: 0; line-height: 1.5;">
                                &copy; {{ date('Y') }} BaiFa Power. Todos los derechos reservados.<br>
                                Sistema de Gestión y Trazabilidad Logística de Generadores Industriales.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
