<?php

namespace App\Http\Controllers\Admin;

use App\Exports\VehicleExport;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Models\VehicleDocumentRenewal;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function vehicleDocuments(Vehicle $vehicle)
    {
        $this->assertCompanyAccess($vehicle);

        $vehicle->load(['project', 'supplier']);
        $documents = $vehicle->documents()->with('uploader')->get();
        $renewals = $vehicle->documentRenewals()->with(['renewer', 'document'])->limit(100)->get();
        $types = Vehicle::documentTypeDefinitions();

        return view('admin.vehicles.vehicle-documents', compact('vehicle', 'documents', 'renewals', 'types'));
    }

    public function documentsUpload(Request $request, Vehicle $vehicle)
    {
        $this->assertCompanyAccess($vehicle);

        $typeKeys = array_keys(Vehicle::documentTypeDefinitions());

        try {
            $validated = $request->validate([
                'document_type' => ['required', Rule::in($typeKeys)],
                'label' => 'required|string|max:255',
                'file' => 'required|file|max:20480|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx',
                'expiry_date' => 'nullable|date',
                'service_date' => 'nullable|date',
                'policy_number' => 'nullable|string|max:100',
                'cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $messages = collect($e->errors())->flatten()->unique()->values()->all();

            return response()->json([
                'success' => false,
                'message' => implode(' ', $messages) ?: 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($validated['document_type'] === 'service' && empty($validated['expiry_date']) && empty($validated['service_date'])) {
            return response()->json([
                'success' => false,
                'message' => 'Service records need at least a service date or next service date.',
            ], 422);
        }

        try {
            $document = DB::transaction(function () use ($request, $vehicle, $validated) {
                $typeDef = Vehicle::documentTypeDefinitions()[$validated['document_type']];
                $previousExpiry = !empty($typeDef['expiry_field'])
                    ? $vehicle->{$typeDef['expiry_field']}
                    : null;
                $previousPolicy = !empty($typeDef['policy_field'])
                    ? $vehicle->{$typeDef['policy_field']}
                    : null;

                VehicleDocument::where('vehicle_id', $vehicle->id)
                    ->where('document_type', $validated['document_type'])
                    ->where('is_current', true)
                    ->update(['is_current' => false]);

                $file = $request->file('file');
                $path = $file->store("vehicle_documents/{$vehicle->company_id}/{$vehicle->id}", 'public');

                $doc = $vehicle->documents()->create([
                    'company_id' => $vehicle->company_id,
                    'uploaded_by' => auth()->id(),
                    'document_type' => $validated['document_type'],
                    'label' => $validated['label'],
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'expiry_date' => $validated['expiry_date'] ?? null,
                    'service_date' => $validated['service_date'] ?? null,
                    'policy_number' => $validated['policy_number'] ?? null,
                    'cost' => $validated['cost'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'is_current' => true,
                ]);

                $updates = [];
                if (!empty($typeDef['expiry_field']) && !empty($validated['expiry_date'])) {
                    $updates[$typeDef['expiry_field']] = $validated['expiry_date'];
                }
                if (!empty($typeDef['policy_field']) && !empty($validated['policy_number'])) {
                    $updates[$typeDef['policy_field']] = $validated['policy_number'];
                }
                if ($validated['document_type'] === 'service' && !empty($validated['service_date'])) {
                    $updates['last_service_date'] = $validated['service_date'];
                }
                if ($updates !== []) {
                    $vehicle->update($updates);
                }

                VehicleDocumentRenewal::create([
                    'company_id' => $vehicle->company_id,
                    'vehicle_id' => $vehicle->id,
                    'vehicle_document_id' => $doc->id,
                    'document_type' => $validated['document_type'],
                    'previous_expiry' => $previousExpiry,
                    'new_expiry' => $validated['expiry_date'] ?? null,
                    'service_date' => $validated['service_date'] ?? null,
                    'previous_policy_number' => $previousPolicy,
                    'new_policy_number' => $validated['policy_number'] ?? null,
                    'cost' => $validated['cost'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'renewed_by' => auth()->id(),
                    'renewed_at' => now(),
                ]);

                return $doc;
            });

            $document->load('uploader');

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded and vehicle expiry updated.',
                'document' => $this->vehicleDocumentJson($document),
            ]);
        } catch (\Throwable $e) {
            Log::error('Vehicle document upload failed: ' . $e->getMessage(), [
                'vehicle_id' => $vehicle->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Could not save the file. Please try again.',
            ], 500);
        }
    }

    public function documentsDestroy(Vehicle $vehicle, VehicleDocument $document)
    {
        $this->assertCompanyAccess($vehicle);

        if ((int) $document->vehicle_id !== (int) $vehicle->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['success' => true, 'message' => 'Document deleted.']);
    }

    public function documentsDownload(Vehicle $vehicle, VehicleDocument $document)
    {
        $this->assertCompanyAccess($vehicle);

        if ((int) $document->vehicle_id !== (int) $vehicle->id) {
            abort(403);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return $disk->download($document->file_path, $document->original_name);
    }

    protected function vehicleDocumentJson(VehicleDocument $doc): array
    {
        return [
            'id' => $doc->id,
            'document_type' => $doc->document_type,
            'type_label' => $doc->getTypeLabel(),
            'label' => $doc->label,
            'original_name' => $doc->original_name,
            'file_size' => $doc->getFileSizeFormatted(),
            'mime_type' => $doc->mime_type,
            'is_image' => $doc->isImage(),
            'is_pdf' => $doc->isPdf(),
            'url' => $doc->getUrl(),
            'expiry_date' => $doc->expiry_date?->format('Y-m-d'),
            'service_date' => $doc->service_date?->format('Y-m-d'),
            'policy_number' => $doc->policy_number,
            'cost' => $doc->cost,
            'notes' => $doc->notes,
            'is_current' => $doc->is_current,
            'uploaded_by' => $doc->uploader?->name,
            'created_at' => $doc->created_at?->format('Y-m-d H:i'),
        ];
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
