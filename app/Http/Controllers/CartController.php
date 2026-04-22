<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
}
