<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BoqWorkExport;
use App\Http\Controllers\Controller;
use App\Models\BoqType;
use App\Models\BoqWork;
use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;

class BoqTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $company = Company::find($companyId);
        $works = $this->getWorksForCompany($companyId);
        return view('admin.boq.work-index', compact('works', 'company'));
    }

    public function exportExcel()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $company = Company::find($companyId);
        $works = $this->getWorksForCompany($companyId);
        if ($works->isEmpty()) {
            return redirect()->route('admin.boq.work-index')->with('info', 'No works to export.');
        }
        $filename = 'boq_work_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new BoqWorkExport($company, $works), $filename);
    }

    public function exportPdf()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $company = Company::find($companyId);
        $works = $this->getWorksForCompany($companyId);
        if ($works->isEmpty()) {
            return redirect()->route('admin.boq.work-index')->with('info', 'No works to export.');
        }
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('admin.boq.work-pdf', compact('company', 'works'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('boq_work_' . now()->format('Y-m-d_His') . '.pdf');
    }

    protected function getWorksForCompany(int $companyId)
    {
        return BoqWork::where('company_id', $companyId)
            ->roots()
            ->with(['items', 'children' => fn ($q) => $q->with('items')->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function updateCompanyBillDetails(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $company = Company::where('id', $companyId)->firstOrFail();
        $data = $request->validate([
            'client' => 'nullable|string|max:255',
            'project' => 'nullable|string|max:255',
            'contract_no' => 'nullable|string|max:255',
            'bill_date' => 'nullable|date',
        ]);
        $company->update($data);
        return redirect()->route('admin.boq.work-index')->with('success', 'Details saved. They will appear on Measurement Book and Bill Statement.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $data['company_id'] = CompanyContext::getActiveCompanyId();
        BoqType::create($data);
        return redirect()->route('admin.boq.work-index')->with('success', 'Type added successfully.');
    }

    public function update(Request $request, BoqType $boq_type)
    {
        if ($boq_type->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $boq_type->update($data);
        return redirect()->route('admin.boq.work-index')->with('success', 'Type updated successfully.');
    }

    public function destroy(BoqType $boq_type)
    {
        if ($boq_type->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $boq_type->delete();
        return redirect()->route('admin.boq.work-index')->with('success', 'Type deleted successfully.');
    }
}
