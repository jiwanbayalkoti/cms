<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\SalaryPayment;
use App\Models\SalaryPaymentTransaction;
use App\Models\Staff;
use App\Models\Project;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\BankAccount;
use App\Models\PaymentMode;
use Illuminate\Http\Request;
use App\Support\CompanyContext;
use Carbon\Carbon;

class SalaryPaymentController extends Controller
{
    use ValidatesForms;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Validate salary payment form data (AJAX endpoint)
     */
    public function validateSalaryPayment(Request $request, SalaryPayment $salaryPayment = null)
    {
        $rules = [
            'staff_id' => 'required|exists:staff,id',
            'payment_month' => 'required|date',
            'payment_date' => 'required|date',
            'base_salary' => 'required|numeric|min:0',
            'working_days' => 'nullable|integer|min:1',
            'total_days' => 'nullable|integer|min:1',
            'overtime_amount' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'allowance_amount' => 'nullable|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,partial,paid,cancelled',
            'payment_method' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
        ];

        // Unique validation for staff + payment_month + company
        if ($salaryPayment) {
            $rules['payment_month'] .= '|unique:salary_payments,payment_month,' . $salaryPayment->id . ',id,staff_id,' . $request->staff_id . ',company_id,' . CompanyContext::getActiveCompanyId();
        } else {
            $rules['payment_month'] .= '|unique:salary_payments,payment_month,NULL,id,staff_id,' . $request->staff_id . ',company_id,' . CompanyContext::getActiveCompanyId();
        }

        return $this->validateForm($request, $rules);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = SalaryPayment::with(['staff', 'project', 'expense'])
            ->where('company_id', $companyId);

        // Filter by staff
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range (from_date and to_date)
        if ($request->filled('from_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfMonth();
            $query->where('payment_month', '>=', $fromDate);
        }
        if ($request->filled('to_date')) {
            $toDate = Carbon::parse($request->to_date)->endOfMonth();
            $query->where('payment_month', '<=', $toDate);
        }
        
        // Legacy: Filter by payment month (single month)
        if ($request->filled('payment_month')) {
            $query->whereYear('payment_month', Carbon::parse($request->payment_month)->year)
                  ->whereMonth('payment_month', Carbon::parse($request->payment_month)->month);
        }

        $salaryPayments = $query->latest('payment_month')->latest('created_at')->paginate(15)->withQueryString();

        // Get all staff with their payment summaries
        $staff = Staff::where('is_active', true)->orderBy('name')->get();
        
        // Get payment summaries for each staff
        $currentYear = now()->year;
        $staffSummaries = $staff->map(function($staffMember) use ($companyId, $request, $currentYear) {
            $staffPaymentsQuery = SalaryPayment::where('staff_id', $staffMember->id)
                ->where('company_id', $companyId);
            
            // Apply filters if provided
            if ($request->filled('project_id')) {
                $staffPaymentsQuery->where('project_id', $request->project_id);
            }
            if ($request->filled('status')) {
                $staffPaymentsQuery->where('status', $request->status);
            }
            // Filter by date range (from_date and to_date)
            if ($request->filled('from_date')) {
                $fromDate = Carbon::parse($request->from_date)->startOfMonth();
                $staffPaymentsQuery->where('payment_month', '>=', $fromDate);
            }
            if ($request->filled('to_date')) {
                $toDate = Carbon::parse($request->to_date)->endOfMonth();
                $staffPaymentsQuery->where('payment_month', '<=', $toDate);
            }
            // Legacy: Filter by payment month (single month)
            if ($request->filled('payment_month')) {
                $staffPaymentsQuery->whereYear('payment_month', Carbon::parse($request->payment_month)->year)
                      ->whereMonth('payment_month', Carbon::parse($request->payment_month)->month);
            }
            
            $staffPayments = $staffPaymentsQuery->get();
            
            // Calculate totals - filter by current year for total paid
            $currentYearPayments = $staffPayments->filter(function($payment) use ($currentYear) {
                return Carbon::parse($payment->payment_month)->year == $currentYear;
            });
            
            $totalPaid = $currentYearPayments->sum('paid_amount');
            $totalRemaining = $staffPayments->sum('balance_amount'); // Total remaining includes all years
            $totalNet = $staffPayments->sum('net_amount');
            $paymentCount = $staffPayments->count();
            
            // Get latest payment for this staff
            $latestPayment = $staffPayments->sortByDesc('payment_month')->first();
            
            return [
                'staff_id' => $staffMember->id,
                'staff_name' => $staffMember->name,
                'position_name' => $staffMember->position ? $staffMember->position->name : null,
                'project_name' => $staffMember->project ? $staffMember->project->name : null,
                'payment_count' => $paymentCount,
                'total_paid' => number_format($totalPaid, 2),
                'total_remaining' => number_format($totalRemaining, 2),
                'total_net' => number_format($totalNet, 2),
                'latest_payment_id' => $latestPayment ? $latestPayment->id : null,
                'latest_payment_month' => $latestPayment ? $latestPayment->payment_month_name : null,
                'latest_payment_date' => $latestPayment ? $latestPayment->payment_date->format('M d, Y') : null,
            ];
        });
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            // Also return individual payments for backward compatibility
            $salaryPaymentsData = $salaryPayments->map(function($payment) {
                $statusClass = '';
                if ($payment->status === 'paid') {
                    $statusClass = 'bg-green-100 text-green-800';
                } elseif ($payment->status === 'partial') {
                    $statusClass = 'bg-blue-100 text-blue-800';
                } elseif ($payment->status === 'pending') {
                    $statusClass = 'bg-yellow-100 text-yellow-800';
                } else {
                    $statusClass = 'bg-red-100 text-red-800';
                }
                
                return [
                    'id' => $payment->id,
                    'staff_id' => $payment->staff_id,
                    'staff_name' => $payment->staff->name,
                    'project_name' => $payment->project ? $payment->project->name : null,
                    'payment_month_name' => $payment->payment_month_name,
                    'base_salary' => number_format($payment->base_salary, 2),
                    'gross_amount' => number_format($payment->gross_amount, 2),
                    'tax_amount' => number_format($payment->tax_amount ?? 0, 2),
                    'assessment_type' => ucfirst($payment->assessment_type ?? 'single'),
                    'net_amount' => number_format($payment->net_amount, 2),
                    'status' => ucfirst($payment->status),
                    'status_class' => $statusClass,
                    'paid_amount' => $payment->status === 'partial' ? number_format($payment->paid_amount, 2) : null,
                    'payment_date' => $payment->payment_date->format('M d, Y'),
                ];
            });
            
            return response()->json([
                'salaryPayments' => $salaryPaymentsData,
                'staffSummaries' => $staffSummaries,
                'pagination' => $salaryPayments->links()->render(),
            ]);
        }

        return view('admin.salary_payments.index', compact('salaryPayments', 'staff', 'projects', 'staffSummaries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $staff = Staff::where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        $paymentModes = PaymentMode::orderBy('name')->get();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'staff' => $staff->map(function($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'position_name' => $s->position ? $s->position->name : 'N/A',
                        'salary' => $s->salary,
                    ];
                }),
                'projects' => $projects,
                'bankAccounts' => $bankAccounts,
                'paymentModes' => $paymentModes,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.salary-payments.index');
    }

    /**
     * Check for existing salary payment for staff and month.
     */
    public function checkExisting(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'payment_month' => 'required|date',
            'exclude_id' => 'nullable|exists:salary_payments,id', // Exclude current payment when checking
        ]);

        $companyId = CompanyContext::getActiveCompanyId();
        $paymentMonth = Carbon::parse($validated['payment_month'])->startOfMonth();

        $query = SalaryPayment::where('company_id', $companyId)
            ->where('staff_id', $validated['staff_id'])
            ->where('payment_month', $paymentMonth);
        
        // Exclude current payment if provided (for edit form)
        if (!empty($validated['exclude_id'])) {
            $query->where('id', '!=', $validated['exclude_id']);
        }

        $existingPayment = $query->first();

        if ($existingPayment) {
            return response()->json([
                'exists' => true,
                'is_partial' => $existingPayment->status === 'partial',
                'is_paid' => $existingPayment->status === 'paid',
                'data' => [
                    'id' => $existingPayment->id,
                    'base_salary' => (float) $existingPayment->base_salary,
                    'working_days' => $existingPayment->working_days,
                    'total_days' => $existingPayment->total_days,
                    'overtime_amount' => (float) $existingPayment->overtime_amount,
                    'bonus_amount' => (float) $existingPayment->bonus_amount,
                    'allowance_amount' => (float) $existingPayment->allowance_amount,
                    'deduction_amount' => (float) $existingPayment->deduction_amount,
                    'advance_deduction' => (float) $existingPayment->advance_deduction,
                    'gross_amount' => (float) $existingPayment->gross_amount,
                    'net_amount' => (float) $existingPayment->net_amount,
                    'paid_amount' => (float) $existingPayment->paid_amount,
                    'balance_amount' => (float) $existingPayment->balance_amount,
                    'status' => $existingPayment->status,
                    'payment_date' => $existingPayment->payment_date->format('Y-m-d'),
                    'payment_method' => $existingPayment->payment_method,
                    'bank_account_id' => $existingPayment->bank_account_id,
                    'transaction_reference' => $existingPayment->transaction_reference,
                    'notes' => $existingPayment->notes,
                    'project_id' => $existingPayment->project_id,
                ],
            ]);
        }

        return response()->json([
            'exists' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'staff_id' => 'required|exists:staff,id',
                'payment_month' => 'required|date',
                'payment_date' => 'required|date',
                'base_salary' => 'required|numeric|min:0',
                'working_days' => 'nullable|integer|min:1',
                'total_days' => 'nullable|integer|min:1',
                'overtime_amount' => 'nullable|numeric|min:0',
                'bonus_amount' => 'nullable|numeric|min:0',
                'allowance_amount' => 'nullable|numeric|min:0',
                'deduction_amount' => 'nullable|numeric|min:0',
                'advance_deduction' => 'nullable|numeric|min:0',
                'assessment_type' => 'nullable|in:single,couple',
                'status' => 'required|in:pending,partial,paid,cancelled',
                'payment_percentage' => 'nullable|numeric|min:0|max:100',
                'payment_amount' => 'nullable|numeric|min:0',
                'paid_amount' => 'nullable|numeric|min:0',
                'balance_amount' => 'nullable|numeric|min:0',
                'payment_method' => 'nullable|string|max:255',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'transaction_reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'project_id' => 'nullable|exists:projects,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        // Ensure payment_month is first day of month
        $paymentMonth = Carbon::parse($validated['payment_month'])->startOfMonth();

        // Check for existing payment
        $companyId = CompanyContext::getActiveCompanyId();
        $existingPayment = SalaryPayment::where('staff_id', $validated['staff_id'])
            ->where('payment_month', $paymentMonth)
            ->where('company_id', $companyId)
            ->first();

        if ($existingPayment && $existingPayment->status === 'paid') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary payment for this staff member and month is already fully paid.',
                    'errors' => [
                        'payment_month' => ['Salary payment for this staff member and month is already fully paid.']
                    ],
                ], 422);
            }
            return back()->withInput()->with('error', 'Salary payment for this staff member and month is already fully paid.');
        }

        // If partial payment exists, update it instead of creating new
        if ($existingPayment && ($existingPayment->status === 'partial' || $existingPayment->status === 'pending')) {
            // Calculate new payment amount from form
            $newPaymentAmount = 0;
            if (isset($validated['payment_amount']) && $validated['payment_amount'] > 0) {
                $newPaymentAmount = (float) $validated['payment_amount'];
            } elseif (isset($validated['payment_percentage']) && $validated['payment_percentage'] > 0) {
                $newPaymentAmount = ($existingPayment->net_amount * (float) $validated['payment_percentage']) / 100;
            } elseif (isset($validated['paid_amount']) && $validated['paid_amount'] > 0) {
                $newPaymentAmount = (float) $validated['paid_amount'];
            }
            
            // Add new payment amount to existing paid amount
            $updatedPaidAmount = $existingPayment->paid_amount + $newPaymentAmount;
            $updatedBalanceAmount = $existingPayment->net_amount - $updatedPaidAmount;
            
            // Update status
            $newStatus = $existingPayment->status;
            if ($updatedBalanceAmount <= 0.01) {
                $newStatus = 'paid';
                $updatedPaidAmount = $existingPayment->net_amount;
                $updatedBalanceAmount = 0;
            } elseif ($updatedPaidAmount > 0) {
                $newStatus = 'partial';
            }
            
            // Update existing payment
            $existingPayment->update([
                'paid_amount' => $updatedPaidAmount,
                'balance_amount' => $updatedBalanceAmount,
                'status' => $newStatus,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'] ?? $existingPayment->payment_method,
                'bank_account_id' => $validated['bank_account_id'] ?? $existingPayment->bank_account_id,
                'transaction_reference' => $validated['transaction_reference'] ?? $existingPayment->transaction_reference,
                'notes' => $validated['notes'] ?? $existingPayment->notes,
                'updated_by' => auth()->id(),
            ]);
            
            $existingPayment->refresh();
            $existingPayment->load(['staff', 'project']);
            
            // Update expense if needed
            if ($existingPayment->paid_amount > 0) {
                try {
                    if ($existingPayment->expense_id) {
                        $expense = Expense::find($existingPayment->expense_id);
                        if ($expense) {
                            $expense->amount = $existingPayment->paid_amount;
                            $expense->date = $existingPayment->payment_date;
                            $expense->save();
                        }
                    } else {
                        $this->createExpenseFromSalaryPayment($existingPayment);
                        $existingPayment->refresh();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error updating expense: ' . $e->getMessage());
                }
            }
            
            // Return JSON for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment amount added to existing partial payment successfully.',
                    'payment' => [
                        'id' => $existingPayment->id,
                        'staff_name' => $existingPayment->staff->name,
                        'payment_month_name' => $existingPayment->payment_month_name,
                        'base_salary' => number_format($existingPayment->base_salary, 2),
                        'gross_amount' => number_format($existingPayment->gross_amount, 2),
                        'tax_amount' => number_format($existingPayment->tax_amount ?? 0, 2),
                        'net_amount' => number_format($existingPayment->net_amount, 2),
                        'status' => $existingPayment->status,
                        'payment_date' => $existingPayment->payment_date->format('M d, Y'),
                        'project_id' => $existingPayment->project_id,
                        'project_name' => $existingPayment->project ? $existingPayment->project->name : null,
                        'paid_amount' => number_format($existingPayment->paid_amount, 2),
                    ],
                ]);
            }
            
            return redirect()->route('admin.salary-payments.index')
                ->with('success', 'Payment amount added to existing partial payment successfully.');
        }

        // Calculate amounts for new payment
        $baseSalary = $validated['base_salary'];
        
        // If partial month, calculate prorated salary
        if (!empty($validated['working_days']) && !empty($validated['total_days'])) {
            $baseSalary = ($validated['base_salary'] / $validated['total_days']) * $validated['working_days'];
        }

        $grossAmount = $baseSalary 
            + ($validated['overtime_amount'] ?? 0)
            + ($validated['bonus_amount'] ?? 0)
            + ($validated['allowance_amount'] ?? 0);

        // Get assessment type from form or staff default
        $staff = \App\Models\Staff::find($validated['staff_id']);
        $assessmentType = $validated['assessment_type'] ?? $staff->assessment_type ?? 'single';
        
        // Calculate tax
        $taxCalculator = new \App\Services\NepalTaxCalculator();
        $tempSalaryPayment = new \App\Models\SalaryPayment([
            'gross_amount' => $grossAmount,
            'working_days' => $validated['working_days'] ?? null,
            'total_days' => $validated['total_days'] ?? null,
            'assessment_type' => $assessmentType,
        ]);
        $taxResult = $tempSalaryPayment->calculateTax();
        $taxAmount = $taxResult['tax_amount'];
        $taxableIncome = $taxResult['taxable_income'];

        $netAmount = $grossAmount 
            - ($validated['deduction_amount'] ?? 0)
            - ($validated['advance_deduction'] ?? 0)
            - $taxAmount;

        // Initialize payment amounts from form or calculate
        $status = $validated['status'];
        $paidAmount = 0;
        $balanceAmount = $netAmount;
        
        // Get paid_amount and balance_amount from hidden fields if provided
        if (isset($validated['paid_amount']) && isset($validated['balance_amount'])) {
            $paidAmount = (float) $validated['paid_amount'];
            $balanceAmount = (float) $validated['balance_amount'];
            
            // If paid_amount is 0 but payment_amount or payment_percentage is provided, use those instead
            if ($paidAmount <= 0 && isset($validated['payment_amount']) && $validated['payment_amount'] > 0) {
                $paidAmount = (float) $validated['payment_amount'];
                $balanceAmount = $netAmount - $paidAmount;
            } elseif ($paidAmount <= 0 && isset($validated['payment_percentage']) && $validated['payment_percentage'] > 0) {
                $paidAmount = ($netAmount * (float) $validated['payment_percentage']) / 100;
                $balanceAmount = $netAmount - $paidAmount;
            }
            
            // Ensure paid_amount + balance_amount = net_amount (handle rounding)
            $total = $paidAmount + $balanceAmount;
            if (abs($total - $netAmount) > 0.01) {
                // Adjust balance to match net amount
                $balanceAmount = $netAmount - $paidAmount;
            }
            
            // Auto-update status based on payment amounts
            if ($balanceAmount <= 0.01) {
                $status = 'paid';
                $paidAmount = $netAmount;
                $balanceAmount = 0;
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            } else {
                $status = 'pending';
            }
        } else {
            // Fallback: use status to determine payment
            if ($status === 'paid') {
                $paidAmount = $netAmount;
                $balanceAmount = 0;
            } elseif ($status === 'partial') {
                // If status is partial but no paid_amount provided, set based on payment_amount or payment_percentage
                if (isset($validated['payment_amount']) && $validated['payment_amount'] > 0) {
                    $paidAmount = (float) $validated['payment_amount'];
                    $balanceAmount = $netAmount - $paidAmount;
                } elseif (isset($validated['payment_percentage']) && $validated['payment_percentage'] > 0) {
                    $paidAmount = ($netAmount * (float) $validated['payment_percentage']) / 100;
                    $balanceAmount = $netAmount - $paidAmount;
                } else {
                    // Status is partial but no payment amount provided - keep as pending
                    $paidAmount = 0;
                    $balanceAmount = $netAmount;
                    $status = 'pending';
                }
            }
        }

        $salaryPayment = SalaryPayment::create([
            'company_id' => $companyId,
            'project_id' => $validated['project_id'] ?? null,
            'staff_id' => $validated['staff_id'],
            'payment_month' => $paymentMonth,
            'payment_date' => $validated['payment_date'],
            'base_salary' => $validated['base_salary'],
            'working_days' => $validated['working_days'] ?? null,
            'total_days' => $validated['total_days'] ?? null,
            'overtime_amount' => $validated['overtime_amount'] ?? 0,
            'bonus_amount' => $validated['bonus_amount'] ?? 0,
            'allowance_amount' => $validated['allowance_amount'] ?? 0,
            'deduction_amount' => $validated['deduction_amount'] ?? 0,
            'advance_deduction' => $validated['advance_deduction'] ?? 0,
            'assessment_type' => $assessmentType,
            'taxable_income' => $taxableIncome,
            'tax_amount' => $taxAmount,
            'tax_exempt_amount' => 0,
            'gross_amount' => $grossAmount,
            'net_amount' => $netAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $balanceAmount,
            'status' => $status,
            'payment_method' => $validated['payment_method'] ?? null,
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Refresh to get latest data after creation
        $salaryPayment->refresh();
        
        // Create expense if any amount is paid (partial or full)
        // This handles both partial and full payments
        if ($salaryPayment->paid_amount > 0) {
            try {
                $this->createExpenseFromSalaryPayment($salaryPayment);
                // Refresh again to get expense_id
                $salaryPayment->refresh();
            } catch (\Exception $e) {
                \Log::error('Error creating expense from salary payment: ' . $e->getMessage(), [
                    'salary_payment_id' => $salaryPayment->id,
                    'paid_amount' => $salaryPayment->paid_amount,
                    'status' => $salaryPayment->status,
                    'trace' => $e->getTraceAsString(),
                ]);
                // Don't fail the whole request if expense creation fails
            }
        }

        $salaryPayment->load(['staff', 'project']);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary payment created successfully.',
                'payment' => [
                    'id' => $salaryPayment->id,
                    'staff_name' => $salaryPayment->staff->name,
                    'payment_month_name' => $salaryPayment->payment_month_name,
                    'base_salary' => number_format($salaryPayment->base_salary, 2),
                    'gross_amount' => number_format($salaryPayment->gross_amount, 2),
                    'tax_amount' => number_format($salaryPayment->tax_amount ?? 0, 2),
                    'net_amount' => number_format($salaryPayment->net_amount, 2),
                    'status' => $salaryPayment->status,
                    'payment_date' => $salaryPayment->payment_date->format('M d, Y'),
                    'project_id' => $salaryPayment->project_id,
                    'project_name' => $salaryPayment->project ? $salaryPayment->project->name : null,
                    'paid_amount' => number_format($salaryPayment->paid_amount, 2),
                ],
            ]);
        }

        return redirect()->route('admin.salary-payments.index')
            ->with('success', 'Salary payment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SalaryPayment $salaryPayment, Request $request)
    {
        if ($salaryPayment->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $companyId = CompanyContext::getActiveCompanyId();
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        $paymentModes = PaymentMode::orderBy('name')->get();
        $salaryPayment->load(['staff', 'project', 'expense', 'bankAccount', 'creator', 'updater', 'transactions.bankAccount', 'transactions.creator']);
        
        // Get date filters from request
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        \Log::info('Filtering salary payments (show)', [
            'staff_id' => $salaryPayment->staff_id,
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);
        
        // Get all payment history for this staff member
        $allPaymentsQuery = SalaryPayment::where('staff_id', $salaryPayment->staff_id)
            ->where('company_id', $companyId)
            ->with(['project']);
        
        // Apply month filters if provided (format: YYYY-MM)
        if ($fromDate) {
            $fromMonth = Carbon::parse($fromDate)->startOfMonth();
            $allPaymentsQuery->where('payment_month', '>=', $fromMonth);
            \Log::info('Applied from_date filter (show)', ['from_date' => $fromDate, 'from_month' => $fromMonth]);
        }
        if ($toDate) {
            $toMonth = Carbon::parse($toDate)->endOfMonth();
            $allPaymentsQuery->where('payment_month', '<=', $toMonth);
            \Log::info('Applied to_date filter (show)', ['to_date' => $toDate, 'to_month' => $toMonth]);
        }
        
        $allPayments = $allPaymentsQuery->orderBy('payment_month', 'desc')->get();
        
        \Log::info('Filtered payments count (show)', ['count' => $allPayments->count()]);
        
        // Group payments by month and calculate totals
        $monthlyPayments = $allPayments->groupBy(function($payment) {
            return $payment->payment_month->format('Y-m');
        })->map(function($payments, $monthKey) {
            $firstPayment = $payments->first();
            $totalPaid = $payments->sum('paid_amount');
            $totalRemaining = $payments->sum('balance_amount');
            $totalNet = $payments->sum('net_amount');
            
            return [
                'month' => $firstPayment->payment_month->format('F Y'),
                'month_key' => $monthKey,
                'payments' => $payments->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_date' => $payment->payment_date->format('M d, Y'),
                        'payment_date_raw' => $payment->payment_date->format('Y-m-d'),
                        'net_amount' => number_format($payment->net_amount, 2),
                        'paid_amount' => number_format($payment->paid_amount, 2),
                        'balance_amount' => number_format($payment->balance_amount, 2),
                        'status' => $payment->status,
                        'project_name' => $payment->project ? $payment->project->name : null,
                    ];
                })->toArray(),
                'total_paid' => number_format($totalPaid, 2),
                'total_remaining' => number_format($totalRemaining, 2),
                'total_net' => number_format($totalNet, 2),
            ];
        })->values();
        
        // Calculate overall totals
        $overallTotalPaid = $allPayments->sum('paid_amount');
        $overallTotalRemaining = $allPayments->sum('balance_amount');
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'payment' => [
                    'id' => $salaryPayment->id,
                    'staff_id' => $salaryPayment->staff_id,
                    'staff_name' => $salaryPayment->staff->name,
                    'staff_position' => $salaryPayment->staff->position ? $salaryPayment->staff->position->name : null,
                    'project_name' => $salaryPayment->project ? $salaryPayment->project->name : null,
                    'payment_month_name' => $salaryPayment->payment_month_name,
                    'payment_date' => $salaryPayment->payment_date->format('Y-m-d'),
                    'formatted_payment_date' => $salaryPayment->payment_date->format('M d, Y'),
                    'base_salary' => number_format($salaryPayment->base_salary, 2),
                    'working_days' => $salaryPayment->working_days,
                    'total_days' => $salaryPayment->total_days,
                    'overtime_amount' => number_format($salaryPayment->overtime_amount ?? 0, 2),
                    'bonus_amount' => number_format($salaryPayment->bonus_amount ?? 0, 2),
                    'allowance_amount' => number_format($salaryPayment->allowance_amount ?? 0, 2),
                    'deduction_amount' => number_format($salaryPayment->deduction_amount ?? 0, 2),
                    'advance_deduction' => number_format($salaryPayment->advance_deduction ?? 0, 2),
                    'gross_amount' => number_format($salaryPayment->gross_amount, 2),
                    'tax_amount' => number_format($salaryPayment->tax_amount ?? 0, 2),
                    'taxable_income' => number_format($salaryPayment->taxable_income ?? 0, 2),
                    'assessment_type' => $salaryPayment->assessment_type ?? 'single',
                    'net_amount' => number_format($salaryPayment->net_amount, 2),
                    'paid_amount' => number_format($salaryPayment->paid_amount, 2),
                    'balance_amount' => number_format($salaryPayment->balance_amount, 2),
                    'status' => $salaryPayment->status,
                    'payment_method' => $salaryPayment->payment_method,
                    'bank_account_name' => $salaryPayment->bankAccount ? $salaryPayment->bankAccount->account_name : null,
                    'transaction_reference' => $salaryPayment->transaction_reference,
                    'notes' => $salaryPayment->notes,
                    'created_by' => $salaryPayment->creator ? $salaryPayment->creator->name : 'N/A',
                    'updated_by' => $salaryPayment->updater ? $salaryPayment->updater->name : 'N/A',
                    'created_at' => $salaryPayment->created_at ? $salaryPayment->created_at->format('M d, Y H:i') : '',
                    'updated_at' => $salaryPayment->updated_at ? $salaryPayment->updated_at->format('M d, Y H:i') : '',
                ],
                'monthly_payments' => $monthlyPayments,
                'overall_total_paid' => number_format($overallTotalPaid, 2),
                'overall_total_remaining' => number_format($overallTotalRemaining, 2),
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.salary-payments.index');
    }

    /**
     * View staff payment details by staff ID (for staff without payments)
     */
    public function viewByStaff($staffId, Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $staff = Staff::where('id', $staffId)
            ->where('company_id', $companyId)
            ->firstOrFail();
        
        // Get date filters from request
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        
        \Log::info('Filtering salary payments', [
            'staff_id' => $staffId,
            'from_date' => $fromDate,
            'to_date' => $toDate
        ]);
        
        // Get all payment history for this staff member
        $allPaymentsQuery = SalaryPayment::where('staff_id', $staffId)
            ->where('company_id', $companyId)
            ->with(['project']);
        
        // Apply month filters if provided (format: YYYY-MM)
        if ($fromDate) {
            $fromMonth = Carbon::parse($fromDate)->startOfMonth();
            $allPaymentsQuery->where('payment_month', '>=', $fromMonth);
            \Log::info('Applied from_date filter', ['from_date' => $fromDate, 'from_month' => $fromMonth]);
        }
        if ($toDate) {
            $toMonth = Carbon::parse($toDate)->endOfMonth();
            $allPaymentsQuery->where('payment_month', '<=', $toMonth);
            \Log::info('Applied to_date filter', ['to_date' => $toDate, 'to_month' => $toMonth]);
        }
        
        $allPayments = $allPaymentsQuery->orderBy('payment_month', 'desc')->get();
        
        \Log::info('Filtered payments count', ['count' => $allPayments->count()]);
        
        // Group payments by month and calculate totals
        $monthlyPayments = $allPayments->groupBy(function($payment) {
            return $payment->payment_month->format('Y-m');
        })->map(function($payments, $monthKey) {
            $firstPayment = $payments->first();
            $totalPaid = $payments->sum('paid_amount');
            $totalRemaining = $payments->sum('balance_amount');
            $totalNet = $payments->sum('net_amount');
            
            return [
                'month' => $firstPayment->payment_month->format('F Y'),
                'month_key' => $monthKey,
                'payments' => $payments->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_date' => $payment->payment_date->format('M d, Y'),
                        'payment_date_raw' => $payment->payment_date->format('Y-m-d'),
                        'net_amount' => number_format($payment->net_amount, 2),
                        'paid_amount' => number_format($payment->paid_amount, 2),
                        'balance_amount' => number_format($payment->balance_amount, 2),
                        'status' => $payment->status,
                        'project_name' => $payment->project ? $payment->project->name : null,
                    ];
                })->toArray(),
                'total_paid' => number_format($totalPaid, 2),
                'total_remaining' => number_format($totalRemaining, 2),
                'total_net' => number_format($totalNet, 2),
            ];
        })->values();
        
        // Calculate overall totals
        $overallTotalPaid = $allPayments->sum('paid_amount');
        $overallTotalRemaining = $allPayments->sum('balance_amount');
        
        // Get latest payment for edit button
        $latestPayment = $allPayments->first();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'payment' => [
                    'id' => $latestPayment ? $latestPayment->id : null,
                    'staff_id' => $staff->id,
                    'staff_name' => $staff->name,
                    'staff_position' => $staff->position ? $staff->position->name : null,
                    'project_name' => $staff->project ? $staff->project->name : null,
                    'payment_month_name' => $latestPayment ? $latestPayment->payment_month_name : null,
                    'payment_date' => $latestPayment ? $latestPayment->payment_date->format('Y-m-d') : null,
                    'formatted_payment_date' => $latestPayment ? $latestPayment->payment_date->format('M d, Y') : null,
                ],
                'monthly_payments' => $monthlyPayments,
                'overall_total_paid' => number_format($overallTotalPaid, 2),
                'overall_total_remaining' => number_format($overallTotalRemaining, 2),
            ]);
        }
        
        return redirect()->route('admin.salary-payments.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalaryPayment $salaryPayment)
    {
        if ($salaryPayment->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $companyId = CompanyContext::getActiveCompanyId();
        $staff = Staff::where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        $paymentModes = PaymentMode::orderBy('name')->get();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'payment' => [
                    'id' => $salaryPayment->id,
                    'staff_id' => $salaryPayment->staff_id,
                    'project_id' => $salaryPayment->project_id,
                    'payment_month' => $salaryPayment->payment_month->format('Y-m'),
                    'payment_date' => $salaryPayment->payment_date->format('Y-m-d'),
                    'base_salary' => $salaryPayment->base_salary,
                    'working_days' => $salaryPayment->working_days,
                    'total_days' => $salaryPayment->total_days,
                    'overtime_amount' => $salaryPayment->overtime_amount ?? 0,
                    'bonus_amount' => $salaryPayment->bonus_amount ?? 0,
                    'allowance_amount' => $salaryPayment->allowance_amount ?? 0,
                    'deduction_amount' => $salaryPayment->deduction_amount ?? 0,
                    'advance_deduction' => $salaryPayment->advance_deduction ?? 0,
                    'assessment_type' => $salaryPayment->assessment_type ?? 'single',
                    'status' => $salaryPayment->status,
                    'payment_method' => $salaryPayment->payment_method,
                    'bank_account_id' => $salaryPayment->bank_account_id,
                    'transaction_reference' => $salaryPayment->transaction_reference,
                    'notes' => $salaryPayment->notes,
                    'paid_amount' => $salaryPayment->paid_amount,
                    'balance_amount' => $salaryPayment->balance_amount,
                ],
                'staff' => $staff->map(function($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'position_name' => $s->position ? $s->position->name : 'N/A',
                        'salary' => $s->salary,
                    ];
                }),
                'projects' => $projects,
                'bankAccounts' => $bankAccounts,
                'paymentModes' => $paymentModes,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.salary-payments.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalaryPayment $salaryPayment)
    {
        if ($salaryPayment->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }

        $validated = $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'payment_month' => 'required|date|unique:salary_payments,payment_month,' . $salaryPayment->id . ',id,staff_id,' . $request->staff_id . ',company_id,' . $salaryPayment->company_id,
            'payment_date' => 'required|date',
            'base_salary' => 'required|numeric|min:0',
            'working_days' => 'nullable|integer|min:1',
            'total_days' => 'nullable|integer|min:1',
            'overtime_amount' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'allowance_amount' => 'nullable|numeric|min:0',
            'deduction_amount' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'assessment_type' => 'nullable|in:single,couple',
            'status' => 'required|in:pending,partial,paid,cancelled',
            'payment_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'balance_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        // Ensure payment_month is first day of month
        $paymentMonth = Carbon::parse($validated['payment_month'])->startOfMonth();

        // Calculate amounts
        $baseSalary = $validated['base_salary'];
        
        // If partial month, calculate prorated salary
        if (!empty($validated['working_days']) && !empty($validated['total_days'])) {
            $baseSalary = ($validated['base_salary'] / $validated['total_days']) * $validated['working_days'];
        }

        $grossAmount = $baseSalary 
            + ($validated['overtime_amount'] ?? 0)
            + ($validated['bonus_amount'] ?? 0)
            + ($validated['allowance_amount'] ?? 0);

        // Get assessment type from form or staff default
        $staff = \App\Models\Staff::find($validated['staff_id']);
        $assessmentType = $validated['assessment_type'] ?? $staff->assessment_type ?? $salaryPayment->assessment_type ?? 'single';
        
        // Calculate tax
        $taxCalculator = new \App\Services\NepalTaxCalculator();
        $tempSalaryPayment = new \App\Models\SalaryPayment([
            'gross_amount' => $grossAmount,
            'working_days' => $validated['working_days'] ?? null,
            'total_days' => $validated['total_days'] ?? null,
            'assessment_type' => $assessmentType,
        ]);
        $taxResult = $tempSalaryPayment->calculateTax();
        $taxAmount = $taxResult['tax_amount'];
        $taxableIncome = $taxResult['taxable_income'];

        $oldStatus = $salaryPayment->status;
        $oldNetAmount = $salaryPayment->net_amount;
        
        $netAmount = $grossAmount 
            - ($validated['deduction_amount'] ?? 0)
            - ($validated['advance_deduction'] ?? 0)
            - $taxAmount;
        
        // Get paid_amount and balance_amount from hidden fields if provided
        $status = $validated['status'];
        $paidAmount = $salaryPayment->paid_amount ?? 0;
        $balanceAmount = $netAmount - $paidAmount;
        
        if (isset($validated['paid_amount']) && isset($validated['balance_amount'])) {
            $paidAmount = (float) $validated['paid_amount'];
            $balanceAmount = (float) $validated['balance_amount'];
            
            // Ensure paid_amount + balance_amount = net_amount (handle rounding)
            $total = $paidAmount + $balanceAmount;
            if (abs($total - $netAmount) > 0.01) {
                // Adjust balance to match net amount
                $balanceAmount = $netAmount - $paidAmount;
            }
            
            // Auto-update status based on payment amounts
            if ($balanceAmount <= 0.01) {
                $status = 'paid';
                $paidAmount = $netAmount;
                $balanceAmount = 0;
            } elseif ($paidAmount > 0) {
                $status = 'partial';
            } else {
                $status = 'pending';
            }
        } else {
            // Fallback: use status to determine payment
            if ($status === 'paid') {
                $paidAmount = $netAmount;
                $balanceAmount = 0;
            } elseif ($status === 'pending') {
                $paidAmount = 0;
                $balanceAmount = $netAmount;
            } else {
                // If net amount decreased, adjust paid amount
                if ($netAmount < $oldNetAmount && $paidAmount > $netAmount) {
                    $paidAmount = $netAmount;
                    $balanceAmount = 0;
                } else {
                    $balanceAmount = $netAmount - $paidAmount;
                }
            }
        }

        $salaryPayment->update([
            'project_id' => $validated['project_id'] ?? null,
            'staff_id' => $validated['staff_id'],
            'payment_month' => $paymentMonth,
            'payment_date' => $validated['payment_date'],
            'base_salary' => $validated['base_salary'],
            'working_days' => $validated['working_days'] ?? null,
            'total_days' => $validated['total_days'] ?? null,
            'overtime_amount' => $validated['overtime_amount'] ?? 0,
            'bonus_amount' => $validated['bonus_amount'] ?? 0,
            'allowance_amount' => $validated['allowance_amount'] ?? 0,
            'deduction_amount' => $validated['deduction_amount'] ?? 0,
            'advance_deduction' => $validated['advance_deduction'] ?? 0,
            'assessment_type' => $assessmentType,
            'taxable_income' => $taxableIncome,
            'tax_amount' => $taxAmount,
            'tax_exempt_amount' => 0,
            'gross_amount' => $grossAmount,
            'net_amount' => $netAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $balanceAmount,
            'status' => $status,
            'payment_method' => $validated['payment_method'] ?? null,
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        // Refresh the model to get latest data
        $salaryPayment->refresh();

        // Update status based on payment amounts
        $salaryPayment->updatePaymentStatus();

        // Refresh again after status update
        $salaryPayment->refresh();

        // Create or update expense based on paid amount (for both partial and full payments)
        if ($salaryPayment->paid_amount > 0) {
            // If expense exists, update it; otherwise create new one
            if ($salaryPayment->expense_id) {
                $expense = Expense::find($salaryPayment->expense_id);
                if ($expense) {
                    // Update expense amount to match paid amount (for partial payments)
                    $expense->amount = $salaryPayment->paid_amount;
                    $expense->date = $salaryPayment->payment_date;
                    $expense->payment_method = $salaryPayment->payment_method;
                    $expense->notes = "Transaction Reference: {$salaryPayment->transaction_reference}" . 
                        ($salaryPayment->notes ? " | {$salaryPayment->notes}" : '');
                    
                    // Update description with partial payment info if status is partial
                    $partialInfo = '';
                    if ($salaryPayment->status === 'partial' && $salaryPayment->net_amount > 0) {
                        $percentage = ($salaryPayment->paid_amount / $salaryPayment->net_amount) * 100;
                        $partialInfo = " (Partial: " . number_format($percentage, 1) . "% - Rs. " . number_format($salaryPayment->paid_amount, 2) . " of Rs. " . number_format($salaryPayment->net_amount, 2) . ")";
                    }
                    
                    $expense->description = "Salary payment for {$salaryPayment->payment_month_name} - {$salaryPayment->staff->name}{$partialInfo}" . 
                        ($salaryPayment->notes ? " | Notes: {$salaryPayment->notes}" : '');
                    $expense->updated_by = auth()->id();
                    $expense->save();
                } else {
                    // Expense ID exists but expense was deleted, create new one
                    $this->createExpenseFromSalaryPayment($salaryPayment);
                }
            } else {
                // Create new expense for partial or full payment
                $this->createExpenseFromSalaryPayment($salaryPayment);
            }
        } else {
            // If no amount is paid, delete expense if exists
            if ($salaryPayment->expense_id) {
                $expense = Expense::find($salaryPayment->expense_id);
                if ($expense) {
                    $expense->delete();
                }
                $salaryPayment->expense_id = null;
                $salaryPayment->save();
            }
        }

        $salaryPayment->load(['staff', 'project']);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary payment updated successfully.',
                'payment' => [
                    'id' => $salaryPayment->id,
                    'staff_name' => $salaryPayment->staff->name,
                    'payment_month_name' => $salaryPayment->payment_month_name,
                    'base_salary' => number_format($salaryPayment->base_salary, 2),
                    'gross_amount' => number_format($salaryPayment->gross_amount, 2),
                    'tax_amount' => number_format($salaryPayment->tax_amount ?? 0, 2),
                    'net_amount' => number_format($salaryPayment->net_amount, 2),
                    'status' => $salaryPayment->status,
                    'payment_date' => $salaryPayment->payment_date->format('M d, Y'),
                    'project_id' => $salaryPayment->project_id,
                    'project_name' => $salaryPayment->project ? $salaryPayment->project->name : null,
                    'paid_amount' => number_format($salaryPayment->paid_amount, 2),
                ],
            ]);
        }

        return redirect()->route('admin.salary-payments.index')
            ->with('success', 'Salary payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryPayment $salaryPayment)
    {
        if ($salaryPayment->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }

        // Delete linked expense if exists
        if ($salaryPayment->expense_id) {
            $expense = Expense::find($salaryPayment->expense_id);
            if ($expense) {
                $expense->delete();
            }
        }

        $salaryPayment->delete();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary payment deleted successfully.',
            ]);
        }

        return redirect()->route('admin.salary-payments.index')
            ->with('success', 'Salary payment deleted successfully.');
    }

    /**
     * Get or create salary category.
     */
    private function getOrCreateSalaryCategory($companyId)
    {
        $category = Category::where('company_id', $companyId)
            ->where('type', 'expense')
            ->where(function ($q) {
                $q->where('name', 'like', '%Salary%')
                  ->orWhere('name', 'like', '%Staff%')
                  ->orWhere('name', 'like', '%Payment%');
            })
            ->first();

        if (!$category) {
            $category = Category::create([
                'company_id' => $companyId,
                'name' => 'Salary & Staff Payments',
                'type' => 'expense',
                'is_active' => true,
            ]);
        }

        return $category;
    }

    /**
     * Create expense entry for salary payment.
     * Uses paid_amount for partial payments, net_amount for full payments.
     */
    private function createExpenseFromSalaryPayment(SalaryPayment $salaryPayment)
    {
        // Refresh to get latest data
        $salaryPayment->refresh();
        
        // Only create expense if no expense exists
        if (!$salaryPayment->expense_id) {
            $companyId = $salaryPayment->company_id;
            $category = $this->getOrCreateSalaryCategory($companyId);

            // Get or create Salary expense type
            $expenseType = ExpenseType::firstOrCreate(
                ['name' => 'Salary'],
                ['name' => 'Salary']
            );

            // Load staff relationship
            $salaryPayment->load('staff');
            
            $staffName = $salaryPayment->staff->name ?? 'Unknown';
            $monthName = $salaryPayment->payment_month_name;
            
            // Use paid_amount for expense (for partial payments)
            // If fully paid, paid_amount should equal net_amount
            $expenseAmount = $salaryPayment->paid_amount > 0 ? $salaryPayment->paid_amount : $salaryPayment->net_amount;
            
            // Ensure expense amount is valid
            if ($expenseAmount <= 0) {
                \Log::warning('Cannot create expense: paid_amount is 0 or negative', [
                    'salary_payment_id' => $salaryPayment->id,
                    'paid_amount' => $salaryPayment->paid_amount,
                    'net_amount' => $salaryPayment->net_amount,
                    'status' => $salaryPayment->status,
                ]);
                return false;
            }
            
            // Add partial payment indicator in description
            $partialInfo = '';
            if ($salaryPayment->status === 'partial' && $salaryPayment->net_amount > 0) {
                $percentage = ($salaryPayment->paid_amount / $salaryPayment->net_amount) * 100;
                $partialInfo = " (Partial: " . number_format($percentage, 1) . "% - Rs. " . number_format($salaryPayment->paid_amount, 2) . " of Rs. " . number_format($salaryPayment->net_amount, 2) . ")";
            }

            try {
                $expense = Expense::create([
                    'company_id' => $companyId,
                    'project_id' => $salaryPayment->project_id,
                    'staff_id' => $salaryPayment->staff_id,
                    'category_id' => $category->id,
                    'expense_type_id' => $expenseType->id,
                    'item_name' => "Salary Payment - {$staffName}",
                    'description' => "Salary payment for {$monthName} - {$staffName}{$partialInfo}" . 
                        ($salaryPayment->notes ? " | Notes: {$salaryPayment->notes}" : ''),
                    'amount' => $expenseAmount,
                    'date' => $salaryPayment->payment_date,
                    'payment_method' => $salaryPayment->payment_method,
                    'notes' => ($salaryPayment->transaction_reference ? "Transaction Reference: {$salaryPayment->transaction_reference}" : '') . 
                        ($salaryPayment->notes ? ($salaryPayment->transaction_reference ? " | {$salaryPayment->notes}" : $salaryPayment->notes) : ''),
                    'created_by' => auth()->id(),
                ]);

                $salaryPayment->expense_id = $expense->id;
                $salaryPayment->save();
                
                \Log::info('Expense created from salary payment', [
                    'expense_id' => $expense->id,
                    'salary_payment_id' => $salaryPayment->id,
                    'amount' => $expenseAmount,
                    'status' => $salaryPayment->status,
                    'paid_amount' => $salaryPayment->paid_amount,
                ]);
                
                return true;
            } catch (\Exception $e) {
                \Log::error('Error creating expense: ' . $e->getMessage(), [
                    'salary_payment_id' => $salaryPayment->id,
                    'paid_amount' => $salaryPayment->paid_amount,
                    'status' => $salaryPayment->status,
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }
        
        return false;
    }

    /**
     * Record a partial payment for salary payment.
     */
    public function recordPayment(Request $request, SalaryPayment $salaryPayment)
    {
        if ($salaryPayment->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }

        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $salaryPayment->balance_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Create payment transaction
        SalaryPaymentTransaction::create([
            'salary_payment_id' => $salaryPayment->id,
            'amount' => $validated['payment_amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'] ?? null,
            'bank_account_id' => $validated['bank_account_id'] ?? null,
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Update salary payment amounts
        $newPaidAmount = $salaryPayment->paid_amount + $validated['payment_amount'];
        $newBalanceAmount = $salaryPayment->net_amount - $newPaidAmount;

        $salaryPayment->paid_amount = $newPaidAmount;
        $salaryPayment->balance_amount = $newBalanceAmount;

        // Update status based on payment
        if ($newBalanceAmount <= 0.01) {
            $salaryPayment->status = 'paid';
            $salaryPayment->balance_amount = 0;
            $salaryPayment->paid_amount = $salaryPayment->net_amount;
        } else {
            $salaryPayment->status = 'partial';
        }

        $salaryPayment->save();
        
        // Create or update expense based on paid amount
        if ($salaryPayment->paid_amount > 0) {
            if ($salaryPayment->expense_id) {
                // Update existing expense
                $expense = Expense::find($salaryPayment->expense_id);
                if ($expense) {
                    $expense->amount = $salaryPayment->paid_amount;
                    $expense->date = $validated['payment_date'];
                    $expense->payment_method = $validated['payment_method'] ?? $expense->payment_method;
                    
                    // Update description with partial info if needed
                    $partialInfo = '';
                    if ($salaryPayment->status === 'partial' && $salaryPayment->net_amount > 0) {
                        $percentage = ($salaryPayment->paid_amount / $salaryPayment->net_amount) * 100;
                        $partialInfo = " (Partial: " . number_format($percentage, 1) . "% - Rs. " . number_format($salaryPayment->paid_amount, 2) . " of Rs. " . number_format($salaryPayment->net_amount, 2) . ")";
                    }
                    
                    $expense->description = "Salary payment for {$salaryPayment->payment_month_name} - {$salaryPayment->staff->name}{$partialInfo}" . 
                        ($salaryPayment->notes ? " | Notes: {$salaryPayment->notes}" : '');
                    $expense->notes = "Transaction Reference: {$validated['transaction_reference']}" . 
                        ($validated['notes'] ? " | {$validated['notes']}" : '');
                    $expense->updated_by = auth()->id();
                    $expense->save();
                }
            } else {
                // Create new expense
                $this->createExpenseFromSalaryPayment($salaryPayment);
            }
        }

        return back()->with('success', 'Payment recorded successfully.');
    }
}
