<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Control - BaiFa</title>
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
                                        @if($isArrival || $isAvailable)
                                        <span style="background-color: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3); color: #22c55e; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">
                                            @if($isAvailable) Equipo Operativo @else Llegada a Destino @endif
                                        </span>
                                        @elseif($isInTransit)
                                        <span style="background-color: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); color: #38bdf8; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">
                                            En Tránsito
                                        </span>
                                        @elseif($isWarehouse)
                                        <span style="background-color: rgba(168, 85, 247, 0.15); border: 1px solid rgba(168, 85, 247, 0.3); color: #c084fc; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">
                                            En Almacén
                                        </span>
                                        @else
                                        <span style="background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 9999px;">
                                            Punto de Control
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
                                @if($isArrival)
                                ¡Llegada confirmada de tu generador!
                                @elseif($isAvailable)
                                ¡Tu generador ya se encuentra disponible y operativo!
                                @elseif($isInTransit)
                                Tu generador va en camino hacia su destino
                                @elseif($isWarehouse)
                                Generador registrado en almacén
                                @else
                                Nuevo avance de punto de control en ruta
                                @endif
                            </h1>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-top: 0; margin-bottom: 18px;">
                                Estimado/a cliente <strong>{{ $client->company_fiscal_name ?: $client->contact_name }}</strong>,
                            </p>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 24px;">
                                @if($isArrival)
                                ¡Excelentes noticias! Te confirmamos que tu generador eléctrico <strong>#{{ $generator->serial_number }}</strong> ha arribado exitosamente a su locación final.
                                @elseif($isAvailable)
                                ¡Todo listo! Tu generador eléctrico <strong>#{{ $generator->serial_number }}</strong> ha completado los procesos técnicos de instalación y ahora se encuentra <strong>100% disponible y operativo</strong>.
                                @elseif($isInTransit)
                                Te informamos que tu generador eléctrico <strong>#{{ $generator->serial_number }}</strong> se encuentra en tránsito y ha registrado un nuevo punto de avance en ruta.
                                @elseif($isWarehouse)
                                Te confirmamos que tu generador eléctrico <strong>#{{ $generator->serial_number }}</strong> se encuentra resguardado y verificado en almacén, listo para las siguientes fases de despacho.
                                @else
                                Se ha registrado una nueva actualización en los puntos de control del seguimiento logístico de tu generador eléctrico <strong>#{{ $generator->serial_number }}</strong>.
                                @endif
                            </p>

                            <!-- Checkpoint Event Card -->
                            <div style="background-color: @if($isArrival || $isAvailable) #f0fdf4 @elseif($isInTransit) #f0f9ff @elseif($isWarehouse) #faf5ff @else #fffbeb @endif; border: 1px solid @if($isArrival || $isAvailable) #bbf7d0 @elseif($isInTransit) #bae6fd @elseif($isWarehouse) #e9d5ff @else #fde68a @endif; border-left: 5px solid @if($isArrival || $isAvailable) #16a34a @elseif($isInTransit) #0284c7 @elseif($isWarehouse) #9333ea @else #d97706 @endif; border-radius: 10px; padding: 22px; margin-bottom: 24px;">
                                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: @if($isArrival || $isAvailable) #166534 @elseif($isInTransit) #0369a1 @elseif($isWarehouse) #6b21a8 @else #92400e @endif; letter-spacing: 0.5px; margin-bottom: 12px;">
                                    Registro del Punto de Control
                                </div>
                                <table role="presentation" width="100%" style="font-size: 14px; color: #334155; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b; width: 160px;">Punto / Ubicación:</td>
                                        <td style="padding: 6px 0; font-weight: 700; font-size: 15px; color: #0f172a;">
                                            📍 {{ $checkpoint->checkpoint_name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Estatus del activo:</td>
                                        <td style="padding: 6px 0; font-weight: 700; color: @if($isArrival || $isAvailable) #16a34a @elseif($isInTransit) #0284c7 @elseif($isWarehouse) #9333ea @else #d97706 @endif;">
                                            {{ $checkpoint->status->label() }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: #64748b;">Fecha y hora:</td>
                                        <td style="padding: 6px 0; font-weight: 600; color: #0f172a;">
                                            {{ $checkpoint->event_date?->format('d/m/Y h:i A') ?? now()->format('d/m/Y h:i A') }}
                                        </td>
                                    </tr>
                                    @if($checkpoint->notes)
                                    <tr>
                                        <td style="padding: 8px 0 0 0; color: #64748b; vertical-align: top;">Observaciones:</td>
                                        <td style="padding: 8px 0 0 0; color: #334155; line-height: 1.5; font-style: italic;">
                                            "{{ $checkpoint->notes }}"
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            <!-- Generator Card -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
                                <div style="font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                                    Datos del Generador
                                </div>
                                <table role="presentation" width="100%" style="font-size: 14px; color: #334155; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b; width: 160px;">Serial de fábrica:</td>
                                        <td style="padding: 5px 0; font-weight: 700; color: #0f172a; font-family: ui-monospace, Menlo, Monaco, Consolas, monospace;">
                                            {{ $generator->serial_number }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b;">Modelo:</td>
                                        <td style="padding: 5px 0; font-weight: 600; color: #0f172a;">{{ $generator->model }}</td>
                                    </tr>
                                    @if($generator->capacity_kva)
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b;">Capacidad nominal:</td>
                                        <td style="padding: 5px 0; font-weight: 600; color: #0f172a;">{{ $generator->capacity_kva }} kVA</td>
                                    </tr>
                                    @endif
                                    @if($generator->estimated_arrival_date)
                                    <tr>
                                        <td style="padding: 5px 0; color: #64748b;">Fecha estimada (ETA):</td>
                                        <td style="padding: 5px 0; font-weight: 600; color: #0f172a;">{{ $generator->estimated_arrival_date->format('d/m/Y') }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            <div style="text-align: center; margin: 32px 0 24px 0;">
                                <a href="{{ $trackingUrl }}" style="display: inline-block; background-color: @if($isArrival || $isAvailable) #16a34a @else #0284c7 @endif; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600; padding: 13px 36px; border-radius: 8px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);">
                                    Rastrear Generador en Tiempo Real
                                </a>
                            </div>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin-bottom: 0; text-align: center;">
                                Puedes dar seguimiento detallado a todos los puntos de control registrados a través de tu portal de cliente.
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
