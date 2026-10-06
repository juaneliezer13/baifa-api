<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Generador - BaiFa</title>
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
                                        @if($statusChanged)
                                        <span style="background-color: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); color: #38bdf8; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">
                                            Cambio de Estatus
                                        </span>
                                        @else
                                        <span style="background-color: rgba(148, 163, 184, 0.15); border: 1px solid rgba(148, 163, 184, 0.3); color: #cbd5e1; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">
                                            Actualización de Equipo
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <!-- Main Body -->
                    <tr>
                        <td style="padding: 36px;">
                            <h1 style="font-size: 21px; font-weight: 700; color: #0f172a; margin-top: 0; margin-bottom: 14px; line-height: 1.3;">
                                @if($statusChanged)
                                Actualización de estatus de tu generador
                                @else
                                Modificación en la ficha de tu generador
                                @endif
                            </h1>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 18px;">
                                Estimado/a cliente <strong>{{ $client->company_fiscal_name ?: $client->contact_name }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 24px;">
                                @if($statusChanged)
                                Te informamos que el estado logístico de tu generador eléctrico <strong>#{{ $generator->serial_number }}</strong> ha sido actualizado en la plataforma.
                                @else
                                Te informamos que se han actualizado los datos de tu generador eléctrico <strong>#{{ $generator->serial_number }}</strong> en el sistema <strong>BaiFa Power</strong>.
                                @endif
                            </p>

                            @if($statusChanged)
                            <!-- Status Transition Card -->
                            <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #0369a1; letter-spacing: 0.5px; margin-bottom: 10px;">
                                    Transición de Estatus Operativo
                                </div>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td align="center">
                                            <span style="display: inline-block; background-color: #e2e8f0; color: #475569; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 13px;">
                                                {{ $oldStatusLabel ?? 'Estado previo' }}
                                            </span>
                                            <span style="display: inline-block; margin: 0 12px; font-size: 16px; font-weight: 700; color: #0284c7;">
                                                ➔
                                            </span>
                                            <span style="display: inline-block; background-color: #0284c7; color: #ffffff; padding: 6px 14px; border-radius: 6px; font-weight: 700; font-size: 14px; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);">
                                                {{ $newStatusLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            @endif

                            <!-- Generator Card -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                                    Ficha del Generador Eléctrico
                                </div>
                                <table role="presentation" width="100%" style="font-size: 14px; color: #334155; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b; width: 160px;">Serial de fábrica:</td>
                                        <td style="padding: 6px 0; font-weight: 700; color: #0f172a; font-family: ui-monospace, Menlo, Monaco, Consolas, monospace;">
                                            {{ $generator->serial_number }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Modelo:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0f172a;">{{ $generator->model }}</td>
                                    </tr>
                                    @if($generator->name)
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Nombre del activo:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0f172a;">{{ $generator->name }}</td>
                                    </tr>
                                    @endif
                                    @if($generator->capacity_kva)
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Capacidad nominal:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0f172a;">{{ $generator->capacity_kva }} kVA</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Estatus actual:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0284c7;">
                                            {{ $generator->status->label() }}
                                        </td>
                                    </tr>
                                    @if($generator->estimated_arrival_date)
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Llegada estimada (ETA):</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0f172a;">
                                            {{ $generator->estimated_arrival_date->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            @if(!empty($changedFields))
                            <!-- Modified Fields Breakdown -->
                            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 24px;">
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                                    Resumen de Cambios Aplicados
                                </div>
                                <table role="presentation" width="100%" style="font-size: 13px; border-collapse: collapse;">
                                    @foreach($changedFields as $fieldKey => $change)
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 8px 0; color: #475569; font-weight: 600; width: 40%;">{{ $change['label'] }}:</td>
                                        <td style="padding: 8px 0; color: #0f172a;">
                                            <span style="color: #64748b; text-decoration: line-through;">{{ $change['old'] }}</span>
                                            <span style="color: #0284c7; font-weight: 600; margin-left: 8px;">➔ {{ $change['new'] }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            @endif

                            @if($generator->notes)
                            <div style="background-color: #f8fafc; border-left: 4px solid #64748b; padding: 14px 18px; border-radius: 6px; margin-bottom: 24px;">
                                <p style="margin: 0; font-size: 13px; color: #334155; line-height: 1.5;">
                                    <strong>Notas / Observaciones:</strong><br>
                                    {{ $generator->notes }}
                                </p>
                            </div>
                            @endif

                            <div style="text-align: center; margin: 32px 0 24px 0;">
                                <a href="{{ $trackingUrl }}" style="display: inline-block; background-color: #0284c7; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600; padding: 13px 36px; border-radius: 8px; box-shadow: 0 3px 6px rgba(2, 132, 199, 0.3);">
                                    Consultar en el Portal
                                </a>
                            </div>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin-bottom: 0; text-align: center;">
                                Para mayor información sobre tu generador, puedes responder directamente a este correo o contactar al equipo técnico.
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
