<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $paymentTypes = PaymentType::orderBy('name')->get();
        return view('admin.payment_types.index', compact('paymentTypes'));
    }

    public function create()
    {
        return view('admin.payment_types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name',
            'code' => 'nullable|string|max:255|unique:payment_types,code',
        ]);
        PaymentType::create($data);
        return redirect()->route('admin.payment-types.index')->with('success', 'Payment type added successfully.');
    }

    public function edit(PaymentType $paymentType)
    {
        return view('admin.payment_types.edit', compact('paymentType'));
    }

    public function update(Request $request, PaymentType $paymentType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name,' . $paymentType->id,
            'code' => 'nullable|string|max:255|unique:payment_types,code,' . $paymentType->id,
        ]);
        $paymentType->update($data);
        return redirect()->route('admin.payment-types.index')->with('success', 'Payment type updated successfully.');
    }

    public function destroy(PaymentType $paymentType)
    {
        $paymentType->delete();
        return redirect()->route('admin.payment-types.index')->with('success', 'Payment type deleted successfully.');
    }
}

