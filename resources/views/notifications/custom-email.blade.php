<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f5f7;">
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" max-width="600px"
       style="max-width: 600px; background-color: {{ $colors['bg'] }}; border: 2px solid {{ $colors['border'] }}; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">

    <!-- Header -->
    <tr>
        <td style="padding: 24px 24px 10px 24px;">
            <h1 style="margin: 0; font-size: 22px; font-weight: bold; color: {{ $colors['headerText'] }};">
                {{ $title }}
            </h1>
        </td>
    </tr>

    <!-- Notification body -->
    <tr>
        <td style="padding: 10px 24px 20px 24px; font-size: 16px; line-height: 1.6; color: {{ $colors['mainText'] }};">
            {!! nl2br(e($notificationBody)) !!}
        </td>
    </tr>

    <!-- Action button (if provided) -->
    @if(isset($actionUrl))
        <tr>
            <td style="padding: 10px 24px 20px 24px;">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" bgcolor="{{ $colors['btnBg'] }}" style="border: 1px solid {{ $colors['border'] }}; border-radius: 6px;">
                            <a href="{{ $actionUrl }}" target="_blank"
                               style="display: inline-block; padding: 12px 24px; font-size: 14px; font-weight: bold; text-decoration: none; color: {{ $colors['btnText'] }};">
                                {{ $actionText ?? 'View Details' }}
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endif

    <!-- Footer (sent time) -->
    <tr>
        <td style="padding: 15px 24px; background-color: rgba(0,0,0,0.02); border-top: 1px solid {{ $colors['border'] }}; font-size: 12px; color: {{ $colors['subText'] }};">
            Sent on {{ now()->format('Y-m-d H:i:s') }}
        </td>
    </tr>
</table>
</body>
</html>
