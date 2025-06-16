<!DOCTYPE html>
<html>
<head>
    <title>Invoice Cetak</title>
</head>
<body>
    <h1>Invoice: {{ $invoice->invoice_no }}</h1>
    <p>Customer: {{ $invoice->customer_name }}</p>
    <p>Tanggal: {{ $invoice->created_at->format('d-m-Y') }}</p>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        {{-- <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->product->nama ?? '-' }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody> --}}
    </table>
</body>
</html>
