<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchasedByRequest;
use App\Http\Requests\UpdatePurchasedByRequest;
use App\Models\PurchasedBy;

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
        return view('admin.purchased_bies.create');
    }

    public function store(StorePurchasedByRequest $request)
    {
        PurchasedBy::create($request->validated());

        return redirect()->route('admin.purchased-bies.index')
            ->with('success', 'Purchased By person created successfully.');
    }

    public function edit(PurchasedBy $purchased_by)
    {
        return view('admin.purchased_bies.edit', ['purchasedBy' => $purchased_by]);
    }

    public function update(UpdatePurchasedByRequest $request, PurchasedBy $purchased_by)
    {
        $purchased_by->update($request->validated());

        return redirect()->route('admin.purchased-bies.index')
            ->with('success', 'Purchased By person updated successfully.');
    }

    public function destroy(PurchasedBy $purchased_by)
    {
        $purchased_by->delete();

        return redirect()->route('admin.purchased-bies.index')
            ->with('success', 'Purchased By person deleted successfully.');
    }
}
