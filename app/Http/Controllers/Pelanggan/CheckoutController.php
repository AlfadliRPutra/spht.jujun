<?php

namespace App\Http\Controllers\Pelanggan;

use App\Enums\PaymentMethod;
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

        $addresses = $user->addresses()->get();

        if ($addresses->isEmpty()) {
            return redirect()->route('profile.edit')
                ->with('error', 'Tambahkan minimal satu alamat pengiriman sebelum melakukan checkout.');
        }

        // Address picker: ambil dari query, atau default user, atau alamat pertama.
        $selectedId = (int) $request->query('address_id', 0);
        $selected   = $selectedId
            ? $addresses->firstWhere('id', $selectedId)
            : null;
        $selected   ??= $addresses->firstWhere('is_default', true) ?? $addresses->first();

        $paymentMethod = PaymentMethod::fromInput($request->query('payment_method'));

        $checkout = $summary->build($cart, $user, $selected->snapshot(), $paymentMethod);

        return view('pages.pelanggan.checkout.index', [
            'cart'              => $cart,
            'checkout'          => $checkout,
            'addresses'         => $addresses,
            'selectedAddress'   => $selected,
            'paymentMethod'     => $paymentMethod,
            'paymentMethods'    => PaymentMethod::cases(),
        ]);
    }
}
