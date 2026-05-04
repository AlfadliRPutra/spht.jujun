<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        .header p {
            margin: 0 0 5px 0;
            color: #666;
        }
        .summary-box {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .summary-item {
            display: table-cell;
            padding: 15px;
            text-align: center;
            border-right: 1px solid #ddd;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-item .label {
            display: block;
            font-size: 11px;
            color: #777;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .status {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Laporan Penjualan Petani</h1>
    <p>Petani/Toko: <strong>{{ $petani->name }}</strong></p>
    @if($dateFrom || $dateTo)
        <p>Periode: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') : 'Awal' }} - {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d/m/Y') : 'Sekarang' }}</p>
    @else
        <p>Periode: Semua Waktu</p>
    @endif
    <p>Tanggal Cetak: {{ now()->format('d/m/Y H:i') }}</p>
</div>

<div class="summary-box">
    <div class="summary-item">
        <span class="label">Total Pendapatan Selesai</span>
        <span class="value">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</span>
    </div>
    <div class="summary-item">
        <span class="label">Total Produk Terjual Selesai</span>
        <span class="value">{{ number_format($totalTerjual, 0, ',', '.') }} Item</span>
    </div>
    <div class="summary-item">
        <span class="label">Total Transaksi Selesai</span>
        <span class="value">{{ number_format($totalTransaksi, 0, ',', '.') }} Transaksi</span>
    </div>
</div>

<h3>Daftar Pesanan</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Item & Qty</th>
            <th class="text-right">Total Tagihan</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $order)
            @php
                // Filter items that belong to this petani
                $ownItems = $order->items->filter(fn($i) => $i->product?->user_id === $petani->id);
                if($ownItems->isEmpty()) continue;
                
                $orderTotal = 0;
                $itemTexts = [];
                foreach($ownItems as $item) {
                    $orderTotal += ($item->harga * $item->jumlah);
                    $itemTexts[] = $item->product_name . ' (' . $item->jumlah . 'x)';
                }
            @endphp
            <tr>
                <td>#{{ $order->id }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}<br><small>{{ $order->created_at->format('H:i') }}</small></td>
                <td>{{ $order->user->name }}</td>
                <td>{!! nl2br(implode("\n", $itemTexts)) !!}</td>
                <td class="text-right">Rp {{ number_format($orderTotal, 0, ',', '.') }}</td>
                <td class="text-center">{{ $order->status->value }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data penjualan pada periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>