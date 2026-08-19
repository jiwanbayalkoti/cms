<?php

namespace App\Console\Commands;

use App\Mail\VehicleDueAlert;
use App\Models\Company;
use App\Services\VehicleAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyVehicleDueAlerts extends Command
{
    protected $signature = 'vehicles:notify-due';

    protected $description = 'Email company admins about overdue or upcoming vehicle service and document dates.';

    public function handle(VehicleAlertService $alerts): int
    {
        $sent = 0;

        Company::query()->orderBy('id')->chunkById(50, function ($companies) use ($alerts, &$sent) {
            foreach ($companies as $company) {
                $items = $alerts->items($company->id);
                if ($items->isEmpty()) {
                    continue;
                }

                $admins = $alerts->companyAdmins($company->id);
                $emails = $admins->pluck('email')->filter()->unique()->values();
                if ($emails->isEmpty()) {
                    continue;
                }

                Mail::to($emails->all())->send(new VehicleDueAlert($company, $items));
                $sent++;
            }
        });

        $this->info("Sent vehicle alert emails to {$sent} compan" . ($sent === 1 ? 'y.' : 'ies.'));

        return self::SUCCESS;
    }
}
