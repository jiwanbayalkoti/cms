<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('expenses', 'loan_id')) {
            return;
        }

        Loan::query()->where('direction', 'repaid')->orderBy('id')->chunkById(100, function ($loans) {
            foreach ($loans as $loan) {
                if (Expense::where('loan_id', $loan->id)->whereNull('loan_payment_id')->exists()) {
                    continue;
                }

                $category = Category::firstOrCreate(
                    [
                        'company_id' => $loan->company_id,
                        'type' => 'expense',
                        'name' => 'Loan Repayment',
                    ],
                    [
                        'description' => 'Loan given/repaid and repayments against taken loans',
                        'is_active' => true,
                    ]
                );

                $party = $loan->party_source ?? $loan->party_name ?? $loan->source ?? 'N/A';

                Expense::create([
                    'company_id' => $loan->company_id,
                    'project_id' => $loan->project_id,
                    'loan_id' => $loan->id,
                    'category_id' => $category->id,
                    'item_name' => 'Loan Repaid (Given)',
                    'description' => "Loan given/repaid — Party: {$party}",
                    'amount' => $loan->amount,
                    'date' => $loan->loan_date,
                    'payment_method' => $loan->payment_method,
                    'notes' => $loan->notes,
                    'created_by' => $loan->created_by,
                ]);
            }
        });

        LoanPayment::query()->with('loan')->orderBy('id')->chunkById(100, function ($payments) {
            foreach ($payments as $payment) {
                $loan = $payment->loan;
                if (! $loan || $loan->direction !== 'received') {
                    continue;
                }
                if (Expense::where('loan_payment_id', $payment->id)->exists()) {
                    continue;
                }

                $category = Category::firstOrCreate(
                    [
                        'company_id' => $loan->company_id,
                        'type' => 'expense',
                        'name' => 'Loan Repayment',
                    ],
                    [
                        'description' => 'Loan given/repaid and repayments against taken loans',
                        'is_active' => true,
                    ]
                );

                $party = $loan->party_source ?? $loan->party_name ?? $loan->source ?? 'N/A';

                Expense::create([
                    'company_id' => $loan->company_id,
                    'project_id' => $loan->project_id,
                    'loan_id' => $loan->id,
                    'loan_payment_id' => $payment->id,
                    'category_id' => $category->id,
                    'item_name' => 'Loan Repayment (Installment)',
                    'description' => "Repayment against loan taken — Party: {$party}",
                    'amount' => $payment->amount,
                    'date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'notes' => $payment->notes,
                    'created_by' => $payment->created_by,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Keep expense rows; reversing would delete user-visible history.
    }
};
