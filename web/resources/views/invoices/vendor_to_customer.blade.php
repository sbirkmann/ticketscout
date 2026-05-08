<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Rechnung {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; font-size: 14px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { max-width: 150px; max-height: 80px; }
        .invoice-details { margin-top: 20px; margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f8f9fa; border-bottom: 2px solid #ddd; padding: 10px; text-align: left; }
        td { border-bottom: 1px solid #eee; padding: 10px; text-align: left; }
        .total { font-weight: bold; font-size: 16px; border-top: 2px solid #333; padding-top: 10px;}
        .text-right { text-align: right; }
        .footer { margin-top: 50px; font-size: 11px; color: #777; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
        .tax-info { margin-top: 30px; font-size: 12px; color: #555; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            @if(isset($settings) && $settings->invoice_logo_path)
                <img src="{{ public_path('storage/' . $settings->invoice_logo_path) }}" class="logo" alt="Logo">
            @else
                <h1>Rechnung</h1>
            @endif
            <p style="margin-top: 15px;"><strong>Rechnungsnummer:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Datum:</strong> {{ $invoice->created_at->format('d.m.Y') }}</p>
        </div>
        <div class="text-right">
            <h3>Aussteller:</h3>
            <p>{{ $vendor->name }}<br>{{ $vendor->email }}</p>
        </div>
    </div>

    <div class="invoice-details">
        <h3>Rechnungsempfänger:</h3>
        <p>{{ $customerName }}<br>{{ $order->guest_email ?? ($order->user ? $order->user->email : '') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Beschreibung</th>
                <th>Menge</th>
                <th>Netto</th>
                <th>MwSt.</th>
                <th>Brutto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->ticketCategory ? $item->ticketCategory->name : 'Ticket' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format(($item->price / (1 + (($item->ticketCategory->tax_rate ?? 0) / 100))) * $item->quantity, 2, ',', '.') }} €</td>
                <td>{{ $item->ticketCategory->tax_rate ?? 0 }}%</td>
                <td>{{ number_format($item->price * $item->quantity, 2, ',', '.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="invoice-details text-right">
        <p>Netto: {{ number_format($invoice->net, 2, ',', '.') }} €</p>
        <p>MwSt.: {{ number_format($invoice->tax, 2, ',', '.') }} €</p>
        <p class="total">Gesamtbetrag: {{ number_format($invoice->gross, 2, ',', '.') }} €</p>
    </div>

    @if(isset($settings) && $settings->invoice_tax_info)
    <div class="tax-info">
        <p>{!! nl2br(e($settings->invoice_tax_info)) !!}</p>
    </div>
    @endif

    @if(isset($settings) && $settings->invoice_footer_text)
    <div class="footer">
        {!! nl2br(e($settings->invoice_footer_text)) !!}
    </div>
    @endif
</body>
</html>
