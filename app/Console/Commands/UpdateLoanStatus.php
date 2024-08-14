<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loans;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class UpdateLoanStatus extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:loan-status';

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

        try {
            $expiryLoans = Loans::where('due_date', '<=', $today)->where('loan_status', '=', null)->get();
            $todayLoans = Loans::where('due_date', '=', $today)->where('loan_status', '=', null)->get();

            foreach ($expiryLoans as $expiryLoan) {
                //Update each ex$expiryLoan as you want to
                $expiryLoan->loan_status = 'Expired';
                $expiryLoan->update();
            }

            foreach ($todayLoans as $todayLoan) {
                //Update each ex$expiryLoan as you want to
                $todayLoan->loan_status = 'Today';
                $todayLoan->update();
            }

            DB::commit();
            $this->info('Loan statuses updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}
