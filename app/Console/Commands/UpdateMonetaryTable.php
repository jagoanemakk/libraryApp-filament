<?php

namespace App\Console\Commands;

use App\Models\Loans;
use App\Models\Monetary;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateMonetaryTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monetary:update-data';

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

        $loansData = Loans::where('due_date', '<=', $today)->get();
        // Log::info($loansData);

        foreach ($loansData as $data) {
            $existLoans = Monetary::where('loans_id', $data->id)->exists();

            if (!$existLoans) {
                Monetary::insert([
                    'user_id' => $data->user_id,
                    'books_id' => $data->books_id,
                    'loans_id' => $data->id,
                    'fee' => 5000,
                    'status' => 'Waiting',
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]);
            }
        }

        $this->info('Fee updated successfully.');
    }
}
