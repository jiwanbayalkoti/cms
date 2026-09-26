<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\Worker;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Support\CompanyContext;

class WorkerController extends Controller
{
    use ValidatesForms;

    public function __construct()
    {
        $this->middleware('admin');
    }

    public function validateWorker(Request $request, Worker $worker = null)
    {
        $rules = $this->rules($worker);

        return $this->validateForm($request, $rules);
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = Worker::with('project')->where('company_id', $companyId);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('wage_type')) {
            $query->where('wage_type', $request->wage_type);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $workers = $query->latest()->paginate(15)->withQueryString();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        return view('admin.workers.index', compact('workers', 'projects'));
    }

    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['projects' => $projects]);
        }

        return redirect()->route('admin.workers.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['is_active'] = $request->has('is_active');
        $validated['company_id'] = CompanyContext::getActiveCompanyId();

        if (($validated['wage_type'] ?? '') === 'daily') {
            $validated['monthly_salary'] = null;
        } else {
            $validated['daily_wage'] = null;
        }

        $worker = Worker::create($validated);
        $worker->load('project');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Worker created successfully.',
                'worker' => $this->workerPayload($worker),
            ]);
        }

        return redirect()->route('admin.workers.index')
            ->with('success', 'Worker created successfully.');
    }

    public function show(Worker $worker)
    {
        $this->assertCompany($worker);
        $worker->load('project');

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['worker' => $this->workerPayload($worker, true)]);
        }

        return redirect()->route('admin.workers.index');
    }

    public function edit(Worker $worker)
    {
        $this->assertCompany($worker);
        $companyId = CompanyContext::getActiveCompanyId();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'worker' => $this->workerPayload($worker, true),
                'projects' => $projects,
            ]);
        }

        return redirect()->route('admin.workers.index');
    }

    public function update(Request $request, Worker $worker)
    {
        $this->assertCompany($worker);
        $validated = $request->validate($this->rules($worker));
        $validated['is_active'] = $request->has('is_active');

        if (($validated['wage_type'] ?? '') === 'daily') {
            $validated['monthly_salary'] = null;
        } else {
            $validated['daily_wage'] = null;
        }

        $worker->update($validated);
        $worker->load('project');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Worker updated successfully.',
                'worker' => $this->workerPayload($worker),
            ]);
        }

        return redirect()->route('admin.workers.index')
            ->with('success', 'Worker updated successfully.');
    }

    public function destroy(Request $request, Worker $worker)
    {
        $this->assertCompany($worker);
        $worker->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Worker deleted successfully.',
            ]);
        }

        return redirect()->route('admin.workers.index')
            ->with('success', 'Worker deleted successfully.');
    }

    protected function rules(?Worker $worker = null): array
    {
        return [
            'project_id' => 'nullable|exists:projects,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'wage_type' => 'required|in:daily,monthly',
            'daily_wage' => 'nullable|required_if:wage_type,daily|numeric|min:0',
            'monthly_salary' => 'nullable|required_if:wage_type,monthly|numeric|min:0',
            'join_date' => 'nullable|date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }

    protected function assertCompany(Worker $worker): void
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if ($companyId && (int) $companyId !== 1 && (int) $worker->company_id !== (int) $companyId) {
            abort(403);
        }
    }

    protected function workerPayload(Worker $worker, bool $full = false): array
    {
        $data = [
            'id' => $worker->id,
            'name' => $worker->name,
            'phone' => $worker->phone,
            'email' => $worker->email,
            'wage_type' => $worker->wage_type,
            'daily_wage' => $worker->daily_wage,
            'monthly_salary' => $worker->monthly_salary,
            'wage_label' => $worker->wage_label,
            'project_id' => $worker->project_id,
            'project_name' => $worker->project?->name,
            'join_date' => $worker->join_date?->format('Y-m-d'),
            'join_date_formatted' => $worker->join_date?->format('M d, Y'),
            'is_active' => $worker->is_active,
        ];

        if ($full) {
            $data['address'] = $worker->address;
            $data['notes'] = $worker->notes;
        }

        return $data;
    }
}
