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
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.payment-types.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name',
            'code' => 'nullable|string|max:255|unique:payment_types,code',
        ]);
        $paymentType = PaymentType::create($data);
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment type added successfully.',
                'paymentType' => [
                    'id' => $paymentType->id,
                    'name' => $paymentType->name,
                    'code' => $paymentType->code,
                ],
            ]);
        }
        
        return redirect()->route('admin.payment-types.index')->with('success', 'Payment type added successfully.');
    }

    public function edit(PaymentType $paymentType)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'paymentType' => [
                    'id' => $paymentType->id,
                    'name' => $paymentType->name,
                    'code' => $paymentType->code,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.payment-types.index');
    }

    public function update(Request $request, PaymentType $paymentType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:payment_types,name,' . $paymentType->id,
            'code' => 'nullable|string|max:255|unique:payment_types,code,' . $paymentType->id,
        ]);
        $paymentType->update($data);
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment type updated successfully.',
                'paymentType' => [
                    'id' => $paymentType->id,
                    'name' => $paymentType->name,
                    'code' => $paymentType->code,
                ],
            ]);
        }
        
        return redirect()->route('admin.payment-types.index')->with('success', 'Payment type updated successfully.');
    }

    public function destroy(Request $request, PaymentType $paymentType)
    {
        $paymentType->delete();
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment type deleted successfully.',
            ]);
        }
        
        return redirect()->route('admin.payment-types.index')->with('success', 'Payment type deleted successfully.');
    }
}

