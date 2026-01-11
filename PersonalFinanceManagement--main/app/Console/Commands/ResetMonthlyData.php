<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use Carbon\Carbon;

class ResetMonthlyData extends Command
{
    protected $signature = 'data:reset-monthly';
    protected $description = 'Reset monthly budget and expense data on the 1st of every month.';

    public function handle()
    {
        $today = Carbon::today();
        if ($today->day === 1) { // Check if today is the 1st of the month
            // Reset budget and expense data for all accounts
            $accounts = Account::all();
            foreach ($accounts as $account) {
                $account->budget = 1000; // Reset to default budget or fetch from settings
                $account->save();
            }

            $this->info('Monthly data reset successfully.');
        } else {
            $this->info('Today is not the 1st of the month. No action taken.');
        }
    }
}