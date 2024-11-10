<?php

namespace App\Console;

use App\Console\Commands\UpdateLoanStatus;
use App\Console\Commands\UpdateMonetaryTable;
use App\Models\Loans;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        // 'App\Console\Commands\UpdateLoanStatus',
        UpdateLoanStatus::class,
        UpdateMonetaryTable::class,
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('optimize:clear');

        $schedule->command('loans:loan-status')->timezone('Asia/Jakarta');
        $schedule->command('monetary:update-data')->timezone('Asia/Jakarta');
        // $schedule->command('monetary:update-fee');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
