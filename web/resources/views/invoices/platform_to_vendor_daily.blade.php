<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Sammelrechnung Plattformgebühren</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; margin-bottom: 50px; border-bottom: 2px solid #14b8a6; padding-bottom: 20px; }
        .company-details { text-align: right; font-size: 12px; color: #666; }
        .invoice-details { margin-bottom: 40px; }
        .invoice-title { font-size: 24px; color: #14b8a6; font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background-color: #f3f4f6; padding: 10px; text-align: left; font-size: 12px; border-bottom: 1px solid #e5e7eb; }
        td { padding: 10px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
        .totals { width: 300px; float: right; margin-top: 20px; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; }
        .totals-row.bold { font-weight: bold; border-top: 2px solid #333; padding-top: 10px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1 style="color: #14b8a6; margin: 0; font-size: 28px;">Ticketsout24</h1>
        </div>
        <div class="company-details">
            <strong>Ticketsout24 GmbH</strong><br>
            Musterstraße 1<br>
            12345 Musterstadt<br>
            USt-IdNr.: DE123456789
        </div>
    </div>

    <div class="invoice-details">
        <div style="float: left; width: 50%;">
            <strong>Rechnung an:</strong><br>
            {{ $vendor->vendorSettings?->company_name ?? $vendor->name }}<br>
            {{ $vendor->vendorSettings?->address }}<br>
            {{ $vendor->vendorSettings?->zip }} {{ $vendor->vendorSettings?->city }}
        </div>
        <div style="float: right; width: 40%; text-align: right;">
            <div class="invoice-title">Sammelrechnung</div>
            <div>Rechnungs-Nr.: <strong>{{ $invoice->invoice_number }}</strong></div>
            <div>Datum: {{ $invoice->created_at->format('d.m.Y') }}</div>
            <div>Leistungszeitraum: {{ $date->format('d.m.Y') }}</div>
        </div>
        <div style="clear: both;"></div>
    </div>

    <p style="margin-bottom: 30px;">Sehr geehrte(r) Herr/Frau {{ $vendor->name }},<br><br>hiermit stellen wir Ihnen die Plattformgebühren für die abgewickelten Ticketverkäufe vom {{ $date->format('d.m.Y') }} in Rechnung.</p>

    <table>
        <thead>
            <tr>
                <th>Bestell-Nr.</th>
                <th>Event</th>
                <th>Kunde</th>
                <th style="text-align: right;">Gebühr (Brutto)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_number }}</td>
                <td>{{ $order->event->title }}</td>
                <td>{{ $order->billing_first_name }} {{ $order->billing_last_name }}</td>
                <td style="text-align: right;">{{ number_format($order->platform_fee, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Nettobetrag:</span>
            <span>{{ number_format($invoice->net, 2, ',', '.') }} €</span>
        </div>
        <div class="totals-row">
            <span>zzgl. 19% USt.:</span>
            <span>{{ number_format($invoice->tax, 2, ',', '.') }} €</span>
        </div>
        <div class="totals-row bold">
            <span>Gesamtbetrag:</span>
            <span>{{ number_format($invoice->gross, 2, ',', '.') }} €</span>
        </div>
    </div>

    <div style="clear: both; margin-top: 60px;">
        <p>Der Rechnungsbetrag wurde bereits im Rahmen des Stripe-Auszahlungsprozesses (Split-Payment) automatisch von Ihren Einnahmen einbehalten. Eine gesonderte Zahlung ist <strong>nicht</strong> erforderlich.</p>
    </div>

    <div class="footer">
        Ticketsout24 GmbH | Geschäftsführer: Max Mustermann | HRB 12345 Amtsgericht Musterstadt
    </div>

</body>
</html>
