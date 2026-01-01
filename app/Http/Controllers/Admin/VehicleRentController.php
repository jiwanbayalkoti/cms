<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\VehicleRent;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\BankAccount;
use App\Models\AdvancePayment;
use App\Support\CompanyContext;
use App\Exports\VehicleRentExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VehicleRentController extends Controller
{
    use ValidatesForms, HasProjectAccess;
    
    public function __construct()
    {
        $this->middleware('admin');
    }
    
    /**
     * Validate vehicle rent form data (AJAX endpoint)
     */
    public function validateVehicleRent(Request $request)
    {
        // Validate rate_type first to determine if total_amount is required
        $rateType = $request->input('rate_type');
        $totalAmountRule = $rateType === 'not_fixed' ? 'nullable|numeric|min:0' : 'required|numeric|min:0';
        
        $rules = [
            'project_id' => 'nullable|exists:projects,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'vehicle_type' => 'required|string|max:100',
            'vehicle_number' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:255',
            'driver_contact' => 'nullable|string|max:50',
            'rent_date' => 'required|date',
            'start_location' => 'required|string|max:255',
            'destination_location' => 'required|string|max:255',
            'distance_km' => 'nullable|numeric|min:0',
            'hours' => 'nullable|integer|min:0|max:23',
            'minutes' => 'nullable|integer|min:0|max:59',
            'rate_per_km' => 'nullable|numeric|min:0',
            'rate_per_hour' => 'nullable|numeric|min:0',
            'fixed_rate' => 'nullable|numeric|min:0',
            'number_of_days' => 'nullable|integer|min:1',
            'rate_per_day' => 'nullable|numeric|min:0',
            'rent_start_date' => 'nullable|date',
            'rent_end_date' => 'nullable|date|after_or_equal:rent_start_date',
            'quantity_quintal' => 'nullable|numeric|min:0',
            'rate_per_quintal' => 'nullable|numeric|min:0',
            'rate_type' => 'required|in:per_km,fixed,per_hour,daywise,per_quintal,not_fixed',
            'total_amount' => $totalAmountRule,
            'paid_amount' => 'nullable|numeric|min:0',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'purpose' => 'nullable|string',
        ];
        
        return $this->validateForm($request, $rules);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = VehicleRent::where('company_id', $companyId)
            ->with(['project', 'supplier', 'bankAccount', 'creator'])
            ->orderBy('rent_date', 'desc')
            ->orderBy('created_at', 'desc');
        
        // Filter by accessible projects
        $this->filterByAccessibleProjects($query, 'project_id');
        
        // Filter by project
        if ($request->filled('project_id')) {
            $projectId = (int) $request->project_id;
            // Verify user has access to this project
            if (!$this->canAccessProject($projectId)) {
                abort(403, 'You do not have access to this project.');
            }
            $query->where('project_id', $projectId);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Filter by vehicle type
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        
        // Filter by rate type
        if ($request->filled('rate_type')) {
            $query->where('rate_type', $request->rate_type);
        }
        
        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('rent_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('rent_date', '<=', $request->end_date);
        }
        
        // Get all filtered results for summary calculations (before pagination)
        $allRents = $query->get();
        
        // Calculate summary totals
        $totalAmount = 0;
        $totalPaid = 0;
        $totalBalance = 0;
        $totalAdvancePayments = 0;
        
        foreach ($allRents as $rent) {
            // Use calculated amounts for ongoing daywise rents
            $rentTotal = $rent->is_ongoing ? $rent->calculated_total_amount : $rent->total_amount;
            $rentBalance = $rent->is_ongoing ? $rent->calculated_balance_amount : $rent->balance_amount;
            
            $totalAmount += $rentTotal;
            $totalPaid += $rent->paid_amount;
            $totalBalance += $rentBalance;
        }
        
        // Calculate advance payments only when supplier filter is selected
        $totalAdvancePayments = 0;
        $netBalance = $totalBalance;
        
        if ($request->filled('supplier_id')) {
            $totalAdvancePayments = AdvancePayment::where('company_id', $companyId)
                ->where('supplier_id', $request->supplier_id)
                ->sum('amount');
            
            // Calculate net balance (after advance payments)
            $netBalance = $totalBalance - $totalAdvancePayments;
        }
        
        // Paginate results (calculations for ongoing rents will be done dynamically in views)
        $vehicleRents = $query->paginate(10);
        
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $vehicleTypes = VehicleRent::getVehicleTypes();
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $vehicleRentsData = $vehicleRents->map(function($rent) use ($vehicleTypes) {
                $statusClass = '';
                $statusIcon = '';
                if ($rent->payment_status === 'paid') {
                    $statusClass = 'badge bg-success';
                    $statusIcon = 'bi-check-circle-fill';
                } elseif ($rent->payment_status === 'partial') {
                    $statusClass = 'badge bg-warning text-dark';
                    $statusIcon = 'bi-exclamation-triangle-fill';
                } else {
                    $statusClass = 'badge bg-danger';
                    $statusIcon = 'bi-x-circle-fill';
                }
                
                // Calculate balance
                $balanceAmount = $rent->is_ongoing ? ($rent->calculated_balance_amount ?? 0) : ($rent->balance_amount ?? 0);
                $totalAmount = $rent->is_ongoing ? ($rent->calculated_total_amount ?? $rent->total_amount) : $rent->total_amount;
                
                // Format route
                $route = '';
                if ($rent->start_location || $rent->destination_location) {
                    $route = '<strong>From:</strong> ' . ($rent->start_location ?? 'N/A') . '<br><strong>To:</strong> ' . ($rent->destination_location ?? 'N/A');
                } else {
                    $route = 'N/A';
                }
                
                return [
                    'id' => $rent->id,
                    'rent_date' => $rent->rent_date->format('Y-m-d'),
                    'vehicle_type' => $vehicleTypes[$rent->vehicle_type] ?? $rent->vehicle_type,
                    'vehicle_number' => $rent->vehicle_number ?? '—',
                    'route' => $route,
                    'rate_type' => ucfirst(str_replace('_', ' ', $rent->rate_type)),
                    'project_name' => $rent->project ? $rent->project->name : '—',
                    'supplier_name' => $rent->supplier ? $rent->supplier->name : '—',
                    'total_amount' => number_format($totalAmount, 2),
                    'paid_amount' => number_format($rent->paid_amount ?? 0, 2),
                    'balance' => number_format($balanceAmount, 2),
                    'payment_status' => ucfirst($rent->payment_status),
                    'status_class' => $statusClass,
                    'status_icon' => $statusIcon,
                    'is_ongoing' => $rent->is_ongoing ?? false,
                ];
            });
            
            $summaryData = null;
            if ($allRents->count() > 0) {
                $summaryData = [
                    'totalAmount' => number_format($totalAmount, 2),
                    'totalPaid' => number_format($totalPaid, 2),
                    'totalBalance' => number_format($totalBalance, 2),
                    'totalAdvancePayments' => number_format($totalAdvancePayments ?? 0, 2),
                    'netBalance' => number_format($netBalance ?? 0, 2),
                    'hasNetBalance' => isset($netBalance),
                ];
            }
            
            return response()->json([
                'vehicleRents' => $vehicleRentsData,
                'pagination' => $vehicleRents->links()->render(),
                'summary' => $summaryData,
            ]);
        }
        
        return view('admin.vehicle_rents.index', compact('vehicleRents', 'projects', 'suppliers', 'vehicleTypes', 'totalAmount', 'totalPaid', 'totalBalance', 'totalAdvancePayments', 'netBalance'));
    }

    /**
     * Export vehicle rents to Excel
     */
    public function export(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = VehicleRent::where('company_id', $companyId)
            ->with(['project', 'supplier', 'bankAccount', 'creator'])
            ->orderBy('rent_date', 'desc')
            ->orderBy('created_at', 'desc');
        
        // Apply same filters as index method
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        
        if ($request->filled('rate_type')) {
            $query->where('rate_type', $request->rate_type);
        }
        
        if ($request->filled('start_date')) {
            $query->where('rent_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('rent_date', '<=', $request->end_date);
        }
        
        $filename = 'vehicle_rents_' . now()->format('Ymd_His') . '.xlsx';
        
        return Excel::download(new VehicleRentExport($query), $filename);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        
        $vehicleTypes = VehicleRent::getVehicleTypes();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'projects' => $projects,
                'suppliers' => $suppliers,
                'bankAccounts' => $bankAccounts,
                'vehicleTypes' => $vehicleTypes,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.vehicle-rents.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        // Validate rate_type first to determine if total_amount is required
        $rateType = $request->input('rate_type');
        $totalAmountRule = $rateType === 'not_fixed' ? 'nullable|numeric|min:0' : 'required|numeric|min:0';
        
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'vehicle_type' => 'required|string|max:100',
            'vehicle_number' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:255',
            'driver_contact' => 'nullable|string|max:50',
            'rent_date' => 'required|date',
            'start_location' => 'required|string|max:255',
            'destination_location' => 'required|string|max:255',
            'distance_km' => 'nullable|numeric|min:0',
            'hours' => 'nullable|integer|min:0|max:23',
            'minutes' => 'nullable|integer|min:0|max:59',
            'rate_per_km' => 'nullable|numeric|min:0',
            'rate_per_hour' => 'nullable|numeric|min:0',
            'fixed_rate' => 'nullable|numeric|min:0',
            'number_of_days' => 'nullable|integer|min:1',
            'rate_per_day' => 'nullable|numeric|min:0',
            'rent_start_date' => 'nullable|date',
            'rent_end_date' => 'nullable|date|after_or_equal:rent_start_date',
            'quantity_quintal' => 'nullable|numeric|min:0',
            'rate_per_quintal' => 'nullable|numeric|min:0',
            'rate_type' => 'required|in:per_km,fixed,per_hour,daywise,per_quintal,not_fixed',
            'total_amount' => $totalAmountRule,
            'paid_amount' => 'nullable|numeric|min:0',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'purpose' => 'nullable|string',
        ]);
        
        $validated['company_id'] = $companyId;
        $validated['created_by'] = auth()->id();
        
        // Auto-calculate total based on rate type
        $totalAmount = 0;
        
        if ($validated['rate_type'] === 'per_km' && isset($validated['distance_km']) && isset($validated['rate_per_km'])) {
            $totalAmount = $validated['distance_km'] * $validated['rate_per_km'];
        } elseif ($validated['rate_type'] === 'per_hour') {
            $hours = $validated['hours'] ?? 0;
            $minutes = $validated['minutes'] ?? 0;
            $totalHours = $hours + ($minutes / 60);
            $ratePerHour = $validated['rate_per_hour'] ?? 0;
            $totalAmount = $totalHours * $ratePerHour;
        } elseif ($validated['rate_type'] === 'daywise') {
            $ratePerDay = $validated['rate_per_day'] ?? 0;
            
            // Check if it's an ongoing rent (start date set but end date empty)
            $isOngoing = isset($validated['rent_start_date']) 
                && (!isset($validated['rent_end_date']) || empty($validated['rent_end_date']));
            
            if ($isOngoing) {
                // For ongoing rents, don't save number_of_days and set total_amount to 0
                // (will be calculated dynamically in views/reports)
                $validated['number_of_days'] = null;
                $totalAmount = 0;
            } else {
                // Calculate days based on rent dates if provided, otherwise use number_of_days
                if (isset($validated['rent_start_date'])) {
                    $startDate = \Carbon\Carbon::parse($validated['rent_start_date']);
                    $endDate = isset($validated['rent_end_date']) && $validated['rent_end_date'] 
                        ? \Carbon\Carbon::parse($validated['rent_end_date']) 
                        : now();
                    $numberOfDays = max(1, $startDate->diffInDays($endDate) + 1); // +1 to include both dates
                    $validated['number_of_days'] = $numberOfDays;
                } else {
                    $numberOfDays = $validated['number_of_days'] ?? 0;
                }
                
                $totalAmount = $numberOfDays * $ratePerDay;
            }
        } elseif ($validated['rate_type'] === 'per_quintal') {
            $quantityQuintal = $validated['quantity_quintal'] ?? 0;
            $ratePerQuintal = $validated['rate_per_quintal'] ?? 0;
            $totalAmount = $quantityQuintal * $ratePerQuintal;
        } elseif ($validated['rate_type'] === 'not_fixed') {
            // For not_fixed, use the total_amount directly from input (can be 0 or empty initially)
            $totalAmount = $validated['total_amount'] ?? 0;
        } elseif ($validated['rate_type'] === 'fixed') {
            $totalAmount = $validated['fixed_rate'] ?? $validated['total_amount'] ?? 0;
        }
        
        // For not_fixed, allow 0 total amount (user will enter it)
        if ($validated['rate_type'] === 'not_fixed' && $totalAmount <= 0) {
            $validated['total_amount'] = 0;
        } else {
            $validated['total_amount'] = $totalAmount;
        }
        
        // Calculate balance using final total amount
        $paidAmount = $validated['paid_amount'] ?? 0;
        $finalTotalAmount = $validated['total_amount'];
        
        // Validate that paid amount doesn't exceed total amount
        if ($paidAmount > $finalTotalAmount && $finalTotalAmount > 0) {
            return back()
                ->withInput()
                ->withErrors(['paid_amount' => 'Advance payment cannot exceed total amount.']);
        }
        
        $balanceAmount = max(0, $finalTotalAmount - $paidAmount);
        
        // Determine payment status
        $paymentStatus = 'unpaid';
        // If total amount is 0, it should be unpaid (not paid)
        if ($finalTotalAmount > 0) {
            if ($balanceAmount <= 0.01) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }
        }
        
        $validated['balance_amount'] = $balanceAmount;
        $validated['payment_status'] = $paymentStatus;

        $vehicleRent = VehicleRent::create($validated);
        $this->createExpenseFromVehicleRent($vehicleRent);
        $vehicleRent->load(['project', 'supplier']);
        
        $vehicleTypes = VehicleRent::getVehicleTypes();
        $rateTypeLabels = [
            'fixed' => 'Fixed Rate',
            'per_km' => 'Per KM',
            'per_hour' => 'Per Hour',
            'daywise' => 'Daywise',
            'per_quintal' => 'Per Quintal',
            'not_fixed' => 'Not Fixed',
        ];
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle rent record created successfully.',
                'rent' => [
                    'id' => $vehicleRent->id,
                    'rent_date' => $vehicleRent->rent_date->format('Y-m-d'),
                    'vehicle_type' => $vehicleTypes[$vehicleRent->vehicle_type] ?? $vehicleRent->vehicle_type,
                    'vehicle_number' => $vehicleRent->vehicle_number ?? '—',
                    'start_location' => $vehicleRent->start_location,
                    'destination_location' => $vehicleRent->destination_location,
                    'rate_type' => $vehicleRent->rate_type,
                    'rate_type_label' => $rateTypeLabels[$vehicleRent->rate_type] ?? ucfirst(str_replace('_', ' ', $vehicleRent->rate_type)),
                    'project_name' => $vehicleRent->project ? $vehicleRent->project->name : '—',
                    'supplier_name' => $vehicleRent->supplier ? $vehicleRent->supplier->name : '—',
                    'total_amount' => number_format($vehicleRent->total_amount, 2),
                    'paid_amount' => number_format($vehicleRent->paid_amount, 2),
                    'balance_amount' => number_format($vehicleRent->balance_amount, 2),
                    'payment_status' => $vehicleRent->payment_status,
                ],
            ]);
        }
        
        return redirect()->route('admin.vehicle-rents.index')
            ->with('success', 'Vehicle rent record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleRent $vehicleRent)
    {
        $vehicleRent->load(['project', 'bankAccount', 'creator', 'updater', 'supplier']);
        $vehicleTypes = VehicleRent::getVehicleTypes();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            $rateTypeLabels = [
                'fixed' => 'Fixed Rate',
                'per_km' => 'Per KM',
                'per_hour' => 'Per Hour',
                'daywise' => 'Daywise',
                'per_quintal' => 'Per Quintal',
                'not_fixed' => 'Not Fixed',
            ];
            
            return response()->json([
                'rent' => [
                    'id' => $vehicleRent->id,
                    'rent_date' => $vehicleRent->rent_date->format('Y-m-d'),
                    'vehicle_type' => $vehicleTypes[$vehicleRent->vehicle_type] ?? $vehicleRent->vehicle_type,
                    'vehicle_number' => $vehicleRent->vehicle_number,
                    'driver_name' => $vehicleRent->driver_name,
                    'driver_contact' => $vehicleRent->driver_contact,
                    'start_location' => $vehicleRent->start_location,
                    'destination_location' => $vehicleRent->destination_location,
                    'distance_km' => $vehicleRent->distance_km ? number_format($vehicleRent->distance_km, 2) : null,
                    'hours' => $vehicleRent->hours,
                    'minutes' => $vehicleRent->minutes,
                    'rate_per_km' => $vehicleRent->rate_per_km ? number_format($vehicleRent->rate_per_km, 2) : null,
                    'rate_per_hour' => $vehicleRent->rate_per_hour ? number_format($vehicleRent->rate_per_hour, 2) : null,
                    'fixed_rate' => $vehicleRent->fixed_rate ? number_format($vehicleRent->fixed_rate, 2) : null,
                    'number_of_days' => $vehicleRent->number_of_days,
                    'rate_per_day' => $vehicleRent->rate_per_day ? number_format($vehicleRent->rate_per_day, 2) : null,
                    'rent_start_date' => $vehicleRent->rent_start_date ? $vehicleRent->rent_start_date->format('Y-m-d') : null,
                    'rent_end_date' => $vehicleRent->rent_end_date ? $vehicleRent->rent_end_date->format('Y-m-d') : null,
                    'quantity_quintal' => $vehicleRent->quantity_quintal ? number_format($vehicleRent->quantity_quintal, 2) : null,
                    'rate_per_quintal' => $vehicleRent->rate_per_quintal ? number_format($vehicleRent->rate_per_quintal, 2) : null,
                    'rate_type' => $vehicleRent->rate_type,
                    'rate_type_label' => $rateTypeLabels[$vehicleRent->rate_type] ?? ucfirst(str_replace('_', ' ', $vehicleRent->rate_type)),
                    'total_amount' => number_format($vehicleRent->total_amount, 2),
                    'paid_amount' => number_format($vehicleRent->paid_amount, 2),
                    'balance_amount' => number_format($vehicleRent->balance_amount, 2),
                    'payment_status' => $vehicleRent->payment_status,
                    'payment_date' => $vehicleRent->payment_date ? $vehicleRent->payment_date->format('Y-m-d') : null,
                    'bank_account_name' => $vehicleRent->bankAccount ? $vehicleRent->bankAccount->account_name : null,
                    'notes' => $vehicleRent->notes,
                    'purpose' => $vehicleRent->purpose,
                    'project_name' => $vehicleRent->project ? $vehicleRent->project->name : null,
                    'supplier_name' => $vehicleRent->supplier ? $vehicleRent->supplier->name : null,
                    'created_by' => $vehicleRent->creator ? $vehicleRent->creator->name : 'N/A',
                    'updated_by' => $vehicleRent->updater ? $vehicleRent->updater->name : 'N/A',
                    'created_at' => $vehicleRent->created_at ? $vehicleRent->created_at->format('M d, Y H:i') : '',
                    'updated_at' => $vehicleRent->updated_at ? $vehicleRent->updated_at->format('M d, Y H:i') : '',
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.vehicle-rents.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleRent $vehicleRent)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        
        $vehicleTypes = VehicleRent::getVehicleTypes();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'rent' => [
                    'id' => $vehicleRent->id,
                    'project_id' => $vehicleRent->project_id,
                    'supplier_id' => $vehicleRent->supplier_id,
                    'vehicle_type' => $vehicleRent->vehicle_type,
                    'vehicle_number' => $vehicleRent->vehicle_number,
                    'driver_name' => $vehicleRent->driver_name,
                    'driver_contact' => $vehicleRent->driver_contact,
                    'rent_date' => $vehicleRent->rent_date->format('Y-m-d'),
                    'start_location' => $vehicleRent->start_location,
                    'destination_location' => $vehicleRent->destination_location,
                    'distance_km' => $vehicleRent->distance_km,
                    'hours' => $vehicleRent->hours,
                    'minutes' => $vehicleRent->minutes,
                    'rate_per_km' => $vehicleRent->rate_per_km,
                    'rate_per_hour' => $vehicleRent->rate_per_hour,
                    'fixed_rate' => $vehicleRent->fixed_rate,
                    'number_of_days' => $vehicleRent->number_of_days,
                    'rate_per_day' => $vehicleRent->rate_per_day,
                    'rent_start_date' => $vehicleRent->rent_start_date ? $vehicleRent->rent_start_date->format('Y-m-d') : null,
                    'rent_end_date' => $vehicleRent->rent_end_date ? $vehicleRent->rent_end_date->format('Y-m-d') : null,
                    'quantity_quintal' => $vehicleRent->quantity_quintal,
                    'rate_per_quintal' => $vehicleRent->rate_per_quintal,
                    'rate_type' => $vehicleRent->rate_type,
                    'total_amount' => $vehicleRent->total_amount,
                    'paid_amount' => $vehicleRent->paid_amount,
                    'bank_account_id' => $vehicleRent->bank_account_id,
                    'payment_date' => $vehicleRent->payment_date ? $vehicleRent->payment_date->format('Y-m-d') : null,
                    'notes' => $vehicleRent->notes,
                    'purpose' => $vehicleRent->purpose,
                ],
                'projects' => $projects,
                'suppliers' => $suppliers,
                'bankAccounts' => $bankAccounts,
                'vehicleTypes' => $vehicleTypes,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.vehicle-rents.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VehicleRent $vehicleRent)
    {
        // Validate rate_type first to determine if total_amount is required
        $rateType = $request->input('rate_type');
        $totalAmountRule = $rateType === 'not_fixed' ? 'nullable|numeric|min:0' : 'required|numeric|min:0';
        
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'vehicle_type' => 'required|string|max:100',
            'vehicle_number' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:255',
            'driver_contact' => 'nullable|string|max:50',
            'rent_date' => 'required|date',
            'start_location' => 'required|string|max:255',
            'destination_location' => 'required|string|max:255',
            'distance_km' => 'nullable|numeric|min:0',
            'hours' => 'nullable|integer|min:0|max:23',
            'minutes' => 'nullable|integer|min:0|max:59',
            'rate_per_km' => 'nullable|numeric|min:0',
            'rate_per_hour' => 'nullable|numeric|min:0',
            'fixed_rate' => 'nullable|numeric|min:0',
            'number_of_days' => 'nullable|integer|min:1',
            'rate_per_day' => 'nullable|numeric|min:0',
            'rent_start_date' => 'nullable|date',
            'rent_end_date' => 'nullable|date|after_or_equal:rent_start_date',
            'quantity_quintal' => 'nullable|numeric|min:0',
            'rate_per_quintal' => 'nullable|numeric|min:0',
            'rate_type' => 'required|in:per_km,fixed,per_hour,daywise,per_quintal,not_fixed',
            'total_amount' => $totalAmountRule,
            'paid_amount' => 'nullable|numeric|min:0',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'purpose' => 'nullable|string',
        ]);
        
        $validated['updated_by'] = auth()->id();
        
        // Auto-calculate total based on rate type
        $totalAmount = 0;
        
        if ($validated['rate_type'] === 'per_km' && isset($validated['distance_km']) && isset($validated['rate_per_km'])) {
            $totalAmount = $validated['distance_km'] * $validated['rate_per_km'];
        } elseif ($validated['rate_type'] === 'per_hour') {
            $hours = $validated['hours'] ?? 0;
            $minutes = $validated['minutes'] ?? 0;
            $totalHours = $hours + ($minutes / 60);
            $ratePerHour = $validated['rate_per_hour'] ?? 0;
            $totalAmount = $totalHours * $ratePerHour;
        } elseif ($validated['rate_type'] === 'daywise') {
            $ratePerDay = $validated['rate_per_day'] ?? 0;
            
            // Check if it's an ongoing rent (start date set but end date empty)
            $isOngoing = isset($validated['rent_start_date']) 
                && (!isset($validated['rent_end_date']) || empty($validated['rent_end_date']));
            
            if ($isOngoing) {
                // For ongoing rents, don't save number_of_days and set total_amount to 0
                // (will be calculated dynamically in views/reports)
                $validated['number_of_days'] = null;
                $totalAmount = 0;
            } else {
                // Calculate days based on rent dates if provided, otherwise use number_of_days
                if (isset($validated['rent_start_date'])) {
                    $startDate = \Carbon\Carbon::parse($validated['rent_start_date']);
                    $endDate = isset($validated['rent_end_date']) && $validated['rent_end_date'] 
                        ? \Carbon\Carbon::parse($validated['rent_end_date']) 
                        : now();
                    $numberOfDays = max(1, $startDate->diffInDays($endDate) + 1); // +1 to include both dates
                    $validated['number_of_days'] = $numberOfDays;
                } else {
                    $numberOfDays = $validated['number_of_days'] ?? 0;
                }
                
                $totalAmount = $numberOfDays * $ratePerDay;
            }
        } elseif ($validated['rate_type'] === 'per_quintal') {
            $quantityQuintal = $validated['quantity_quintal'] ?? 0;
            $ratePerQuintal = $validated['rate_per_quintal'] ?? 0;
            $totalAmount = $quantityQuintal * $ratePerQuintal;
        } elseif ($validated['rate_type'] === 'not_fixed') {
            // For not_fixed, use the total_amount directly from input (can be 0 or empty initially)
            $totalAmount = $validated['total_amount'] ?? 0;
        } elseif ($validated['rate_type'] === 'fixed') {
            $totalAmount = $validated['fixed_rate'] ?? $validated['total_amount'] ?? 0;
        }
        
        // For not_fixed, allow 0 total amount (user will enter it)
        if ($validated['rate_type'] === 'not_fixed' && $totalAmount <= 0) {
            $validated['total_amount'] = 0;
        } else {
            $validated['total_amount'] = $totalAmount;
        }
        
        // Calculate balance using final total amount
        $paidAmount = $validated['paid_amount'] ?? 0;
        $finalTotalAmount = $validated['total_amount'];
        $balanceAmount = $finalTotalAmount - $paidAmount;
        
        // Determine payment status
        $paymentStatus = 'unpaid';
        // If total amount is 0, it should be unpaid (not paid)
        if ($finalTotalAmount > 0) {
            if ($balanceAmount <= 0.01) {
                $paymentStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $paymentStatus = 'partial';
            }
        }
        
        $validated['balance_amount'] = $balanceAmount;
        $validated['payment_status'] = $paymentStatus;

        $vehicleRent->update($validated);
        $this->createExpenseFromVehicleRent($vehicleRent);
        $vehicleRent->load(['project', 'supplier']);
        $vehicleTypes = VehicleRent::getVehicleTypes();
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $rateTypeLabels = [
                'fixed' => 'Fixed Rate',
                'per_km' => 'Per KM',
                'per_hour' => 'Per Hour',
                'daywise' => 'Daywise',
                'per_quintal' => 'Per Quintal',
                'not_fixed' => 'Not Fixed',
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'Vehicle rent record updated successfully.',
                'rent' => [
                    'id' => $vehicleRent->id,
                    'rent_date' => $vehicleRent->rent_date->format('Y-m-d'),
                    'vehicle_type' => $vehicleTypes[$vehicleRent->vehicle_type] ?? $vehicleRent->vehicle_type,
                    'vehicle_number' => $vehicleRent->vehicle_number ?? '—',
                    'start_location' => $vehicleRent->start_location,
                    'destination_location' => $vehicleRent->destination_location,
                    'rate_type' => $vehicleRent->rate_type,
                    'rate_type_label' => $rateTypeLabels[$vehicleRent->rate_type] ?? ucfirst(str_replace('_', ' ', $vehicleRent->rate_type)),
                    'project_name' => $vehicleRent->project ? $vehicleRent->project->name : '—',
                    'supplier_name' => $vehicleRent->supplier ? $vehicleRent->supplier->name : '—',
                    'total_amount' => number_format($vehicleRent->total_amount, 2),
                    'paid_amount' => number_format($vehicleRent->paid_amount, 2),
                    'balance_amount' => number_format($vehicleRent->balance_amount, 2),
                    'payment_status' => $vehicleRent->payment_status,
                ],
            ]);
        }
        
        return redirect()->route('admin.vehicle-rents.index')
            ->with('success', 'Vehicle rent record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleRent $vehicleRent)
    {
        $vehicleRent->delete();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle rent record deleted successfully.',
            ]);
        }

        return redirect()->route('admin.vehicle-rents.index')
            ->with('success', 'Vehicle rent record deleted successfully.');
    }

    /**
     * Create an expense record for a PAID vehicle rent, once only.
     */
    private function createExpenseFromVehicleRent(\App\Models\VehicleRent $vehicleRent)
    {
        if ($vehicleRent->payment_status === 'paid' && !\App\Models\Expense::where('vehicle_rent_id', $vehicleRent->id)->exists()) {
            // Try to find a Transport/Vehicle Rent category; fallback to first company category
            $category = \App\Models\Category::where('company_id', $vehicleRent->company_id)
                ->where(function ($q) {
                    $q->where('name', 'like', '%Vehicle%')
                      ->orWhere('name', 'like', '%Transport%')
                      ->orWhere('name', 'like', '%Rent%');
                })->first();

            if (!$category) {
                $category = \App\Models\Category::where('company_id', $vehicleRent->company_id)->first();
            }

            $vehicleType = ucfirst($vehicleRent->vehicle_type ?? 'vehicle');
            \App\Models\Expense::create([
                'company_id'        => $vehicleRent->company_id,
                'project_id'        => $vehicleRent->project_id,
                'vehicle_rent_id'   => $vehicleRent->id,
                'category_id'       => $category ? $category->id : 1,
                'expense_type'      => 'Vehicle rent',
                'item_name'         => "$vehicleType Rent",
                'description'       => "Vehicle No: {$vehicleRent->vehicle_number}, Driver: {$vehicleRent->driver_name}" . ($vehicleRent->notes ? " | Notes: {$vehicleRent->notes}" : ''),
                'amount'            => $vehicleRent->total_amount,
                'date'              => $vehicleRent->payment_date ?? $vehicleRent->rent_date ?? now(),
                'payment_method'    => null,
                'notes'             => $vehicleRent->notes,
                'created_by'        => auth()->id(),
            ]);
        }
    }
}

