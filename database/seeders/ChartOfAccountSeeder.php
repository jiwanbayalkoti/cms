<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;
use App\Support\CompanyContext;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyId = CompanyContext::getActiveCompanyId() ?? 1;
        
        // Check if accounts already exist for this company
        if (ChartOfAccount::where('company_id', $companyId)->exists()) {
            $this->command->info('Chart of Accounts already exists for this company. Skipping...');
            return;
        }

        $accounts = [
            // ASSETS (1xxx)
            ['code' => '1000', 'name' => 'ASSETS', 'type' => 'asset', 'category' => null, 'level' => 1, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 1],
            
            // Current Assets (11xx)
            ['code' => '1100', 'name' => 'CURRENT ASSETS', 'type' => 'asset', 'category' => 'current_asset', 'parent' => '1000', 'level' => 2, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 2],
            ['code' => '1101', 'name' => 'Cash in Hand', 'type' => 'asset', 'category' => 'current_asset', 'parent' => '1100', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 3],
            ['code' => '1102', 'name' => 'Bank Accounts', 'type' => 'asset', 'category' => 'current_asset', 'parent' => '1100', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 4],
            ['code' => '1103', 'name' => 'Accounts Receivable', 'type' => 'asset', 'category' => 'current_asset', 'parent' => '1100', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 5],
            ['code' => '1104', 'name' => 'Inventory', 'type' => 'asset', 'category' => 'current_asset', 'parent' => '1100', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 6],
            ['code' => '1105', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'category' => 'current_asset', 'parent' => '1100', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 7],
            ['code' => '1106', 'name' => 'Advance to Suppliers', 'type' => 'asset', 'category' => 'current_asset', 'parent' => '1100', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 8],
            
            // Fixed Assets (12xx)
            ['code' => '1200', 'name' => 'FIXED ASSETS', 'type' => 'asset', 'category' => 'fixed_asset', 'parent' => '1000', 'level' => 2, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 9],
            ['code' => '1201', 'name' => 'Land', 'type' => 'asset', 'category' => 'fixed_asset', 'parent' => '1200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 10],
            ['code' => '1202', 'name' => 'Building', 'type' => 'asset', 'category' => 'fixed_asset', 'parent' => '1200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 11],
            ['code' => '1203', 'name' => 'Plant & Machinery', 'type' => 'asset', 'category' => 'fixed_asset', 'parent' => '1200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 12],
            ['code' => '1204', 'name' => 'Vehicles', 'type' => 'asset', 'category' => 'fixed_asset', 'parent' => '1200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 13],
            ['code' => '1205', 'name' => 'Furniture & Fixtures', 'type' => 'asset', 'category' => 'fixed_asset', 'parent' => '1200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 14],
            ['code' => '1206', 'name' => 'Accumulated Depreciation', 'type' => 'asset', 'category' => 'fixed_asset', 'parent' => '1200', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 15],
            
            // LIABILITIES (2xxx)
            ['code' => '2000', 'name' => 'LIABILITIES', 'type' => 'liability', 'category' => null, 'level' => 1, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 16],
            
            // Current Liabilities (21xx)
            ['code' => '2100', 'name' => 'CURRENT LIABILITIES', 'type' => 'liability', 'category' => 'current_liability', 'parent' => '2000', 'level' => 2, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 17],
            ['code' => '2101', 'name' => 'Accounts Payable', 'type' => 'liability', 'category' => 'current_liability', 'parent' => '2100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 18],
            ['code' => '2102', 'name' => 'Short-term Loans', 'type' => 'liability', 'category' => 'current_liability', 'parent' => '2100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 19],
            ['code' => '2103', 'name' => 'Tax Payable', 'type' => 'liability', 'category' => 'current_liability', 'parent' => '2100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 20],
            ['code' => '2104', 'name' => 'Accrued Expenses', 'type' => 'liability', 'category' => 'current_liability', 'parent' => '2100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 21],
            ['code' => '2105', 'name' => 'Advance from Customers', 'type' => 'liability', 'category' => 'current_liability', 'parent' => '2100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 22],
            
            // Long-term Liabilities (22xx)
            ['code' => '2200', 'name' => 'LONG-TERM LIABILITIES', 'type' => 'liability', 'category' => 'long_term_liability', 'parent' => '2000', 'level' => 2, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 23],
            ['code' => '2201', 'name' => 'Long-term Loans', 'type' => 'liability', 'category' => 'long_term_liability', 'parent' => '2200', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 24],
            
            // EQUITY (3xxx)
            ['code' => '3000', 'name' => 'EQUITY', 'type' => 'equity', 'category' => null, 'level' => 1, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 25],
            ['code' => '3100', 'name' => 'CAPITAL', 'type' => 'equity', 'category' => 'capital', 'parent' => '3000', 'level' => 2, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 26],
            ['code' => '3101', 'name' => 'Share Capital', 'type' => 'equity', 'category' => 'capital', 'parent' => '3100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 27],
            ['code' => '3102', 'name' => 'Retained Earnings', 'type' => 'equity', 'category' => 'retained_earnings', 'parent' => '3100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 28],
            ['code' => '3103', 'name' => 'Current Year Profit/Loss', 'type' => 'equity', 'category' => 'retained_earnings', 'parent' => '3100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 29],
            
            // REVENUE (4xxx)
            ['code' => '4000', 'name' => 'REVENUE', 'type' => 'revenue', 'category' => null, 'level' => 1, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 30],
            ['code' => '4100', 'name' => 'OPERATING REVENUE', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent' => '4000', 'level' => 2, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 31],
            ['code' => '4101', 'name' => 'Sales Revenue', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent' => '4100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 32],
            ['code' => '4102', 'name' => 'Service Revenue', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent' => '4100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 33],
            ['code' => '4103', 'name' => 'Construction Revenue', 'type' => 'revenue', 'category' => 'operating_revenue', 'parent' => '4100', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 34],
            ['code' => '4200', 'name' => 'OTHER REVENUE', 'type' => 'revenue', 'category' => 'other_revenue', 'parent' => '4000', 'level' => 2, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 35],
            ['code' => '4201', 'name' => 'Interest Income', 'type' => 'revenue', 'category' => 'other_revenue', 'parent' => '4200', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 36],
            ['code' => '4202', 'name' => 'Other Income', 'type' => 'revenue', 'category' => 'other_revenue', 'parent' => '4200', 'level' => 3, 'balance_type' => 'credit', 'is_system' => true, 'display_order' => 37],
            
            // EXPENSES (5xxx)
            ['code' => '5000', 'name' => 'EXPENSES', 'type' => 'expense', 'category' => null, 'level' => 1, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 38],
            ['code' => '5100', 'name' => 'OPERATING EXPENSES', 'type' => 'expense', 'category' => 'operating_expense', 'parent' => '5000', 'level' => 2, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 39],
            ['code' => '5101', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'category' => 'operating_expense', 'parent' => '5100', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 40],
            ['code' => '5102', 'name' => 'Material Costs', 'type' => 'expense', 'category' => 'operating_expense', 'parent' => '5100', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 41],
            ['code' => '5103', 'name' => 'Labor Costs', 'type' => 'expense', 'category' => 'operating_expense', 'parent' => '5100', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 42],
            ['code' => '5200', 'name' => 'ADMINISTRATIVE EXPENSES', 'type' => 'expense', 'category' => 'administrative_expense', 'parent' => '5000', 'level' => 2, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 43],
            ['code' => '5201', 'name' => 'Salaries & Wages', 'type' => 'expense', 'category' => 'administrative_expense', 'parent' => '5200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 44],
            ['code' => '5202', 'name' => 'Rent Expense', 'type' => 'expense', 'category' => 'administrative_expense', 'parent' => '5200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 45],
            ['code' => '5203', 'name' => 'Utilities', 'type' => 'expense', 'category' => 'administrative_expense', 'parent' => '5200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 46],
            ['code' => '5204', 'name' => 'Office Supplies', 'type' => 'expense', 'category' => 'administrative_expense', 'parent' => '5200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 47],
            ['code' => '5205', 'name' => 'Depreciation Expense', 'type' => 'expense', 'category' => 'administrative_expense', 'parent' => '5200', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 48],
            ['code' => '5300', 'name' => 'FINANCIAL EXPENSES', 'type' => 'expense', 'category' => 'financial_expense', 'parent' => '5000', 'level' => 2, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 49],
            ['code' => '5301', 'name' => 'Interest Expense', 'type' => 'expense', 'category' => 'financial_expense', 'parent' => '5300', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 50],
            ['code' => '5302', 'name' => 'Bank Charges', 'type' => 'expense', 'category' => 'financial_expense', 'parent' => '5300', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 51],
            ['code' => '5400', 'name' => 'OTHER EXPENSES', 'type' => 'expense', 'category' => 'other_expense', 'parent' => '5000', 'level' => 2, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 52],
            ['code' => '5401', 'name' => 'Miscellaneous Expenses', 'type' => 'expense', 'category' => 'other_expense', 'parent' => '5400', 'level' => 3, 'balance_type' => 'debit', 'is_system' => true, 'display_order' => 53],
        ];

        $parentMap = [];
        
        foreach ($accounts as $accountData) {
            $parentCode = $accountData['parent'] ?? null;
            $parentId = $parentCode ? ($parentMap[$parentCode] ?? null) : null;
            
            $account = ChartOfAccount::create([
                'company_id' => $companyId,
                'account_code' => $accountData['code'],
                'account_name' => $accountData['name'],
                'account_type' => $accountData['type'],
                'account_category' => $accountData['category'],
                'parent_account_id' => $parentId,
                'level' => $accountData['level'],
                'balance_type' => $accountData['balance_type'],
                'is_active' => true,
                'is_system' => $accountData['is_system'],
                'display_order' => $accountData['display_order'],
                'opening_balance' => 0,
            ]);
            
            $parentMap[$accountData['code']] = $account->id;
        }

        $this->command->info('Chart of Accounts seeded successfully for company ID: ' . $companyId);
    }
}
