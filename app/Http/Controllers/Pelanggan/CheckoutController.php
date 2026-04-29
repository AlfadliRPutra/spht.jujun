<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Services\CheckoutSummaryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request, CheckoutSummaryService $summary): View|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $cart = $user->cart()->with('items.product.petani')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('pelanggan.keranjang.index')
                ->with('error', 'Keranjang kosong.');
        }

        // Bangun ringkasan ongkir per toko (validasi alamat + zona dilakukan di service).
        $checkout = $summary->build($cart, $user);

        return view('pages.pelanggan.checkout.index', [
            'cart'     => $cart,
            'checkout' => $checkout,
        ]);
    }
}
