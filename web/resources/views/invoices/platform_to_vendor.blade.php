<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Abrechnung Plattformgebühr {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { display: flex; justify-content: space-between; border-bottom: 1px solid #ccc; padding-bottom: 20px; margin-bottom: 20px; }
        .invoice-details { margin-top: 20px; }
        table { wwidth: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .total { font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Abrechnung Plattformgebühr</h1>
            <p>Rechnungsnummer: {{ $invoice->invoice_number }}</p>
            <p>Datum: {{ $invoice->created_at->format('d.m.Y') }}</p>
        </div>
        <div class="text-right">
            <h3>Aussteller:</h3>
            <p>{{ config('app.name') }} Plattform</p>
        </div>
    </div>

    <div class="invoice-details">
        <h3>Rechnungsempfänger:</h3>
        <p>{{ $vendor->name }}<br>{{ $vendor->email }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Beschreibung</th>
                <th>Netto</th>
                <th>MwSt. (19%)</th>
                <th>Brutto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Transaktions- und Plattformgebühr für Bestellung #{{ $order->id }}</td>
                <td>{{ number_format($invoice->net, 2, ',', '.') }} €</td>
                <td>{{ number_format($invoice->tax, 2, ',', '.') }} €</td>
                <td>{{ number_format($invoice->gross, 2, ',', '.') }} €</td>
            </tr>
        </tbody>
    </table>

    <div class="invoice-details text-right">
        <p class="total">Einbehaltener Betrag: {{ number_format($invoice->gross, 2, ',', '.') }} €</p>
    </div>
</body>
</html>
