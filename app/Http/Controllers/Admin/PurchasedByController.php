<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchasedByRequest;
use App\Http\Requests\UpdatePurchasedByRequest;
use App\Models\PurchasedBy;
use Illuminate\Http\Request;

class PurchasedByController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $purchasedBies = PurchasedBy::orderBy('name')->paginate(15);

        return view('admin.purchased_bies.index', compact('purchasedBies'));
    }

    public function create()
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.purchased-bies.index');
    }

    public function store(StorePurchasedByRequest $request)
    {
        $purchasedBy = PurchasedBy::create($request->validated());

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchased By person created successfully.',
                'purchasedBy' => [
                    'id' => $purchasedBy->id,
                    'name' => $purchasedBy->name,
                    'contact' => $purchasedBy->contact,
                    'email' => $purchasedBy->email,
                    'is_active' => $purchasedBy->is_active,
                ],
            ]);
        }

        return redirect()->route('admin.purchased-bies.index')
            ->with('success', 'Purchased By person created successfully.');
    }

    public function edit(PurchasedBy $purchased_by)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'purchasedBy' => [
                    'id' => $purchased_by->id,
                    'name' => $purchased_by->name,
                    'contact' => $purchased_by->contact,
                    'email' => $purchased_by->email,
                    'is_active' => $purchased_by->is_active,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.purchased-bies.index');
    }

    public function update(UpdatePurchasedByRequest $request, PurchasedBy $purchased_by)
    {
        $purchased_by->update($request->validated());

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchased By person updated successfully.',
                'purchasedBy' => [
                    'id' => $purchased_by->id,
                    'name' => $purchased_by->name,
                    'contact' => $purchased_by->contact,
                    'email' => $purchased_by->email,
                    'is_active' => $purchased_by->is_active,
                ],
            ]);
        }

        return redirect()->route('admin.purchased-bies.index')
            ->with('success', 'Purchased By person updated successfully.');
    }

    public function destroy(Request $request, PurchasedBy $purchased_by)
    {
        $purchased_by->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchased By person deleted successfully.',
            ]);
        }

        return redirect()->route('admin.purchased-bies.index')
            ->with('success', 'Purchased By person deleted successfully.');
    }
}
