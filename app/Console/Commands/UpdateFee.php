<?php

namespace App\Console\Commands;

use App\Models\Monetary;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monetary:update-fee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');
        $fee = 5000;

        $monetaryData = Monetary::where('status', 'Waiting')->get();

        foreach ($monetaryData as $data) {
            $due_date = $data->loans->due_date;
            $countDays = $due_date->diffInDays($today);

            $totalFee = $countDays * $fee;

            if ($monetaryData) {
                Monetary::where('id', $data->id)->update([
                    'fee' => $totalFee,
                ]);
            }
        }
    }
}
