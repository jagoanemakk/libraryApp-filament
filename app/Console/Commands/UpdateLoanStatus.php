<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loans;
use App\Models\Monetary;
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

        // DB::beginTransaction();

        // try {
        $loans = Loans::whereNull('deletes_by')->get();

        foreach ($loans as $loan) {
            if ($loan->due_date < $today) {
                $loan->loan_status = 'Expired';
                $loan->updated_at = $today;
                $loan->update();
            } else if ($loan->due_date == $today) {
                $loan->loan_status = 'Today';
                $loan->updated_at = $today;
                $loan->update();
            }
        }

        // $loan->updated_at = $today;
        // $loan->update();
        // $expiredLoans = Loans::where('due_date', '<', $today)->get();
        // $todayLoans = Loans::where('due_date', $today)->get();

        // // if ($expiredLoans->loan_status == NULL) {
        // foreach ($expiredLoans as $expired) {
        //     //Update each ex$expired as you want to
        //     if ($expired->deletes_by === NULL) {
        //         $expired->loan_status = 'Expired';
        //         $expired->updated_at = $today;
        //         $expired->update();
        //     } else {
        //         $expired->updated_at = $today;
        //         $expired->update();
        //     }
        // }

        // foreach ($todayLoans as $todayLoan) {
        //     //Update each ex$expiryLoan as you want to
        //     if ($todayLoan->deletes_by === NULL) {
        //         $todayLoan->loan_status = 'Today';
        //         $todayLoan->updated_at = $today;
        //         $todayLoan->update();
        //     } else {
        //         $expired->updated_at = $today;
        //         $expired->update();
        //     }
        // }

        // DB::commit();
        $this->info('Loan statuses updated successfully.');
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     $this->error('An error occurred: ' . $e->getMessage());
        // }
    }
}
