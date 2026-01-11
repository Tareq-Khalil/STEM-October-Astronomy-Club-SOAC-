<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Account;
use Carbon\Carbon;

class ProcessRecurringTransactions extends Command
{
    protected $signature = 'transactions:process-recurring';
    protected $description = 'Process recurring transactions and generate new ones based on their intervals.';

    public function handle()
    {
        // Get all recurring transactions whose next_date is today or earlier
        $transactions = Transaction::where('is_recurring', true)
            ->whereNotNull('next_date')
            ->where('next_date', '<=', Carbon::today()->toDateString())
            ->get();

        foreach ($transactions as $transaction) {
            $newDate = $this->calculateNextDate($transaction->next_date, $transaction->recurring_interval);

            // Create a new transaction
            $newTransaction = Transaction::create([
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
                'date' => $transaction->next_date,
                'is_recurring' => true,
                'recurring_interval' => $transaction->recurring_interval,
                'next_date' => $newDate, // Set next recurrence date
                'category_id' => $transaction->category_id,
                'account_id' => $transaction->account_id,
                'user_id' => $transaction->user_id,
            ]);

            // Update account balance
            $account = Account::find($transaction->account_id);
            if ($account) {
                if ($transaction->type === 'expense') {
                    $account->balance -= $transaction->amount;
                } elseif ($transaction->type === 'income') {
                    $account->balance += $transaction->amount;
                }
                $account->save();
            }

            // Update the original transaction's next_date
            $transaction->next_date = $newDate;
            $transaction->save();
        }

        $this->info('Recurring transactions processed successfully.');
    }

    private function calculateNextDate($currentDate, $interval)
    {
        $date = Carbon::parse($currentDate);

        switch (strtolower($interval)) {
            case 'daily':
                return $date->addDay()->toDateString();
            case 'weekly':
                return $date->addWeek()->toDateString();
            case 'monthly':
                return $date->addMonth()->toDateString();
            case 'yearly':
                return $date->addYear()->toDateString();
            default:
                return null;
        }
    }
}
