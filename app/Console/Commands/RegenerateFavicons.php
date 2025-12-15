<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Company;
use App\Services\FaviconGeneratorService;
use Illuminate\Support\Facades\Storage;

class RegenerateFavicons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'favicons:regenerate {--company-id= : Regenerate for specific company ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate favicons for all companies or a specific company';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->option('company-id');
        $faviconService = app(FaviconGeneratorService::class);
        $storage = Storage::disk('public');
        
        if ($companyId) {
            $companies = Company::where('id', $companyId)->get();
        } else {
            $companies = Company::all();
        }
        
        $this->info("Regenerating favicons for " . $companies->count() . " companies...");
        
        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();
        
        foreach ($companies as $company) {
            try {
                // Delete old favicon if exists
                if ($company->favicon && $storage->exists($company->favicon)) {
                    $storage->delete($company->favicon);
                }
                
                // Generate new favicon
                $faviconPath = $faviconService->generateDefaultFavicon($company->name);
                
                // Verify and update
                if ($storage->exists($faviconPath)) {
                    $company->update(['favicon' => $faviconPath]);
                    $this->line("\n✓ Generated favicon for: {$company->name}");
                } else {
                    $this->error("\n✗ Failed to generate favicon for: {$company->name}");
                }
            } catch (\Exception $e) {
                $this->error("\n✗ Error for {$company->name}: " . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Favicon regeneration completed!");
        
        return Command::SUCCESS;
    }
}
