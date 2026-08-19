<?php

namespace App\Http\Controllers\Admin;

use App\Exports\VehicleExport;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Vehicle;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class VehicleController extends Controller
{
    use ValidatesForms;

    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = Vehicle::with(['project', 'supplier', 'driverStaff'])
            ->where('company_id', $companyId);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        if ($request->filled('ownership')) {
            $query->where('ownership', $request->ownership);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($inner) use ($search) {
                $inner->where('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('driver_name', 'like', "%{$search}%")
                    ->orWhere('driver_contact', 'like', "%{$search}%")
                    ->orWhere('insurance_policy_no', 'like', "%{$search}%");
            });
        }

        $vehicles = $query->latest()->paginate(15)->withQueryString();

        $expiredCount = Vehicle::where('company_id', $companyId)->withExpiredDocuments()->count();
        $expiringCount = Vehicle::where('company_id', $companyId)->withExpiringDocuments()->count();

        return view('admin.vehicles.index', array_merge(
            compact('vehicles', 'expiredCount', 'expiringCount'),
            $this->formOptions($companyId)
        ));
    }

    public function documents(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $filter = $request->get('filter', 'attention');

        $query = Vehicle::with(['project', 'supplier'])
            ->where('company_id', $companyId);

        if ($filter === 'expired') {
            $query->withExpiredDocuments();
        } elseif ($filter === 'expiring') {
            $query->withExpiringDocuments();
        } elseif ($filter === 'attention') {
            $ids = Vehicle::where('company_id', $companyId)
                ->withExpiredDocuments()
                ->pluck('id')
                ->merge(
                    Vehicle::where('company_id', $companyId)
                        ->withExpiringDocuments()
                        ->pluck('id')
                )
                ->unique()
                ->values();
            $query->whereIn('id', $ids);
        }

        $vehicles = $query->orderBy('vehicle_number')->get();

        return view('admin.vehicles.documents', [
            'vehicles' => $vehicles,
            'filter' => $filter,
            'documentDefinitions' => Vehicle::documentDefinitions(),
        ]);
    }

    public function validateVehicle(Request $request, Vehicle $vehicle = null)
    {
        return $this->validateForm($request, $this->rules($request, $vehicle));
    }

    public function create()
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($this->formOptions(CompanyContext::getActiveCompanyId()));
        }

        return redirect()->route('admin.vehicles.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules($request));
        $validated['company_id'] = CompanyContext::getActiveCompanyId();
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        $validated['supplier_id'] = $validated['ownership'] === 'hired' ? ($validated['supplier_id'] ?? null) : null;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('vehicles', 'public');
        } else {
            unset($validated['photo']);
        }

        $vehicle = Vehicle::create($validated);
        $vehicle->load(['project', 'supplier', 'driverStaff']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle created successfully.',
                'vehicle' => $this->serializeVehicle($vehicle),
            ]);
        }

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle created successfully.');
    }

    public function show(Vehicle $vehicle)
    {
        $this->assertCompanyAccess($vehicle);
        $vehicle->load(['project', 'supplier', 'driverStaff']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'vehicle' => $this->serializeVehicle($vehicle, true),
            ]);
        }

        return redirect()->route('admin.vehicles.index');
    }

    public function edit(Vehicle $vehicle)
    {
        $this->assertCompanyAccess($vehicle);
        $vehicle->load(['project', 'supplier', 'driverStaff']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(array_merge(
                ['vehicle' => $this->serializeVehicle($vehicle, true)],
                $this->formOptions(CompanyContext::getActiveCompanyId())
            ));
        }

        return redirect()->route('admin.vehicles.index');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->assertCompanyAccess($vehicle);

        $validated = $request->validate($this->rules($request, $vehicle));
        $validated['updated_by'] = auth()->id();
        $validated['supplier_id'] = $validated['ownership'] === 'hired' ? ($validated['supplier_id'] ?? null) : null;

        if ($request->hasFile('photo')) {
            if ($vehicle->photo && Storage::disk('public')->exists($vehicle->photo)) {
                Storage::disk('public')->delete($vehicle->photo);
            }
            $validated['photo'] = $request->file('photo')->store('vehicles', 'public');
        } else {
            unset($validated['photo']);
        }

        $vehicle->update($validated);
        $vehicle->load(['project', 'supplier', 'driverStaff']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated successfully.',
                'vehicle' => $this->serializeVehicle($vehicle),
            ]);
        }

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Request $request, Vehicle $vehicle)
    {
        $this->assertCompanyAccess($vehicle);

        if ($vehicle->photo && Storage::disk('public')->exists($vehicle->photo)) {
            Storage::disk('public')->delete($vehicle->photo);
        }

        $vehicle->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle deleted successfully.',
            ]);
        }

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }

    public function export(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = Vehicle::where('company_id', $companyId);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        if ($request->filled('ownership')) {
            $query->where('ownership', $request->ownership);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($inner) use ($search) {
                $inner->where('vehicle_number', 'like', "%{$search}%")
                    ->orWhere('driver_name', 'like', "%{$search}%");
            });
        }

        $filename = 'vehicles-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new VehicleExport($query), $filename);
    }

    protected function rules(Request $request, Vehicle $vehicle = null): array
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $types = array_keys(Vehicle::getVehicleTypes());

        return [
            'project_id' => 'nullable|exists:projects,id',
            'vehicle_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('vehicles', 'vehicle_number')
                    ->where(fn ($q) => $q->where('company_id', $companyId))
                    ->ignore($vehicle?->id),
            ],
            'vehicle_type' => ['required', Rule::in($types)],
            'ownership' => ['required', Rule::in(array_keys(Vehicle::getOwnershipOptions()))],
            'supplier_id' => [
                Rule::requiredIf($request->input('ownership') === 'hired'),
                'nullable',
                'exists:suppliers,id',
            ],
            'driver_staff_id' => 'nullable|exists:staff,id',
            'driver_name' => 'nullable|string|max:255',
            'driver_contact' => 'nullable|string|max:20',
            'status' => ['required', Rule::in(array_keys(Vehicle::getStatusOptions()))],
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'notes' => 'nullable|string',
            'insurance_policy_no' => 'nullable|string|max:100',
            'registration_expiry' => 'nullable|date',
            'insurance_expiry' => 'nullable|date',
            'fitness_expiry' => 'nullable|date',
            'route_permit_expiry' => 'nullable|date',
            'tax_token_expiry' => 'nullable|date',
            'puc_expiry' => 'nullable|date',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date|after_or_equal:last_service_date',
        ];
    }

    protected function formOptions($companyId): array
    {
        return [
            'projects' => Project::where('company_id', $companyId)
                ->where('status', '!=', 'cancelled')
                ->orderBy('name')
                ->get(['id', 'name']),
            'suppliers' => Supplier::where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'staffMembers' => Staff::where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'phone']),
            'vehicleTypes' => Vehicle::getVehicleTypes(),
            'ownershipOptions' => Vehicle::getOwnershipOptions(),
            'statusOptions' => Vehicle::getStatusOptions(),
        ];
    }

    protected function serializeVehicle(Vehicle $vehicle, bool $detailed = false): array
    {
        $data = [
            'id' => $vehicle->id,
            'vehicle_number' => $vehicle->vehicle_number,
            'vehicle_type' => $vehicle->vehicle_type,
            'type_label' => $vehicle->type_label,
            'ownership' => $vehicle->ownership,
            'ownership_label' => $vehicle->ownership_label,
            'status' => $vehicle->status,
            'status_label' => $vehicle->status_label,
            'project_id' => $vehicle->project_id,
            'project_name' => $vehicle->project?->name,
            'supplier_id' => $vehicle->supplier_id,
            'supplier_name' => $vehicle->supplier?->name,
            'driver_staff_id' => $vehicle->driver_staff_id,
            'driver_name' => $vehicle->driverStaff?->name ?? $vehicle->driver_name,
            'driver_contact' => $vehicle->driver_contact,
            'photo_url' => $vehicle->photo_url,
            'worst_document_status' => $vehicle->worst_document_status,
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'driver_name_raw' => $vehicle->driver_name,
                'notes' => $vehicle->notes,
                'insurance_policy_no' => $vehicle->insurance_policy_no,
                'registration_expiry' => optional($vehicle->registration_expiry)->format('Y-m-d'),
                'insurance_expiry' => optional($vehicle->insurance_expiry)->format('Y-m-d'),
                'fitness_expiry' => optional($vehicle->fitness_expiry)->format('Y-m-d'),
                'route_permit_expiry' => optional($vehicle->route_permit_expiry)->format('Y-m-d'),
                'tax_token_expiry' => optional($vehicle->tax_token_expiry)->format('Y-m-d'),
                'puc_expiry' => optional($vehicle->puc_expiry)->format('Y-m-d'),
                'last_service_date' => optional($vehicle->last_service_date)->format('Y-m-d'),
                'next_service_date' => optional($vehicle->next_service_date)->format('Y-m-d'),
                'documents' => $vehicle->documentStatuses(),
            ]);
        }

        return $data;
    }

    protected function assertCompanyAccess(Vehicle $vehicle): void
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if ($companyId && (int) $companyId !== 1 && (int) $vehicle->company_id !== (int) $companyId) {
            abort(403);
        }
    }
}
