<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\CompanyBackupService;
use App\Services\CompanyDataScopeService;
use Illuminate\Console\Command;

class BackupCompaniesMonthly extends Command
{
    protected $signature = 'companies:backup-monthly {--company= : Backup only this company ID}';

    protected $description = 'Create a monthly backup ZIP for each company in its own folder under company_backups/.';

    public function handle(CompanyBackupService $backupService, CompanyDataScopeService $scopeService): int
    {
        $companyId = $this->option('company');
        $query = Company::query()->orderBy('id');

        if ($companyId !== null && $companyId !== '') {
            $query->where('id', (int) $companyId);
        }

        $companies = $query->get();
        if ($companies->isEmpty()) {
            $this->warn('No companies found to back up.');

            return self::SUCCESS;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($companies as $company) {
            try {
                $result = $backupService->runBackup(
                    $company,
                    $scopeService->buildPlan((int) $company->id),
                    'scheduled'
                );
                $successCount++;
                $this->info("Backed up {$company->name} -> {$result['relative']}");
            } catch (\Throwable $e) {
                $failCount++;
                $this->error("Failed {$company->name}: {$e->getMessage()}");
                report($e);
            }
        }

        $this->info("Monthly backup finished. Success: {$successCount}, failed: {$failCount}.");

        return $failCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
