<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Ticket - BaiFa Helpdesk</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 580px; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06); border: 1px solid #e2e8f0;">
                    <!-- Encabezado Oscuro Corporativo -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 26px 36px; text-align: left;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <span style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">BaiFa</span>
                                        <span style="font-size: 13px; font-weight: 600; color: #3eb134; margin-left: 6px;">HELPDESK</span>
                                    </td>
                                    <td align="right">
                                        <span style="background-color: rgba(62, 177, 52, 0.15); border: 1px solid rgba(62, 177, 52, 0.3); color: #3eb134; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 9999px;">
                                            #{{ $ticket->code }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Cuerpo Principal -->
                    <tr>
                        <td style="padding: 36px;">
                            <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 14px; line-height: 1.3;">
                                Actualización en Ticket de Soporte
                            </h1>

                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 18px;">
                                @if($recipientRole === 'client')
                                Estimado/a cliente <strong>{{ $ticket->user->name }}</strong>,
                                @else
                                Estimado/a operador <strong>{{ $ticket->assignedAgent?->name ?? 'Equipo Técnico' }}</strong>,
                                @endif
                            </p>

                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin-bottom: 24px;">
                                Te informamos que el estatus del ticket de soporte <strong>#{{ $ticket->code }}</strong> correspondiente a tu consulta ha cambiado.
                            </p>

                            <!-- Tarjeta de Transición de Estados -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 24px;">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 600; margin-bottom: 8px;">
                                    Transición de Estatus
                                </div>
                                <div style="font-size: 15px; font-weight: 700; color: #0f172a;">
                                    <span style="color: #64748b; text-decoration: line-through;">{{ $oldStatusLabel }}</span>
                                    <span style="color: #3eb134; margin: 0 10px;">➔</span>
                                    <span style="background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; padding: 3px 10px; border-radius: 6px; font-size: 14px;">
                                        {{ $newStatusLabel }}
                                    </span>
                                </div>
                            </div>

                            <!-- Ficha del Ticket -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 24px; font-size: 13px;">
                                <tr>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; width: 35%;">Título:</td>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; color: #0f172a; font-weight: 700;">{{ $ticket->title }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600;">Categoría:</td>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600;">Operador Asignado:</td>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ $ticket->assignedAgent?->name ?? 'Sin asignar aún' }}</td>
                                </tr>
                                @if($changedByName)
                                <tr>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600;">Modificado por:</td>
                                    <td style="padding: 12px 18px; border-bottom: 1px solid #e2e8f0; color: #0f172a;">{{ $changedByName }}</td>
                                </tr>
                                @endif
                                @if($comment)
                                <tr>
                                    <td style="padding: 12px 18px; color: #64748b; font-weight: 600; vertical-align: top;">Comentario:</td>
                                    <td style="padding: 12px 18px; color: #0f172a; font-style: italic;">"{{ $comment }}"</td>
                                </tr>
                                @endif
                            </table>

                            <!-- Botón de Acción -->
                            <div style="text-align: center; margin: 30px 0 10px 0;">
                                <a href="{{ $ticketUrl }}" style="display: inline-block; background-color: #3eb134; color: #ffffff; text-decoration: none; padding: 13px 28px; border-radius: 8px; font-weight: 700; font-size: 14px; box-shadow: 0 4px 10px rgba(62, 177, 52, 0.25);">
                                    Acceder al Helpdesk
                                </a>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 22px 36px; text-align: center; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                            Este es un mensaje automático del sistema Helpdesk de <strong>BaiFa Power</strong>.<br>
                            Remitente oficial: <strong>chirinosjuane@gmail.com</strong>.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
