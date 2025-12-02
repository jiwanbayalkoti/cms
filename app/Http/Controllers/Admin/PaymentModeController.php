<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentModeRequest;
use App\Http\Requests\UpdatePaymentModeRequest;
use App\Models\PaymentMode;

class PaymentModeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $paymentModes = PaymentMode::orderBy('name')->paginate(15);

        return view('admin.payment_modes.index', compact('paymentModes'));
    }

    public function create()
    {
        return view('admin.payment_modes.create');
    }

    public function store(StorePaymentModeRequest $request)
    {
        PaymentMode::create($request->validated());

        return redirect()->route('admin.payment-modes.index')
            ->with('success', 'Payment mode created successfully.');
    }

    public function edit(PaymentMode $payment_mode)
    {
        return view('admin.payment_modes.edit', ['paymentMode' => $payment_mode]);
    }

    public function update(UpdatePaymentModeRequest $request, PaymentMode $payment_mode)
    {
        $payment_mode->update($request->validated());

        return redirect()->route('admin.payment-modes.index')
            ->with('success', 'Payment mode updated successfully.');
    }

    public function destroy(PaymentMode $payment_mode)
    {
        $payment_mode->delete();

        return redirect()->route('admin.payment-modes.index')
            ->with('success', 'Payment mode deleted successfully.');
    }
}
