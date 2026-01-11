<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class TransactionComponent extends Component
{
    use WithFileUploads;

    // Form fields
    public $type = 'expense'; // Default to 'expense'
    public $amount;
    public $description;
    public $date;
    public $is_recurring = false; // Default to false
    public $recurring_interval = 'DAILY'; // Default to 'DAILY'
    public $category_id;
    public $account_id;

    // Categories and accounts for dropdowns
    public $categories = [];
    public $accounts = [];

    // Validation rules
    protected $rules = [
        'type' => 'required|in:income,expense', // Must be either 'income' or 'expense'
        'amount' => 'required|numeric|min:0.01', // Must be a positive number
        'description' => 'nullable|string|max:255', // Optional, but must be a string if provided
        'date' => 'required|date|before_or_equal:today', // Must be a valid date and not in the future
        'is_recurring' => 'boolean', // Must be a boolean (true/false)
        'recurring_interval' => 'required_if:is_recurring,true|in:DAILY,WEEKLY,MONTHLY,YEARLY', // Required if recurring
        'category_id' => 'required|exists:categories,id', // Must exist in the categories table
        'account_id' => 'required|exists:accounts,id', // Must exist in the accounts table
    ];

    // Custom error messages
    protected $messages = [
        'type.required' => 'The transaction type is required.',
        'type.in' => 'The transaction type must be either income or expense.',
        'amount.required' => 'The amount is required.',
        'amount.numeric' => 'The amount must be a number.',
        'amount.min' => 'The amount must be at least 0.01.',
        'description.max' => 'The description must not exceed 255 characters.',
        'date.required' => 'The date is required.',
        'date.date' => 'The date must be a valid date.',
        'date.before_or_equal' => 'The date cannot be in the future.',
        'is_recurring.boolean' => 'The recurring field must be true or false.',
        'recurring_interval.required_if' => 'The recurring interval is required when the transaction is recurring.',
        'recurring_interval.in' => 'The recurring interval must be one of: Daily, Weekly, Monthly, Yearly.',
        'category_id.required' => 'The category is required.',
        'category_id.exists' => 'The selected category is invalid.',
        'account_id.required' => 'The account is required.',
        'account_id.exists' => 'The selected account is invalid.',
    ];

    // Save transaction
    public function save()
    {
        // Validate the form fields
        $this->validate();

        // Add custom validation for expense transactions
        if ($this->type === 'expense') {
            $account = Account::find($this->account_id);

            if (!$account) {
                session()->flash('error', 'Selected account not found.');
                return;
            }

            if ($account->balance < $this->amount) {
                // Add a custom validation error
                $this->addError('amount', 'The expense amount cannot exceed the account balance.');
                return;
            }
        }

        try {
            // Update account balance based on transaction type
            $this->updateAccountBalance();

            // Calculate next date only if the transaction is recurring
            $next_date = null;
            if ($this->is_recurring) {
                // Use the selected interval or default to 'DAILY'
                $interval = $this->recurring_interval ?? 'DAILY';
                $next_date = $this->calculateNextDate($this->date, $interval);
            }

            // Create the transaction
            Transaction::create([
                'type' => $this->type,
                'amount' => $this->amount,
                'description' => $this->description,
                'date' => $this->date,
                'is_recurring' => $this->is_recurring,
                'recurring_interval' => $this->is_recurring ? ($this->recurring_interval ?? 'DAILY') : null, // Save interval only if recurring
                'next_date' => $next_date,
                'category_id' => $this->category_id,
                'account_id' => $this->account_id,
                'user_id' => auth()->id(),
            ]);

            // Reset the form after successful transaction creation
            $this->reset(['amount', 'category_id', 'description', 'is_recurring', 'recurring_interval']);
            session()->flash('message', 'Transaction created successfully!');

            return redirect()->route('transactions.index');
        } catch (\Exception $e) {
            Log::error('Transaction creation failed: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while creating the transaction.');
        }
    }

    // Update account balance based on transaction type
    private function updateAccountBalance()
    {
        $account = Account::find($this->account_id);

        if (!$account) {
            throw new \Exception('Selected account not found.');
        }

        if ($this->type === 'expense' && $account->balance < $this->amount) {
            throw new \Exception('Insufficient funds in the selected account.');
        }

        $account->balance += ($this->type === 'income') ? $this->amount : -$this->amount;
        $account->save();
    }

    // Calculate next date for recurring transactions
    private function calculateNextDate($currentDate, $interval)
    {
        $interval = strtolower(trim($interval));
        $date = Carbon::parse($currentDate);

        switch ($interval) {
            case 'daily':
                return $date->addDay()->toDateString();
            case 'weekly':
                return $date->addWeek()->toDateString();
            case 'monthly':
                return $date->addMonth()->toDateString();
            case 'yearly':
                return $date->addYear()->toDateString();
            default:
                throw new InvalidArgumentException("Invalid recurring interval: {$interval}");
        }
    }

    // Fetch categories based on the selected type
    public function updatedType()
    {
        $this->categories = Category::where('type', $this->type)->get();
        $this->category_id = null;
    }

    // Initialize component
    public function mount()
    {
        $user = auth()->user();
        $this->accounts = $user ? Account::where('user_id', $user->id)->get() : collect();
        $this->account_id = session('selected_account', $this->accounts->first()->id ?? null);
        $this->type = 'expense';
        $this->date = now()->toDateString();
        $this->categories = Category::where('type', $this->type)->get();
    }

    public function render()
    {
        return view('livewire.transaction-component', [
            'accounts' => Account::all(),
        ])->layout('layouts.app');
    }
}