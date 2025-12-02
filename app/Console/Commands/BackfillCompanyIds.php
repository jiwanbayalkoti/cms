<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Staff;
use App\Models\Position;

class BackfillCompanyIds extends Command
{
    protected $signature = 'app:backfill-company {company_id} {--dry-run}';
    protected $description = 'Backfill null company_id for incomes and expenses to the given company_id';

    public function handle(): int
    {
        $companyId = (int) $this->argument('company_id');
        $dry = (bool) $this->option('dry-run');

        $incomeCount = Income::whereNull('company_id')->count();
        $expenseCount = Expense::whereNull('company_id')->count();
        $categoryCount = Category::whereNull('company_id')->count();
        $subcategoryCount = Subcategory::whereNull('company_id')->count();
        $staffCount = Staff::whereNull('company_id')->count();
        $positionCount = Position::whereNull('company_id')->count();

        $this->info("Will backfill -> incomes: {$incomeCount}, expenses: {$expenseCount}, categories: {$categoryCount}, subcategories: {$subcategoryCount}, staff: {$staffCount}, positions: {$positionCount} to company_id={$companyId}");

        if ($dry) {
            $this->info('Dry run complete.');
            return self::SUCCESS;
        }

        Income::whereNull('company_id')->update(['company_id' => $companyId]);
        Expense::whereNull('company_id')->update(['company_id' => $companyId]);
        Category::whereNull('company_id')->update(['company_id' => $companyId]);
        Subcategory::whereNull('company_id')->update(['company_id' => $companyId]);
        Staff::whereNull('company_id')->update(['company_id' => $companyId]);
        Position::whereNull('company_id')->update(['company_id' => $companyId]);

        $this->info('Backfill complete.');
        return self::SUCCESS;
    }
}


