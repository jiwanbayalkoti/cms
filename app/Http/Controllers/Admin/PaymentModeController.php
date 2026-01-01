<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentModeRequest;
use App\Http\Requests\UpdatePaymentModeRequest;
use App\Models\PaymentMode;
use Illuminate\Http\Request;

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
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.payment-modes.index');
    }

    public function store(StorePaymentModeRequest $request)
    {
        $paymentMode = PaymentMode::create($request->validated());

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment mode created successfully.',
                'paymentMode' => [
                    'id' => $paymentMode->id,
                    'name' => $paymentMode->name,
                ],
            ]);
        }

        return redirect()->route('admin.payment-modes.index')
            ->with('success', 'Payment mode created successfully.');
    }

    public function edit(PaymentMode $payment_mode)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'paymentMode' => [
                    'id' => $payment_mode->id,
                    'name' => $payment_mode->name,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.payment-modes.index');
    }

    public function update(UpdatePaymentModeRequest $request, PaymentMode $payment_mode)
    {
        $payment_mode->update($request->validated());

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment mode updated successfully.',
                'paymentMode' => [
                    'id' => $payment_mode->id,
                    'name' => $payment_mode->name,
                ],
            ]);
        }

        return redirect()->route('admin.payment-modes.index')
            ->with('success', 'Payment mode updated successfully.');
    }

    public function destroy(Request $request, PaymentMode $payment_mode)
    {
        $payment_mode->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment mode deleted successfully.',
            ]);
        }

        return redirect()->route('admin.payment-modes.index')
            ->with('success', 'Payment mode deleted successfully.');
    }
}
