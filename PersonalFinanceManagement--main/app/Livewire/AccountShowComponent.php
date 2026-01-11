<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileDownloads;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;

class AccountShowComponent extends Component
{
    use WithPagination;

    public $customStartDate;
    public $customEndDate;
    public $openPopupId = null;
    public $totalIncome = 0;
    public $totalExpenses = 0;
    public $net = 0;
    public $accountId;
    public $account;
    public $search = '';
    public $filterType = 'all';
    public $filterTransaction = 'all';
    public $page = 1;
    public $isFilterTypeDropdownOpen = false;
    public $isFilterTransactionDropdownOpen = false;
    public $selectedPeriod = '7'; // Default to "Last 7 Days"
    public $chartData = [
        'dates' => [],
        'income' => [],
        'expense' => [],
    ];
    public $dropdownOpen = false;
    public $selectedTransactions = [];
    protected $paginationTheme = 'tailwind';
    public $selectAll = false;
    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => 'all'],
        'filterTransaction' => ['except' => 'all'],
        'selectedPeriod' => ['except' => '7'],
        'page' => ['except' => 1],
    ];
    public $reportDropdownOpen = false;

    public function toggleReportDropdown()
    {
        $this->reportDropdownOpen = !$this->reportDropdownOpen;
    }

    public function downloadReport($period)
    {
        $this->reportDropdownOpen = false;
    
        // Validate custom period
        if ($period === 'custom' && (!$this->customStartDate || !$this->customEndDate)) {
            session()->flash('error', 'Please select both start and end dates for the custom period.');
            return;
        }
    
        // Define date range
        $endDate = now();
        $startDate = match ($period) {
            'monthly' => now()->subMonth(),
            'last_3_months' => now()->subMonths(3),
            'last_6_months' => now()->subMonths(6),
            'custom' => Carbon::parse($this->customStartDate),
            default => now()->subMonth(),
        };
    
        if ($period === 'custom') {
            $endDate = Carbon::parse($this->customEndDate);
        }
    
        // Fetch transactions for the selected period
        $transactions = Transaction::where('account_id', $this->accountId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    
        // Calculate totals
        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpenses = $transactions->where('type', 'expense')->sum('amount');
        $net = $totalIncome - $totalExpenses;
    
        // Fetch only categories with transactions
        $categories = Category::whereHas('transactions', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        })->with(['transactions' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }])->get();
    
        $incomeCategories = [];
        $expenseCategories = [];
    
        foreach ($categories as $category) {
            $income = $category->transactions->where('type', 'income')->sum('amount');
            $expense = $category->transactions->where('type', 'expense')->sum('amount');
    
            if ($income > 0) {
                $incomeCategories[$category->name] = $income;
            }
            if ($expense > 0) {
                $expenseCategories[$category->name] = $expense;
            }
        }
    
        // Generate the PDF report
        $pdf = Pdf::loadView('reports.transaction', [
            'account' => $this->account, 
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'net' => $net,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'transaction_report.pdf');
    }
    
    

    public function validateCustomDates()
    {
        $this->validate([
            'customStartDate' => 'required|date',
            'customEndDate' => 'required|date|after_or_equal:customStartDate',
        ]);
    }

    public function mount($account)
    {
        Log::info('Component Mounted with Query String:', request()->query());
        Log::info('Component Mounted');

        if (is_numeric($account)) {
            $this->account = Account::findOrFail($account);
        } else {
            $this->account = $account;
        }

        $this->accountId = $this->account->id;

        // Retrieve the selected period from query parameters (if exists)
        $this->selectedPeriod = request()->query('period', '7'); // Default to '7' if not provided

        // Initialize chart data with selectedPeriod
        $this->loadChartData($this->selectedPeriod);
    }

    // Listen for changes to selectedPeriod
    public function changePeriod($selectedperiod)
    {
        Log::info('Change Period Called:', ['period' => $selectedperiod]);
        $this->selectedPeriod = $selectedperiod; // Update the selected period
        $this->dropdownOpen = false; // Close the dropdown
        $this->loadChartData($this->selectedPeriod);
        $this->dispatch('loadChartData');
        // Call the new method to update the chart
        return redirect()->route('accounts.show', [
            'account' => $this->accountId,
            'period' => $selectedperiod, // Pass the selected period as a query parameter
        ]);
    }

    public function loadChartData($selectedPeriod)
    {
        Log::info('Load Chart Data Called:', ['selectedPeriod' => $selectedPeriod]);

        // Reset totals before recalculating
        $this->totalIncome = 0;
        $this->totalExpenses = 0;
        $this->net = 0;

        // Define the date range based on the selected period
        $endDate = now();
        $startDate = match ($selectedPeriod) {
            '7' => now()->subDays(7),
            '90' => now()->subDays(90),
            '180' => now()->subDays(180),
            'month' => now()->subMonth(),
            'all' => null, // Handle "All Time" explicitly
            default => now()->subDays(7), // Default to last 7 days
        };

        // Fetch transactions based on the selected period
        $transactions = Transaction::where('account_id', $this->accountId)
            ->when($startDate !== null, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->selectRaw('DATE(date) as date, type, SUM(amount) as total')
            ->groupByRaw('DATE(date), type')
            ->orderByRaw('DATE(date) ASC')
            ->get();

        $incomeData = [];
        $expenseData = [];

        foreach ($transactions as $transaction) {
            $dateString = Carbon::parse($transaction->date)->toDateString();

            if ($transaction->type === 'income') {
                $incomeData[$dateString] = (float) $transaction->total;
                $this->totalIncome += (float) $transaction->total;
            } else {
                $expenseData[$dateString] = (float) $transaction->total;
                $this->totalExpenses += (float) $transaction->total;
            }
        }

        // Calculate net value
        $this->net = $this->totalIncome - $this->totalExpenses;

        // Get all unique dates
        $dates = collect(array_merge(array_keys($incomeData), array_keys($expenseData)))
            ->sort()
            ->unique();

        // Format data for chart
        $this->chartData = [
            'dates' => $dates->values()->all(),
            'income' => array_map(fn($date) => $incomeData[$date] ?? 0, $dates->values()->all()),
            'expense' => array_map(fn($date) => $expenseData[$date] ?? 0, $dates->values()->all()),
        ];

        // Dispatch event to update the chart
        $this->dispatch('updateChart', [
            'dates' => $this->chartData['dates'],
            'income' => $this->chartData['income'],
            'expense' => $this->chartData['expense'],
        ]);
    }

    public function updateChartData($period)
    {
        Log::info('Updating Chart Data for Period:', ['period' => $period]);

        // Load the chart data for the selected period
        $this->loadChartData($period);

        // Dispatch an event to the frontend with the updated chart data
        $this->dispatch('chartUpdated', [
            'dates' => $this->chartData['dates'],
            'income' => $this->chartData['income'],
            'expense' => $this->chartData['expense'],
        ]);
        $this->dispatch('updateChart',  $this->expenseData);
    }

    public function updatedloadChartData()
    {
        $this->dispatch('refreshPage');
    }

    public function toggleDropdown()
    {
        $this->dropdownOpen = !$this->dropdownOpen;
    }

    // Open the popup menu for a specific transaction
    public function openPopup($transactionId)
    {
        $this->openPopupId = $transactionId;
    }

    // Close the popup menu
    public function closePopup()
    {
        $this->openPopupId = null;
    }

    // Delete a transaction
    public function deleteTransaction($transactionId)
    {
        // Find the transaction
        $transaction = Transaction::findOrFail($transactionId);

        // Update total income or expenses based on the transaction type
        if ($transaction->type === 'income') {
            $this->totalIncome -= $transaction->amount; // Subtract from total income
            $this->account->balance -= $transaction->amount; // Decrease account balance
        } else {
            $this->totalExpenses -= $transaction->amount; // Subtract from total expenses
            $this->account->balance += $transaction->amount; // Increase account balance
        }

        // Recalculate the net value
        $this->net = $this->totalIncome - $this->totalExpenses;

        // Save the updated account balance
        $this->account->save();

        // Delete the transaction
        $transaction->delete();

        // Reload the chart data to reflect the updated totals
        $this->loadChartData($this->selectedPeriod);

        // Flash a success message
        session()->flash('message', 'Transaction deleted successfully.');

        // Close the popup menu
        $this->closePopup();

        // Dispatch a browser event to refresh the page smoothly
        $this->dispatch('transaction-deleted');
        $this->dispatch('refreshPage');
    }

    public function refreshPage()
    {
        // This will force a re-render of the component
        $this->render();
    }

    protected function getCategoryColors()
    {
        // Fetch all categories (both income and expense)
        $categories = Category::whereHas('transactions', function ($query) {
            $query->where('account_id', $this->accountId)
                ->whereMonth('created_at', now()->month);
        })->get();

        // Define a larger list of colors to ensure uniqueness
        $colors = [
            '#FF6B6B',
            '#4ECDC4',
            '#45B7D1',
            '#96CEB4',
            '#FFA07A',
            '#FFD700',
            '#8A2BE2',
            '#20B2AA',
            '#FF6347',
            '#7B68EE',
            '#00FA9A',
            '#FF4500',
            '#6A5ACD',
            '#32CD32',
            '#FF1493',
            '#1E90FF',
            '#FF69B4',
            '#8B4513',
            '#2E8B57',
            '#DAA520'
        ];

        // Assign colors to categories
        $categoryColors = [];
        foreach ($categories as $index => $category) {
            $categoryColors[$category->name] = $colors[$index % count($colors)];
        }

        return $categoryColors;
    }

    public function toggleFilterTypeDropdown()
    {
        $this->isFilterTypeDropdownOpen = !$this->isFilterTypeDropdownOpen;
        $this->isFilterTransactionDropdownOpen = false; // Close the other dropdown
    }

    public function toggleFilterTransactionDropdown()
    {
        $this->isFilterTransactionDropdownOpen = !$this->isFilterTransactionDropdownOpen;
        $this->isFilterTypeDropdownOpen = false; // Close the other dropdown
    }

    public function setFilterType($type)
    {
        $this->filterType = $type;
        $this->isFilterTypeDropdownOpen = false; // Close the dropdown after selection
    }

    public function setFilterTransaction($transaction)
    {
        $this->filterTransaction = $transaction;
        $this->isFilterTransactionDropdownOpen = false; // Close the dropdown after selection
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Fetch transactions for the user's account based on search query and filters
        $transactions = Transaction::where('account_id', $this->accountId)
            ->when(!empty($this->search), function ($query) {
                $query->whereRaw('LOWER(description) LIKE ?', ['%' . strtolower($this->search) . '%']);
            })
            ->when($this->filterType !== 'all', function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterTransaction !== 'all', function ($query) {
                $query->where('is_recurring', $this->filterTransaction === 'recurring');
            })
            ->latest()
            ->paginate(20);

        // Fetch category colors for chart or UI purposes
        $categoryColors = $this->getCategoryColors();

        // Return the view with the required data
        return view('livewire.account-show-component', [
            'transactions' => $transactions,
            'totalIncome' => $this->totalIncome,
            'totalExpenses' => $this->totalExpenses,
            'net' => $this->net,
            'chartData' => $this->chartData,
            'categoryColors' => $categoryColors,
        ])->layout('layouts.app');
    }
}