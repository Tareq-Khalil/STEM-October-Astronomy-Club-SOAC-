<?php
namespace App\Livewire;
use Livewire\Component;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;

class EditTransactionComponent extends Component
{
    public $transactionId;
    public $transaction;
    public $type;
    public $amount;
    public $account_id;
    public $category_id;
    public $date;
    public $description;
    public $is_recurring = false;
    public $recurring_interval;

    public $accounts;
    public $categories;
    public $account;

    public function mount($transactionId)
    {
        $this->transaction = Transaction::findOrFail($transactionId);
        $this->account = $this->transaction->account;

        $this->type = $this->transaction->type;
        $this->amount = $this->transaction->amount;
        $this->account_id = $this->transaction->account_id;
        $this->category_id = $this->transaction->category_id;
        $this->date = $this->transaction->date;
        $this->description = $this->transaction->description;
        $this->is_recurring = $this->transaction->is_recurring;
        $this->recurring_interval = $this->transaction->recurring_interval;

        $this->accounts = Account::all();
        $this->categories = Category::all();
    }

    public function hasChanges()
    {
        return $this->type != $this->transaction->type ||
               $this->amount != $this->transaction->amount ||
               $this->account_id != $this->transaction->account_id ||
               $this->category_id != $this->transaction->category_id ||
               $this->date != $this->transaction->date ||
               $this->description != $this->transaction->description ||
               $this->is_recurring != $this->transaction->is_recurring ||
               $this->recurring_interval != $this->transaction->recurring_interval;
    }

    public function update()
    {
        // Validate the form data
        $this->validate([
            'type' => 'required|in:expense,income',
            'amount' => 'required|numeric',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_interval' => 'nullable|in:DAILY,WEEKLY,MONTHLY,YEARLY',
        ]);
    
        // Check if there are any changes
        if (!$this->hasChanges()) {
            session()->flash('error', 'No changes were made.');
            return;
        }
    
        // Fetch the original transaction data before updating
        $oldAmount = $this->transaction->amount;
        $oldType = $this->transaction->type;
        $account = $this->transaction->account;
    
        // Reverse the old transaction effect on the account balance
        if ($oldType === 'expense') {
            $account->balance += $oldAmount; // Add back old expense
        } elseif ($oldType === 'income') {
            $account->balance -= $oldAmount; // Subtract old income
        }
    
        // Apply the new transaction effect
        if ($this->type === 'expense') {
            $account->balance -= $this->amount; // Subtract new expense
        } elseif ($this->type === 'income') {
            $account->balance += $this->amount; // Add new income
        }
    
        // Save the updated balance
        $account->save();
    
        // Update the transaction
        $this->transaction->update([
            'type' => $this->type,
            'amount' => $this->amount,
            'account_id' => $this->account_id,
            'category_id' => $this->category_id,
            'date' => $this->date,
            'description' => $this->description,
            'is_recurring' => $this->is_recurring,
            'recurring_interval' => $this->recurring_interval,
        ]);
    
        // Flash success message
        session()->flash('message', 'Transaction updated successfully.');
    
        // Redirect to the account's show page
        return redirect()->route('accounts.show', ['account' => $this->account->id]);
    }
    

    public function render()
    {
        return view('livewire.edit-transaction-component', [
            'account' => $this->account, // Pass the account to the view
        ])->layout('layouts.app');
    }
}