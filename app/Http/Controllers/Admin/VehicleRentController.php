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
        
        return view('admin.vehicle_rents.create', compact('projects', 'suppliers', 'bankAccounts', 'vehicleTypes'));
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
        return redirect()->route('admin.vehicle-rents.index')
            ->with('success', 'Vehicle rent record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleRent $vehicleRent)
    {
        $vehicleRent->load(['project', 'bankAccount', 'creator', 'updater']);
        return view('admin.vehicle_rents.show', compact('vehicleRent'));
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
        
        return view('admin.vehicle_rents.edit', compact('vehicleRent', 'projects', 'suppliers', 'bankAccounts', 'vehicleTypes'));
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
        return redirect()->route('admin.vehicle-rents.index')
            ->with('success', 'Vehicle rent record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleRent $vehicleRent)
    {
        $vehicleRent->delete();

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

