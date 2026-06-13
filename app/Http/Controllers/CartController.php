<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'jumlah'     => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        if ($data['jumlah'] > $product->stok) {
            return back()->with('error', 'Jumlah melebihi stok tersedia.');
        }

        $cart = $request->user()->cart()->firstOrCreate([]);

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $total = $item->jumlah + $data['jumlah'];
            if ($total > $product->stok) {
                return back()->with('error', 'Total jumlah melebihi stok tersedia.');
            }
            $item->update(['jumlah' => $total]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'jumlah'     => $data['jumlah'],
            ]);
        }

        return redirect()
            ->route('pelanggan.keranjang.index')
            ->with('success', $product->nama.' ditambahkan ke keranjang.');
    }

    /**
     * Ubah jumlah satu item keranjang. Dipanggil dari input jumlah di halaman
     * keranjang. Jumlah di-clamp ke stok tersedia agar tidak over-order.
     */
    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeItem($item, $request->user());

        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1'],
        ]);

        $product = $item->product;
        if (! $product || ! $product->is_active) {
            $item->delete();
            return back()->with('error', 'Produk sudah tidak tersedia dan dihapus dari keranjang.');
        }

        if ($product->stok < 1) {
            $item->delete();
            return back()->with('error', 'Stok '.$product->nama.' habis — dihapus dari keranjang.');
        }

        $jumlah = min($data['jumlah'], $product->stok);
        $item->update(['jumlah' => $jumlah]);

        $message = $jumlah < $data['jumlah']
            ? 'Jumlah disesuaikan dengan stok tersedia ('.$jumlah.').'
            : 'Jumlah diperbarui.';

        return back()->with('success', $message);
    }

    /**
     * Hapus satu item dari keranjang.
     */
    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeItem($item, $request->user());

        $name = $item->product?->nama ?? 'Produk';
        $item->delete();

        return back()->with('success', $name.' dihapus dari keranjang.');
    }

    /**
     * Pastikan item keranjang milik user yang sedang login.
     */
    private function authorizeItem(CartItem $item, User $user): void
    {
        abort_unless($item->cart && $item->cart->user_id === $user->id, 403);
    }
}
