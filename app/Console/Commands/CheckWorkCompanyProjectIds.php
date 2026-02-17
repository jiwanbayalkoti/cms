<?php

namespace App\Console\Commands;

use App\Models\CompletedWorkRecord;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckWorkCompanyProjectIds extends Command
{
    protected $signature = 'work:check-company-project-ids {--fix : Fix missing company_id where possible}';
    protected $description = 'Check and optionally fix company_id and project_id for records from yesterday and today (completed work, incomes, expenses)';

    public function handle(): int
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $today = Carbon::today()->toDateString();
        $fix = (bool) $this->option('fix');

        $this->info("Checking records for dates: {$yesterday}, {$today}");
        $this->newLine();

        $hasIssues = false;

        // Completed work records (record_date)
        $cwrMissingCompany = CompletedWorkRecord::whereIn('record_date', [$yesterday, $today])
            ->whereNull('company_id')
            ->get();
        $cwrMissingProject = CompletedWorkRecord::whereIn('record_date', [$yesterday, $today])
            ->whereNull('project_id')
            ->count();
        $cwrTotal = CompletedWorkRecord::whereIn('record_date', [$yesterday, $today])->count();

        if ($cwrTotal > 0) {
            $this->info('Completed Work Records (record_date = yesterday/today):');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total records', $cwrTotal],
                    ['Missing company_id', $cwrMissingCompany->count()],
                    ['Missing project_id (optional)', $cwrMissingProject],
                ]
            );
            if ($cwrMissingCompany->isNotEmpty()) {
                $hasIssues = true;
                foreach ($cwrMissingCompany as $r) {
                    $companyId = $r->work?->company_id ?? null;
                    if ($fix && $companyId) {
                        $r->update(['company_id' => $companyId]);
                        $this->line("  [FIXED] CompletedWorkRecord id={$r->id} -> company_id={$companyId}");
                    } else {
                        $this->line("  id={$r->id} record_date={$r->record_date} company_id=null" . ($companyId ? ' (can fix from work)' : ''));
                    }
                }
            }
            if ($cwrMissingProject > 0) {
                $this->line("  -> {$cwrMissingProject} record(s) have no project_id (optional; set when project is selected in app).");
            }
            $this->newLine();
        }

        // Incomes (date)
        $incMissingCompany = Income::whereIn('date', [$yesterday, $today])->whereNull('company_id')->count();
        $incMissingProject = Income::whereIn('date', [$yesterday, $today])->whereNull('project_id')->count();
        $incTotal = Income::whereIn('date', [$yesterday, $today])->count();
        if ($incTotal > 0) {
            $this->info('Incomes (date = yesterday/today):');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total records', $incTotal],
                    ['Missing company_id', $incMissingCompany],
                    ['Missing project_id (optional)', $incMissingProject],
                ]
            );
            if ($incMissingCompany > 0) {
                $hasIssues = true;
                $this->warn('  Incomes with null company_id cannot be auto-fixed; set company in app or use app:backfill-company.');
            }
            $this->newLine();
        }

        // Expenses (date)
        $expMissingCompany = Expense::whereIn('date', [$yesterday, $today])->whereNull('company_id')->count();
        $expMissingProject = Expense::whereIn('date', [$yesterday, $today])->whereNull('project_id')->count();
        $expTotal = Expense::whereIn('date', [$yesterday, $today])->count();
        if ($expTotal > 0) {
            $this->info('Expenses (date = yesterday/today):');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total records', $expTotal],
                    ['Missing company_id', $expMissingCompany],
                    ['Missing project_id (optional)', $expMissingProject],
                ]
            );
            if ($expMissingCompany > 0) {
                $hasIssues = true;
                $this->warn('  Expenses with null company_id cannot be auto-fixed; set company in app or use app:backfill-company.');
            }
            $this->newLine();
        }

        if (!$cwrTotal && !$incTotal && !$expTotal) {
            $this->info('No records found for yesterday or today.');
            return self::SUCCESS;
        }

        if ($hasIssues && !$fix) {
            $this->warn('Run with --fix to fix missing company_id on completed work where possible.');
        }
        $this->info('Done.');
        return self::SUCCESS;
    }
}
