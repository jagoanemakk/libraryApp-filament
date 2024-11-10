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

        DB::beginTransaction();

        $loansData = Loans::where('due_date', '<', $today)->get();

        try {
            foreach ($loansData as $data) {

                $due_date = $data->due_date;
                $date_formatted = Carbon::parse($due_date);
                $countDays = $date_formatted->diffInDays(Carbon::now()->format('Y-m-d'));

                $totalFee = $countDays * 5000;

                $existLoans = Monetary::where('loans_id', $data->id)->exists();
                if (!$existLoans) {
                    Monetary::insert([
                        'user_id' => $data->user_id,
                        'books_id' => $data->books_id,
                        'loans_id' => $data->id,
                        'fee' => $totalFee,
                        'status' => 'Waiting',
                        'updated_at' => $today
                    ]);
                } else if ($data->monetaries->status != 'Paid') {
                    Monetary::where('loans_id', $data->id)->update([
                        'fee' => $totalFee,
                        // 'status' => 'Waiting',
                        'updated_at' => $today
                    ]);
                }
            }

            DB::commit();
            $this->info('Monetary updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
        }

        // $this->info('Fee updated successfully.');
    }
}
