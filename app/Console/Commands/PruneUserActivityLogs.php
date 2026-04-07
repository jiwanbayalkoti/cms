<?php

namespace App\Console\Commands;

use App\Models\UserActivityLog;
use Illuminate\Console\Command;

class PruneUserActivityLogs extends Command
{
    protected $signature = 'logs:prune-user-activities';

    protected $description = 'Delete user activity logs older than 30 days';

    public function handle(): int
    {
        $deleted = UserActivityLog::where('created_at', '<', now()->subDays(30))->delete();

        $this->info("Pruned {$deleted} user activity logs older than 30 days.");

        return self::SUCCESS;
    }
}

