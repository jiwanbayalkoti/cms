<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoqItem;
use App\Models\BoqType;
use App\Models\BoqWork;
use App\Support\CompanyContext;
use Illuminate\Http\Request;

class BoqWorkController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $types = BoqType::where('company_id', $companyId)->with('works')->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.boq.boq-index', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:boq_works,id',
        ]);
        $companyId = CompanyContext::getActiveCompanyId();
        $data['company_id'] = $companyId;
        if (!empty($data['parent_id'])) {
            $parent = BoqWork::where('company_id', $companyId)->find($data['parent_id']);
            if ($parent) {
                $data['boq_type_id'] = $parent->boq_type_id;
            }
        }
        BoqWork::create($data);
        $message = !empty($data['parent_id']) ? 'Subwork added successfully.' : 'Work added successfully.';
        return redirect()->route('admin.boq.work-index')->with('success', $message);
    }

    public function show(BoqWork $boq_work)
    {
        if ($boq_work->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $boq_work->load(['type', 'items', 'parent', 'children']);
        return view('admin.boq.work-items', compact('boq_work'));
    }

    public function edit(BoqWork $boq_work)
    {
        if ($boq_work->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        return view('admin.boq.work-edit', compact('boq_work'));
    }

    public function update(Request $request, BoqWork $boq_work)
    {
        if ($boq_work->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $boq_work->update($data);
        return redirect()->route('admin.boq.work-index')->with('success', 'Work updated successfully.');
    }

    public function destroy(BoqWork $boq_work)
    {
        if ($boq_work->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $boq_work->delete();
        return redirect()->route('admin.boq.work-index')->with('success', 'Work deleted successfully.');
    }

    public function updateItems(Request $request, BoqWork $boq_work)
    {
        if ($boq_work->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $items = $request->input('items', []);
        $ids = [];
        foreach ($items as $index => $row) {
            $qty = (float) ($row['qty'] ?? 0);
            $rate = (float) ($row['rate'] ?? 0);
            $amount = $qty * $rate;
            $rateInWords = BoqItem::rateToWords($rate);
            $data = [
                'item_description' => $row['item_description'] ?? '',
                'unit' => $row['unit'] ?? '',
                'qty' => $qty,
                'rate' => $rate,
                'rate_in_words' => $rateInWords,
                'amount' => $amount,
                'sort_order' => $index,
            ];
            if (!empty($row['id'])) {
                $item = BoqItem::where('boq_work_id', $boq_work->id)->find($row['id']);
                if ($item) {
                    $item->update($data);
                    $ids[] = $item->id;
                    continue;
                }
            }
            $item = $boq_work->items()->create($data);
            $ids[] = $item->id;
        }
        $boq_work->items()->whereNotIn('id', $ids)->delete();
        return redirect()->route('admin.boq.works.show', $boq_work)->with('success', 'Items saved.');
    }
}
