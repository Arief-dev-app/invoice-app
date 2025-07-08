<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        .tanggal {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #eee;
        }
    </style>
</head>
<body>

    <h2>Laporan Penjualan</h2>
    <div class="tanggal">
        Tanggal: {{ date('d-m-Y', strtotime($start)) }} s/d {{ date('d-m-Y', strtotime($end)) }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th>Nama Barang</th>
                <th style="width: 15%;">Qty</th>
                <th style="width: 20%;">Harga Jual</th>
                <th style="width: 20%;">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; $grandTotal = 0; @endphp
            @foreach ($data as $item)
                @php
                    $total = $item->total_qty * $item->prd->harga_jual;
                    $grandTotal += $total;
                  
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->prd->nama }}</td>
                    <td>{{ number_format($item->total_qty) }}</td>
                    <td>{{ number_format($item->prd->harga_jual) }}</td>
                    <td>Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Total</strong></td>
             
                <td><strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

</body>
</html>
