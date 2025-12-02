<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BillModule;
use App\Models\BillItem;
use App\Models\BillAggregate;
use App\Models\Project;
use App\Models\User;
use App\Models\BillSetting;
use App\Services\BillCalculatorService;
use App\Support\CompanyContext;

class BillModuleSeeder extends Seeder
{
    protected $calculator;

    public function __construct(BillCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    public function run(): void
    {
        $companyId = CompanyContext::getActiveCompanyId() ?? 1;
        
        // Create default settings if not exists
        BillSetting::firstOrCreate(
            ['company_id' => $companyId],
            [
                'tax_rate_default' => 13.0,
                'overhead_default' => 10.0,
                'contingency_default' => 5.0,
                'currency' => 'NPR',
                'work_categories' => BillSetting::getDefaultCategories(),
            ]
        );

        $project = Project::where('company_id', $companyId)->first();
        $user = User::where('company_id', $companyId)->first();

        if (!$project || !$user) {
            $this->command->warn('No project or user found. Please create them first.');
            return;
        }

        $bill = BillModule::create([
            'company_id' => $companyId,
            'project_id' => $project->id,
            'title' => 'Sample Construction Final Bill - Building Foundation',
            'version' => '1.0',
            'created_by' => $user->id,
            'status' => BillModule::STATUS_DRAFT,
            'notes' => 'This is a sample bill module for testing purposes.',
            'mb_number' => 'MB-2024-001',
            'mb_date' => now(),
        ]);

        $items = [
            ['category' => 'Earthwork', 'description' => 'Excavation for foundation', 'uom' => 'Cum', 'quantity' => 150.5, 'unit_rate' => 450.00, 'wastage_percent' => 5, 'tax_percent' => 13],
            ['category' => 'RCC', 'description' => 'RCC M20 for foundation', 'uom' => 'Cum', 'quantity' => 45.0, 'unit_rate' => 8500.00, 'wastage_percent' => 3, 'tax_percent' => 13],
            ['category' => 'Masonry', 'description' => 'Brick work in foundation', 'uom' => 'Cum', 'quantity' => 25.0, 'unit_rate' => 6500.00, 'wastage_percent' => 5, 'tax_percent' => 13],
            ['category' => 'Plaster', 'description' => 'Cement plaster 1:4', 'uom' => 'Sqm', 'quantity' => 500.0, 'unit_rate' => 350.00, 'wastage_percent' => 10, 'tax_percent' => 13],
            ['category' => 'Flooring', 'description' => 'Vitrified tiles flooring', 'uom' => 'Sqm', 'quantity' => 300.0, 'unit_rate' => 850.00, 'wastage_percent' => 5, 'tax_percent' => 13],
            ['category' => 'Doors/Windows', 'description' => 'Wooden door frame', 'uom' => 'Nos', 'quantity' => 12.0, 'unit_rate' => 8500.00, 'wastage_percent' => 0, 'tax_percent' => 13],
            ['category' => 'Electrical', 'description' => 'Electrical wiring and fittings', 'uom' => 'Sqm', 'quantity' => 500.0, 'unit_rate' => 450.00, 'wastage_percent' => 5, 'tax_percent' => 13],
            ['category' => 'Plumbing', 'description' => 'PVC pipes and fittings', 'uom' => 'Rmt', 'quantity' => 200.0, 'unit_rate' => 350.00, 'wastage_percent' => 5, 'tax_percent' => 13],
        ];

        foreach ($items as $index => $itemData) {
            $item = new BillItem($itemData);
            $item->bill_module_id = $bill->id;
            $item->sort_order = $index;
            $this->calculator->calculateItem($item);
            $item->save();
        }

        $this->calculator->calculateAggregate($bill->id, 10.0, 5.0);

        $this->command->info('Sample bill module created with ID: ' . $bill->id);
    }
}
