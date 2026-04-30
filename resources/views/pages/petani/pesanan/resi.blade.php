@php
    /** @var \App\Models\Order $order */
    /** @var \App\Models\User $petani */
    /** @var \Illuminate\Support\Collection $ownItems */
    /** @var ?\App\Models\OrderShipping $shipping */

    $title         = 'Resi #'.$order->id;
    $totalQty      = (int) $ownItems->sum('jumlah');
    $totalWeightKg = (float) $ownItems->sum(fn ($i) => ($i->weight_kg ?? $i->product?->weight_kg ?? 0) * $i->jumlah);
    $subtotal      = (float) $ownItems->sum(fn ($i) => $i->harga * $i->jumlah);
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.41.1/dist/tabler-icons.min.css">
    <style>
        :root { --ink:#0f172a; --muted:#64748b; --line:#cbd5e1; --green:#15803d; }
        * { box-sizing:border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, sans-serif;
            color: var(--ink); background: #f1f5f9;
            margin:0; padding:24px 12px; line-height:1.45;
        }
        .toolbar {
            max-width: 700px; margin: 0 auto 14px;
            display:flex; gap:8px; justify-content:flex-end; align-items:center;
        }
        .btn {
            display:inline-flex; align-items:center; gap:6px;
            padding:8px 14px; border-radius:8px; font-size:.9rem; font-weight:600;
            border:1px solid var(--line); background:#fff; color:var(--ink);
            cursor:pointer; text-decoration:none;
        }
        .btn:hover { background:#f8fafc; }
        .btn-primary { background: var(--green); border-color: var(--green); color:#fff; }
        .btn-primary:hover { background: #166534; }

        .resi {
            max-width: 700px; margin: 0 auto;
            background:#fff; border:1px solid var(--line);
            padding: 24px 28px; border-radius:8px;
        }
        .resi-head {
            display:flex; justify-content:space-between; align-items:flex-start;
            border-bottom: 2px solid var(--ink); padding-bottom:14px; margin-bottom:18px; gap:16px;
        }
        .brand { display:flex; align-items:center; gap:10px; }
        .brand-mark {
            width:42px; height:42px; border-radius:50%;
            background: linear-gradient(135deg, #16a34a, #15803d); color:#fff;
            display:inline-flex; align-items:center; justify-content:center;
            font-weight:800; font-size:1rem; letter-spacing:.5px;
        }
        .brand-name { font-weight:800; font-size:1.05rem; letter-spacing:-.01em; }
        .brand-sub  { font-size:.7rem; color: var(--muted); margin-top:2px; }

        .resi-title { text-align:right; }
        .resi-title h1 { margin:0; font-size:1.15rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
        .resi-title .code { font-family:'SF Mono','Menlo',monospace; font-size:.85rem; color: var(--muted); }
        .resi-title .date { font-size:.72rem; color: var(--muted); margin-top:2px; }

        .addr-row { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }
        .addr-card {
            border:1px solid var(--line); border-radius:6px; padding:12px 14px;
        }
        .addr-card .lbl {
            font-size:.66rem; font-weight:700; text-transform:uppercase;
            color: var(--muted); letter-spacing:.08em; margin-bottom:6px;
            display:flex; align-items:center; gap:5px;
        }
        .addr-card .name { font-weight:700; font-size:.95rem; }
        .addr-card .text { font-size:.83rem; color:#334155; margin-top:4px; }
        .addr-card .meta { font-size:.78rem; color: var(--muted); margin-top:5px; }

        table { width:100%; border-collapse: collapse; margin: 6px 0 14px; }
        th, td { padding:8px 6px; text-align:left; font-size:.86rem; border-bottom:1px solid #e2e8f0; }
        th { background:#f8fafc; font-size:.7rem; text-transform:uppercase; color: var(--muted); letter-spacing:.06em; }
        td.num, th.num { text-align:right; font-variant-numeric: tabular-nums; }
        tfoot td { border-top: 2px solid var(--ink); border-bottom:none; font-weight:700; padding-top:10px; }

        .info-row { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-top:14px; }
        .info-box {
            border:1px dashed var(--line); border-radius:6px; padding:10px 14px;
            background:#fafbfc;
        }
        .info-box .lbl { font-size:.66rem; font-weight:700; text-transform:uppercase; color: var(--muted); letter-spacing:.08em; margin-bottom:4px; }
        .info-box .val { font-size:.92rem; font-weight:700; }
        .info-box .sub { font-size:.74rem; color: var(--muted); margin-top:2px; }

        .resi-foot { margin-top:22px; padding-top:14px; border-top:1px dashed var(--line); display:flex; justify-content:space-between; gap:14px; font-size:.76rem; color: var(--muted); }
        .barcode {
            font-family:'Libre Barcode 39', monospace;
            font-size:2.4rem; line-height:1; letter-spacing:.05em;
            color: var(--ink); text-align:center; margin-top:8px;
        }
        .barcode-fallback {
            font-family:'SF Mono','Menlo',monospace; font-size:.85rem;
            text-align:center; letter-spacing:.4em; color: var(--ink); margin-top:6px;
        }

        @media print {
            body { background:#fff; padding:0; }
            .toolbar { display:none; }
            .resi { border:none; box-shadow:none; max-width:none; padding:14px 18px; border-radius:0; }
            @page { size: A5; margin: 10mm; }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('petani.pesanan.index') }}" class="btn">
            <i class="ti ti-arrow-left"></i> Kembali
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <i class="ti ti-printer"></i> Cetak
        </button>
    </div>

    <div class="resi">
        <div class="resi-head">
            <div class="brand">
                <span class="brand-mark">SJ</span>
                <div>
                    <div class="brand-name">SPHT-JUJUN</div>
                    <div class="brand-sub">Marketplace Hasil Tani Lokal</div>
                </div>
            </div>
            <div class="resi-title">
                <h1>Resi Pengiriman</h1>
                <div class="code">{{ $order->code }}</div>
                <div class="date">{{ $order->created_at->translatedFormat('d F Y · H:i') }} WIB</div>
            </div>
        </div>

        <div class="addr-row">
            <div class="addr-card">
                <div class="lbl"><i class="ti ti-building-store"></i> Pengirim</div>
                <div class="name">{{ $petani->nama_usaha ?: $petani->name }}</div>
                <div class="text">
                    {{ $petani->alamat ?: '—' }}
                </div>
                <div class="meta">
                    @if ($petani->district_name || $petani->city_name)
                        {{ $petani->district_name }}@if ($petani->district_name && $petani->city_name), @endif{{ $petani->city_name }}@if (($petani->district_name || $petani->city_name) && $petani->province_name), @endif{{ $petani->province_name }}
                        <br>
                    @endif
                    @if ($petani->no_hp)
                        <i class="ti ti-phone" style="font-size:.7rem"></i> {{ $petani->no_hp }}
                    @endif
                </div>
            </div>

            <div class="addr-card">
                <div class="lbl"><i class="ti ti-map-pin"></i> Penerima</div>
                <div class="name">{{ $order->nama_penerima }}</div>
                <div class="text">
                    {{ $order->alamat_pengiriman ?: '—' }}
                </div>
                <div class="meta">
                    {{ $order->shipping_district_name }}, {{ $order->shipping_city_name }}, {{ $order->shipping_province_name }}<br>
                    <i class="ti ti-phone" style="font-size:.7rem"></i> {{ $order->no_hp_penerima }}
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:46%">Produk</th>
                    <th class="num" style="width:14%">Qty</th>
                    <th class="num" style="width:18%">Berat</th>
                    <th class="num" style="width:22%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ownItems as $i)
                    @php
                        $w = (float) ($i->weight_kg ?? $i->product?->weight_kg ?? 0);
                    @endphp
                    <tr>
                        <td>{{ $i->product?->nama ?? '[produk dihapus]' }}</td>
                        <td class="num">{{ $i->jumlah }}</td>
                        <td class="num">{{ rtrim(rtrim(number_format($w * $i->jumlah, 3, ',', '.'), '0'), ',') }} kg</td>
                        <td class="num">Rp {{ number_format($i->harga * $i->jumlah, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total ({{ $totalQty }} unit)</td>
                    <td class="num">{{ $totalQty }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($totalWeightKg, 3, ',', '.'), '0'), ',') }} kg</td>
                    <td class="num">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="info-row">
            <div class="info-box">
                <div class="lbl">Zona Pengiriman</div>
                <div class="val">{{ $shipping?->zone_label ?? '—' }}</div>
                @if ($shipping)
                    <div class="sub">{{ $shipping->total_weight_kg }} kg dihitung · Ongkir Rp {{ number_format((float) $shipping->shipping_cost, 0, ',', '.') }}</div>
                @endif
            </div>
            <div class="info-box">
                <div class="lbl">Status</div>
                <div class="val">{{ $order->status->label() }}</div>
                <div class="sub">Pembayaran: {{ ucfirst($order->metode_pembayaran ?? '—') }}</div>
            </div>
        </div>

        <div style="text-align:center; margin-top:18px;">
            <div class="barcode">*{{ $order->code }}*</div>
            <div class="barcode-fallback">{{ $order->code }}</div>
        </div>

        <div class="resi-foot">
            <div>
                Terima kasih telah berjualan di SPHT-JUJUN.<br>
                Pastikan paket dibungkus dengan baik sebelum diserahkan ke kurir.
            </div>
            <div style="text-align:right">
                Dicetak: {{ now()->translatedFormat('d M Y · H:i') }} WIB
            </div>
        </div>
    </div>

    <script>
        // Auto-print kalau halaman dibuka dengan ?print=1
        if (new URLSearchParams(location.search).get('print') === '1') {
            window.addEventListener('load', () => setTimeout(() => window.print(), 250));
        }
    </script>
</body>
</html>
