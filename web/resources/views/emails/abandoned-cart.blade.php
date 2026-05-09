<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Du hast da was vergessen!</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { max-w-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 30px 20px; border: 1px solid #eee; border-top: none; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; background-color: #6366f1; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 20px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Hoppla, {{ $user->name }}!</h2>
        </div>
        <div class="content">
            <p>Es sieht so aus, als hättest du noch Tickets für <strong>{{ $event->title }}</strong> in deinem Warenkorb liegen lassen.</p>
            
            <p>Die Plätze sind sehr begehrt und wir können sie leider nicht ewig für dich reservieren. Sichere dir deine Tickets, bevor das Event ausverkauft ist!</p>
            
            <div style="text-align: center;">
                <a href="{{ $checkoutUrl }}" class="btn">Jetzt Checkout abschließen</a>
            </div>
            
            <p style="margin-top: 30px;">Wir freuen uns auf dich!</p>
            <p>Dein Team von Ticketsout24</p>
        </div>
        <div class="footer">
            <p>Falls du deine Meinung geändert hast, kannst du diese E-Mail einfach ignorieren.</p>
        </div>
    </div>
</body>
</html>
