@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    /** @var int $petaniId */
    use App\Enums\OrderStatus;
    $title  = 'Pesanan Masuk';
    $active = 'petani.pesanan';
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Pesanan dari Pelanggan ({{ $items->total() }})</h3>
        </div>

        <x-table-toolbar
            :action="route('petani.pesanan.index')"
            placeholder="Cari #order atau nama pelanggan..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage">
            <x-slot name="filters">
                <div>
                    <label class="form-label small text-secondary mb-1">Status</label>
                    <select name="status" class="form-select" style="min-width:170px">
                        <option value="">Semua</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </x-slot>
        </x-table-toolbar>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>#Order</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th>Tanggal</th>
                        <th class="text-end">Subtotal</th>
                        <th>Status</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $order)
                        @php
                            $ownItems = $order->items->filter(fn ($i) => $i->product?->user_id === $petaniId);
                            $subtotal = $ownItems->sum(fn ($i) => $i->harga * $i->jumlah);
                        @endphp
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td class="text-truncate" style="max-width:260px">{{ $ownItems->pluck('product.nama')->implode(', ') }}</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span></td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#detail-order-{{ $order->id }}">
                                    <i class="ti ti-eye"></i>
                                </button>
                                @if (in_array($order->status, [OrderStatus::Dibayar, OrderStatus::Dikirim, OrderStatus::Selesai], true))
                                    <a href="{{ route('petani.pesanan.resi', $order) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-success" title="Cetak resi pengiriman">
                                        <i class="ti ti-printer"></i>
                                    </a>
                                @endif
                                @if ($order->status === OrderStatus::Dibayar)
                                    <form action="{{ route('petani.pesanan.ship', $order) }}" method="POST" class="d-inline"
                                          data-confirm="Pesanan #{{ $order->id }} akan ditandai sebagai sudah dikirim. Lanjutkan?"
                                          data-confirm-title="Kirim Pesanan?"
                                          data-confirm-icon="info"
                                          data-confirm-button="Ya, tandai dikirim"
                                          data-confirm-color="#0ea5e9">
                                        @csrf
                                        <button class="btn btn-sm btn-primary"><i class="ti ti-truck-delivery me-1"></i> Kirim</button>
                                    </form>
                                @elseif ($order->status === OrderStatus::Dikirim)
                                    <form action="{{ route('petani.pesanan.complete', $order) }}" method="POST" class="d-inline"
                                          data-confirm="Tandai pesanan #{{ $order->id }} sebagai selesai? Stok produk akan dihitung sebagai terjual."
                                          data-confirm-title="Selesaikan Pesanan?"
                                          data-confirm-icon="success"
                                          data-confirm-button="Ya, selesaikan">
                                        @csrf
                                        <button class="btn btn-sm btn-success"><i class="ti ti-circle-check me-1"></i> Selesai</button>
                                    </form>
                                @endif

                                @if (! in_array($order->status, [OrderStatus::Selesai, OrderStatus::Batal], true))
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#cancel-{{ $order->id }}">
                                        <i class="ti ti-x"></i>
                                    </button>
                                    <div class="modal modal-blur fade" id="cancel-{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <form method="POST" action="{{ route('petani.pesanan.cancel', $order) }}" class="modal-content">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Batalkan Pesanan #{{ $order->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label class="form-label">Alasan pembatalan (wajib)</label>
                                                    <textarea name="cancel_reason" rows="3" class="form-control"
                                                              placeholder="mis. Stok habis, tidak sesuai pesanan, dll." required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link" data-bs-dismiss="modal">Tutup</button>
                                                    <button type="submit" class="btn btn-danger">Batalkan Pesanan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-secondary small">
                Menampilkan <strong>{{ $items->firstItem() ?? 0 }}</strong> - <strong>{{ $items->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $items->total() }}</strong>
            </div>
            {{ $items->links() }}
        </div>
    </div>

    @foreach ($items as $order)
        @php
            $ownItems = $order->items->filter(fn ($i) => $i->product?->user_id === $petaniId);
            $subtotal = $ownItems->sum(fn ($i) => $i->harga * $i->jumlah);
        @endphp
        <div class="modal modal-blur fade" id="detail-order-{{ $order->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0">Detail Pesanan</h5>
                            <div class="text-secondary small">{{ $order->code }} &middot; {{ $order->created_at->format('d M Y · H:i') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge {{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                            @if ($order->metode_pembayaran)
                                <span class="badge bg-secondary-lt">
                                    <i class="ti ti-{{ $order->metode_pembayaran->icon() }} me-1"></i>{{ $order->metode_pembayaran->label() }}
                                </span>
                            @endif
                        </div>

                        <h6 class="text-uppercase text-secondary small fw-bold mb-2">Produk Anda di pesanan ini</h6>
                        <div class="border rounded mb-3">
                            @foreach ($ownItems as $i)
                                <div class="d-flex align-items-center gap-3 p-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                    <img src="{{ $i->product?->image_url ?? asset('img/placeholder.png') }}"
                                         alt="" style="width:44px;height:44px;object-fit:cover;border-radius:.5rem;background:#f6f8fa"
                                         loading="lazy">
                                    <div class="flex-fill">
                                        <div class="fw-medium">
                                            @if ($i->product)
                                                {{ $i->product->nama }}
                                            @else
                                                <span class="text-secondary fst-italic">[produk dihapus]</span>
                                            @endif
                                        </div>
                                        <div class="text-secondary small">{{ $i->jumlah }} × Rp {{ number_format($i->harga, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="fw-semibold">Rp {{ number_format($i->harga * $i->jumlah, 0, ',', '.') }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <h6 class="text-uppercase text-secondary small fw-bold mb-2">Pelanggan</h6>
                                <div class="border rounded p-3 small">
                                    <div class="fw-medium">{{ $order->user->name }}</div>
                                    <div class="text-secondary">{{ $order->user->no_hp ?? '—' }}</div>
                                    <div class="text-secondary mt-1">{{ $order->user->alamat ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <h6 class="text-uppercase text-secondary small fw-bold mb-2">Ringkasan Bagian Anda</h6>
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Subtotal Produk</span>
                                        <span class="text-success">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="text-secondary small mt-1">{{ $ownItems->sum('jumlah') }} unit terjual dari Anda</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Tutup</button>
                        @if (in_array($order->status, [OrderStatus::Dibayar, OrderStatus::Dikirim, OrderStatus::Selesai], true))
                            <a href="{{ route('petani.pesanan.resi', $order) }}" target="_blank" rel="noopener" class="btn btn-outline-success">
                                <i class="ti ti-printer me-1"></i> Cetak Resi
                            </a>
                        @endif
                        @if ($order->status === OrderStatus::Dibayar)
                            <form action="{{ route('petani.pesanan.ship', $order) }}" method="POST" class="m-0"
                                  data-confirm="Pesanan #{{ $order->id }} akan ditandai sebagai sudah dikirim. Lanjutkan?"
                                  data-confirm-title="Kirim Pesanan?"
                                  data-confirm-icon="info"
                                  data-confirm-button="Ya, tandai dikirim"
                                  data-confirm-color="#0ea5e9">
                                @csrf
                                <button class="btn btn-primary"><i class="ti ti-truck-delivery me-1"></i> Tandai Dikirim</button>
                            </form>
                        @elseif ($order->status === OrderStatus::Dikirim)
                            <form action="{{ route('petani.pesanan.complete', $order) }}" method="POST" class="m-0"
                                  data-confirm="Tandai pesanan #{{ $order->id }} sebagai selesai? Stok produk akan dihitung sebagai terjual."
                                  data-confirm-title="Selesaikan Pesanan?"
                                  data-confirm-icon="success"
                                  data-confirm-button="Ya, selesaikan">
                                @csrf
                                <button class="btn btn-success"><i class="ti ti-circle-check me-1"></i> Selesaikan</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</x-layouts.app>
