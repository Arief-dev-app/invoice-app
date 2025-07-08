<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Cetak</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        h1 { font-size: 20px; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h1>Invoice: {{ $transaksi->trans_no }}</h1>
    <p><strong>Customer:</strong> {{ $transaksi->customer->name }}</p>
    <p><strong>Tanggal:</strong> {{ $transaksi->transaction_date }}</p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaksi->detail as $item)
                <tr>
                    <td>{{ $item->prd->nama ?? '-' }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
