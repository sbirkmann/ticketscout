<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dein Event steht kurz bevor!</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #6366f1; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 30px 20px; border: 1px solid #eee; border-top: none; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; background-color: #6366f1; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        .info-box { background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #6366f1; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">Morgen ist es soweit! 🎉</h2>
        </div>
        <div class="content">
            <p>Hallo {{ $user->name }},</p>
            
            <p>wir möchten dich daran erinnern, dass dein Event <strong>{{ $event->title }}</strong> bereits in weniger als 24 Stunden startet!</p>
            
            <div class="info-box">
                <p style="margin-top: 0;"><strong>Wann:</strong> {{ \Carbon\Carbon::parse($event->start_date)->format('d.m.Y H:i') }} Uhr</p>
                @if($event->location)
                <p><strong>Wo:</strong> {{ $event->location->name }}<br>
                {{ $event->location->address }}, {{ $event->location->zip }} {{ $event->location->city }}</p>
                @endif
            </div>

            <p>Bitte halte deine Tickets beim Einlass bereit. Du findest deine Tickets in deinem Customer Dashboard oder du kannst sie dir hier direkt noch einmal ansehen und herunterladen:</p>
            
            <div style="text-align: center;">
                <a href="{{ route('dashboard.orders') }}" class="btn">Meine Tickets öffnen</a>
            </div>
            
            <p style="margin-top: 30px;">Wir wünschen dir viel Spaß beim Event!</p>
            <p>Dein Team von Ticketsout24</p>
        </div>
        <div class="footer">
            <p>Diese E-Mail wurde automatisch generiert. Bitte antworte nicht darauf.</p>
        </div>
    </div>
</body>
</html>
