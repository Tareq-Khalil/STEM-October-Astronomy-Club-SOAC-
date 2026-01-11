<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeComponent extends Component
{
    public $budget = 1000; // Default budget
    public $spent = 0; // Amount spent
    public $selectedAccount;
    public $accounts;
    public $viewingAccount; 
    public $recentTransactions = [];
    public $budgetPercentage = 0;
    public $newBudget;
    public $isEditing = false;
    public $transactions = [];
    public $expenseData = [];
    protected $listeners = [
        'accountCreated' => 'loadAccounts',
        'transactionCreated' => 'loadBudgetData', // Listen for transaction updates
    ];

    public function resetMonthlyData()
    {
        $today = now();
        if ($today->day === 1) { // Check if today is the 1st of the month
            // Reset the budget and expense data
            $this->spent = 0;
            $this->budget = 1000; // Reset to default budget or fetch from account settings
            $this->expenseData = [];
            $this->loadBudgetData();
            $this->loadExpenseData();
        }
    }
    
    public function mount()
    {
        $user = Auth::user();
    
        if (!$user) {
            $this->accounts = collect();
            return;
        }
    
        $this->loadAccounts(); // Load only accounts with transactions
    
        if (session()->has('selected_account') && $this->accounts->contains('id', session('selected_account'))) {
            $this->selectedAccount = session('selected_account');
        } else {
            $this->selectedAccount = $this->accounts->isNotEmpty() ? $this->accounts->first()->id : null;
        }
    
        // Ensure viewingAccount is set to selectedAccount
        $this->viewingAccount = $this->selectedAccount;
    
        $this->resetMonthlyData();
        $this->loadExpenseData();
        $this->loadTransactions();
        $this->loadBudgetData();
    }

    
public function loadExpenseData()
{
    if (!$this->viewingAccount) {
        $this->expenseData = [];
        return;
    }

    // Fetch categories with transactions for the selected account and current month
    $categories = Category::whereHas('transactions', function ($query) {
        $query->where('account_id', $this->viewingAccount)
              ->where('type', 'expense')
              ->whereMonth('created_at', now()->month);
    })->with(['transactions' => function ($query) {
        $query->where('account_id', $this->viewingAccount)
              ->where('type', 'expense')
              ->whereMonth('created_at', now()->month);
    }])->get();

    $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFA07A', '#FFD700', '#8A2BE2', '#20B2AA'];

    $this->expenseData = $categories->map(function ($category, $index) use ($colors) {
        return [
            'name' => $category->name,
            'total' => $category->transactions->sum('amount'),
            'color' => $colors[$index % count($colors)],
        ];
    })->toArray();

    // Debugging: Log the expense data
    Log::info("Expense Data:", $this->expenseData);

    // Dispatch Livewire event to update the chart
    $this->dispatch('updateChart', expenseData: $this->expenseData);
} 
    
    public function updatedViewingAccount($value)
    {
        $this->loadTransactions();
        $this->loadExpenseData(); 
    }
    public function loadAccounts()
    {
        $this->accounts = Account::where('user_id', Auth::id())
           // Only fetch accounts that have transactions
            ->get() ?? collect();
    }
    

    public function loadTransactions()
    {
        if (!$this->viewingAccount) {
            $this->recentTransactions = collect();
            return;
        }
        
        // Fetch transactions for the selected account after the 1st of the current month
        $this->recentTransactions = Transaction::where('account_id', $this->viewingAccount)
            ->whereDate('created_at', '>=', now()->startOfMonth())
            ->latest()
            ->take(5)
            ->get();
    }
    
    public function loadBudgetData()
    {
        if (!$this->selectedAccount) {
            return;
        }

        $account = Account::find($this->selectedAccount);

        if ($account) {
            $this->spent = $account->transactions()->where('type', 'expense')->sum('amount'); 
            $this->budget = $account->budget;
            $this->newBudget = $account->budget;
        } else {
            $this->spent = 0;
            $this->budget = 1; // Set a minimal budget to avoid division by zero
            $this->newBudget = 1;
        }

        // Prevent division by zero & handle overflow
        $this->budgetPercentage = min(100, $this->budget > 0 ? round(($this->spent / $this->budget) * 100, 1) : 0);
    }

    public function editBudget()
    {
        $this->isEditing = true;
    }
    
    public function updateBudget()
    {
        Log::info("Updating Budget: " . json_encode($this->newBudget));

        if (!$this->selectedAccount) {
            Log::error("No account selected!");
            session()->flash('error', 'No account selected.');
            return;
        }

        Log::info("Selected Account ID: " . $this->selectedAccount);

        $account = Account::find($this->selectedAccount);

        if ($account) {
            if (!is_numeric($this->newBudget) || $this->newBudget <= 0) {
                session()->flash('error', 'Invalid budget amount.');
                return;
            }
            
            $account->budget = $this->newBudget;
            $account->save();
            $account->refresh(); // Ensure database update is applied

            $this->budget = $this->newBudget;
            $this->isEditing = false;
            $this->loadBudgetData();

            session()->flash('message', 'Budget updated successfully!');
        }
    }
    
    public function cancelEdit()
    {
        $this->isEditing = false;
    }
    public function selectAccount($accountId)
    {
        $this->selectedAccount = $accountId;
        $this->viewingAccount = $accountId; // Ensure viewingAccount is updated
        
        // Save the selected account in the session
        session(['selected_account' => $accountId]);
        
        // Reload data without a full page refresh
        $this->loadBudgetData(); 
        $this->loadTransactions(); 
        $this->loadExpenseData(); // Ensure this is called to update the chart data
        
        // Dispatch a Livewire event to update the chart
        $this->dispatch('updateChart', expenseData: $this->expenseData);
        $this->dispatch('refreshpage');
    }
    public function updatedSelectedAccount()
{
    $this->viewingAccount = $this->selectedAccount; // Ensure viewingAccount is updated
    session(['selected_account' => $this->selectedAccount]); // Session me save karein
    $this->loadTransactions(); // Transactions reload karein
    $this->loadBudgetData(); // Budget data bhi update karein (optional)
    $this->loadExpenseData();
}
    public function goToAccount($accountId)
    {
        return redirect()->route('accounts.show', $accountId);
    }
    public function toggleAccount($accountId)
{
    $this->selectedAccount = $accountId;
    $this->viewingAccount = $accountId; // Ensure viewingAccount is updated
    
    // Save the selected account in the session
    session(['selected_account' => $accountId]);
    
    // Reload data without a full page refresh
    $this->loadBudgetData(); 
    $this->loadTransactions(); 
    $this->loadExpenseData(); // Ensure this is called to update the chart data
    
    // Dispatch a Livewire event to update the chart
    $this->dispatch('updateChart', expenseData: $this->expenseData);
}
    
    public function render()
    {
        

        return view('livewire.home-component', [
            'accounts' => $this->accounts,
            'recentTransactions' => $this->recentTransactions,
            'expenseData' => $this->expenseData,  // Pass expense data to the view for the pie chart
            'budgetPercentage' => $this->budgetPercentage,
            'budget' => $this->budget,
            'spent' => $this->spent,
            ])->layout('layouts.app');
    }
}
