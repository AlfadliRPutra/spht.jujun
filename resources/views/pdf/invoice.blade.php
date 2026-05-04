<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 10px;
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 14px;
        }
        .header p {
            margin: 2px 0;
        }
        .section {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            text-align: left;
            padding: 3px 0;
            vertical-align: top;
        }
        .text-right {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .bold {
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }
    </style>
</head>
<body>

<div class="header">
    <h2>INVOICE</h2>
    <p>Order #{{ $order->id }}</p>
    <p>{{ $order->created_at->format('d/m/Y H:i') }}</p>
</div>

<div class="section">
    <strong>Pelanggan:</strong><br>
    {{ $order->user->name }}<br>
    {{ $order->alamat_pengiriman }}<br>
    Tlp: {{ $order->telepon }}
</div>

@if($role === 'petani')
    <div class="section">
        <strong>Toko/Petani:</strong><br>
        {{ $petani->name }}
    </div>

    <div class="section">
        <table class="table">
            <thead>
                <tr>
                    <th colspan="2">Item</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @foreach($ownItems as $item)
                @php 
                    $itemTotal = $item->harga * $item->jumlah;
                    $subtotal += $itemTotal;
                @endphp
                <tr>
                    <td colspan="3">{{ $item->product_name }}</td>
                </tr>
                <tr>
                    <td style="width: 20px;">{{ $item->jumlah }}x</td>
                    <td>@ {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($itemTotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <table class="table">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($shipping)
            <tr>
                <td>Ongkir</td>
                <td class="text-right">{{ number_format($shipping->shipping_cost, 0, ',', '.') }}</td>
            </tr>
            <tr class="bold">
                <td>Total (Toko ini)</td>
                <td class="text-right">{{ number_format($subtotal + $shipping->shipping_cost, 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>
    </div>
@else
    @php $grandTotal = 0; @endphp
    @foreach($groupedItems as $storeId => $items)
        @php 
            $storeName = $items->first()->product->user->name ?? 'Toko Tidak Diketahui'; 
            $shipping = $order->shippings->firstWhere('store_id', $storeId);
        @endphp
        <div class="section">
            <strong>Toko: {{ $storeName }}</strong>
            <table class="table">
                @php $subtotal = 0; @endphp
                @foreach($items as $item)
                @php 
                    $itemTotal = $item->harga * $item->jumlah;
                    $subtotal += $itemTotal;
                @endphp
                <tr>
                    <td colspan="3">{{ $item->product_name }}</td>
                </tr>
                <tr>
                    <td style="width: 20px;">{{ $item->jumlah }}x</td>
                    <td>@ {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($itemTotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                
                <tr><td colspan="3"><hr style="border:0; border-top:1px dotted #000; margin: 3px 0;"></td></tr>
                <tr>
                    <td colspan="2">Subtotal Toko</td>
                    <td class="text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($shipping)
                <tr>
                    <td colspan="2">Ongkir</td>
                    <td class="text-right">{{ number_format($shipping->shipping_cost, 0, ',', '.') }}</td>
                </tr>
                @php $grandTotal += ($subtotal + $shipping->shipping_cost); @endphp
                @else
                @php $grandTotal += $subtotal; @endphp
                @endif
            </table>
        </div>
    @endforeach

    <div class="section">
        <table class="table bold">
            <tr>
                <td>Total Tagihan</td>
                <td class="text-right">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </table>
        <p style="margin-top: 5px;">
            Status: {{ $order->status->value }}<br>
            Pembayaran: {{ $order->payment_method?->value ?? 'Midtrans' }}
        </p>
    </div>
@endif

<div class="footer">
    Terima Kasih!<br>
    Simpan struk ini sebagai bukti pembayaran yang sah.
</div>

</body>
</html>