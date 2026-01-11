<div>

    <!-- Header -->
    <main class="min-h-screen">
        <div class="container mx-auto my-4">
            <div class="space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Account Header -->
                <div class="flex flex-col sm:flex-row gap-4 items-end justify-between">
                    <div>
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight gradient-title capitalize">{{ $account->name }}</h1>
                        <p class="text-muted-foreground">{{ $account->type }}</p>
                    </div>
                    <div class="text-right pb-2">
                        <div class="text-xl sm:text-2xl font-bold">Rs. {{ number_format($account->balance, 2) }}</div>
                        <p class="text-sm text-muted-foreground">{{ $account->transactions->count() }} Transactions</p>
                    </div>
                </div>
                <!-- Transaction Overview -->
                <div class="rounded-xl border bg-card text-card-foreground shadow">
    <!-- Header Section -->
    <div class="p-6 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0 pb-7">
        <!-- Title -->
        <div class="tracking-tight text-base font-normal text-center sm:text-left">
            Transaction Overview
        </div>

        <!-- Buttons and Dropdowns Container -->
        <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 items-center">
            <!-- Download Report Button and Dropdown -->
            <div class="relative">
    <!-- Button with responsive width -->
    <button wire:click="toggleReportDropdown" class="bg-black text-white px-4 py-2 rounded-lg w-full sm:w-auto hover:bg-gray-800 transition duration-300">
        Download Report
    </button>

    @if ($reportDropdownOpen)
        <!-- Dropdown with responsive width and positioning -->
        <div class="absolute mt-2 w-full sm:w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-20">
            <!-- Dropdown options -->
            <button wire:click="downloadReport('monthly')" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-sm text-gray-700">
                Monthly Report
            </button>
            <button wire:click="downloadReport('last_3_months')" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-sm text-gray-700">
                Last 3 Months
            </button>
            <button wire:click="downloadReport('last_6_months')" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-sm text-gray-700">
                Last 6 Months
            </button>

            <!-- Custom Period with Sub-List -->
            <div x-data="{ isCustomPeriodOpen: false }" class="w-full">
                <!-- Button to toggle Custom Period -->
                <button @click="isCustomPeriodOpen = !isCustomPeriodOpen" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-sm text-gray-700 rounded-lg transition duration-300">
                    Custom Period
                </button>

                <!-- Sub-List for Custom Period -->
                <div x-show="isCustomPeriodOpen" class="w-full px-4 py-2">
                    <div class="space-y-4">
                        <!-- Start Date Input -->
                        <div>
                            <label for="customStartDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" wire:model="customStartDate" id="customStartDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                        </div>

                        <!-- End Date Input -->
                        <div>
                            <label for="customEndDate" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" wire:model="customEndDate" id="customEndDate" class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
                        </div>

                        <!-- Download Button -->
                        <div class="mt-4">
                            <button wire:click="downloadReport('custom')" class="w-full px-1 py-1 bg-black text-white rounded-lg text-sm hover:bg-gray-800 transition duration-300">
                                Download
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

            <!-- Period Selection Dropdown -->
            <div class="relative">
                <button type="button" wire:click="toggleDropdown" class="flex h-9 items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1 w-[140px]">
                    <span style="pointer-events: none;">
                        @if($selectedPeriod === 'all')
                            All Time
                        @elseif($selectedPeriod === '7')
                            Last 7 Days
                        @elseif($selectedPeriod === '90')
                            Last 3 Months
                        @elseif($selectedPeriod === '180')
                            Last 6 Months
                        @elseif($selectedPeriod === 'month')
                            Last Month
                        @endif
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 opacity-50" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </button>

                <!-- Dropdown Options -->
                @if($dropdownOpen)
                    <div class="absolute z-10 mt-2 w-[140px] rounded-md border bg-card text-card-foreground shadow">
                        <ul>
                            <li>
                                <button wire:click="changePeriod('7')" class="bg-white w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $selectedPeriod === '7' ? 'bg-muted/50' : '' }}">Last 7 Days</button>
                            </li>
                            <li>
                                <button wire:click="changePeriod('month')" class="bg-white w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $selectedPeriod === 'month' ? 'bg-muted/50' : '' }}">Last Month</button>
                            </li>
                            <li>
                                <button wire:click="changePeriod('90')" class="bg-white w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $selectedPeriod === '90' ? 'bg-muted/50' : '' }}">Last 3 Months</button>
                            </li>
                            <li>
                                <button wire:click="changePeriod('180')" class="bg-white w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $selectedPeriod === '180' ? 'bg-muted/50' : '' }}">Last 6 Months</button>
                            </li>
                            <li>
                                <button wire:click="changePeriod('all')" class="bg-white w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $selectedPeriod === 'all' ? 'bg-muted/50' : '' }}">All Time</button>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="p-6 pt-0">
        <!-- Stats Section -->
        <div class="flex flex-col sm:flex-row justify-around gap-4 sm:gap-0 mb-6 text-sm">
            <div class="text-center">
                <p class="text-muted-foreground">Total Income</p>
                <p class="text-lg font-bold text-green-500">Rs. {{ number_format($totalIncome, 2) }}</p>
            </div>
            <div class="text-center">
                <p class="text-muted-foreground">Total Expenses</p>
                <p class="text-lg font-bold text-red-500">Rs. {{ number_format($totalExpenses, 2) }}</p>
            </div>
            <div class="text-center">
                <p class="text-muted-foreground">Net</p>
                <p class="text-lg font-bold {{ $net >= 0 ? 'text-green-500' : 'text-red-500' }}">Rs. {{ number_format($net, 2) }}</p>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="h-[300px] w-full" wire:ignore>
            @if(count($chartData['dates']) > 0)
                <canvas id="transactionChart"></canvas>
            @else
                <div class="flex items-center justify-center h-full text-muted-foreground">
                    <p>No transactions found to display the chart.</p>
                </div>
            @endif
        </div>
    </div>
</div>

                <!-- Search and Filters -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-2 top-2.5 h-4 w-4 text-muted-foreground">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input 
    type="text" 
    wire:model.live.debounce.300ms="search" 
    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 md:text-sm pl-8" 
    placeholder="Search transactions by description..."
>                    </div>

               
                    <!-- Filter Buttons -->
                    <div class="flex gap-2">
                        <!-- All Types Dropdown -->
                        
                        <!-- All Transactions Dropdown -->
                        <div class="relative">
    <button 
        type="button" 
        wire:click="toggleFilterTypeDropdown" 
        class="flex h-9 items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50 w-[130px]"
    >
        <span>{{ $filterType === 'all' ? 'All Types' : ucfirst($filterType) }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 opacity-50">
            <path d="m6 9 6 6 6-6"></path>
        </svg>
    </button>

    @if($isFilterTypeDropdownOpen)
        <div 
            class="absolute z-10 mt-2 w-[130px] rounded-md border bg-white text-card-foreground shadow"
            x-data="{ open: true }"
            x-show="open"
            @click.away="open = false"
        >
            <ul>
                <li>
                    <button 
                        wire:click="setFilterType('all')" 
                        class="w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $filterType === 'all' ? 'bg-muted/50' : '' }}"
                    >
                        All Types
                    </button>
                </li>
                <li>
                    <button 
                        wire:click="setFilterType('income')" 
                        class="w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $filterType === 'income' ? 'bg-muted/50' : '' }}"
                    >
                        Income
                    </button>
                </li>
                <li>
                    <button 
                        wire:click="setFilterType('expense')" 
                        class="w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $filterType === 'expense' ? 'bg-muted/50' : '' }}"
                    >
                        Expense
                    </button>
                </li>
            </ul>
        </div>
    @endif
</div>

<div class="relative">
    <button 
        type="button" 
        wire:click="toggleFilterTransactionDropdown" 
        class="flex h-9 items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50 w-[130px]"
    >
        <span>{{ $filterTransaction === 'all' ? 'All Transactions' : ucfirst($filterTransaction) }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-4 w-4 opacity-50">
            <path d="m6 9 6 6 6-6"></path>
        </svg>
    </button>

    @if($isFilterTransactionDropdownOpen)
        <div 
            class="absolute z-10 mt-2 w-[130px] rounded-md border bg-white text-card-foreground shadow"
            x-data="{ open: true }"
            x-show="open"
            @click.away="open = false"
        >
            <ul>
                <li>
                    <button 
                        wire:click="setFilterTransaction('all')" 
                        class="w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $filterTransaction === 'all' ? 'bg-muted/50' : '' }}"
                    >
                        All Transactions
                    </button>
                </li>
                <li>
                    <button 
                        wire:click="setFilterTransaction('recurring')" 
                        class="w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $filterTransaction === 'recurring' ? 'bg-muted/50' : '' }}"
                    >
                        Recurring
                    </button>
                </li>
                <li>
                    <button 
                        wire:click="setFilterTransaction('non-recurring')" 
                        class="w-full px-4 py-2 text-sm text-left hover:bg-muted/50 {{ $filterTransaction === 'non-recurring' ? 'bg-muted/50' : '' }}"
                    >
                        Non-Recurring
                    </button>
                </li>
            </ul>
        </div>
    @endif
</div>
            </div>
        </div>
        

        <!-- Transactions Table -->
   <!-- Transactions Table -->
   <div class="rounded-md border">
    @if($transactions->count() > 0)
        <div class="relative w-full overflow-visible">
            <table class="w-full caption-bottom text-sm ">
                <!-- Table Headers -->
                <thead class="[&_tr]:border-b">
                    <tr class="border-b transition-colors hover:bg-muted/50">
                        <th class="h-10 px-2 text-left align-middle font-medium text-muted-foreground">Date</th>
                        <th class="h-10 px-2 text-left align-middle font-medium text-muted-foreground">Description</th>
                        <th class="h-10 px-2 text-left align-middle font-medium text-muted-foreground">Category</th>
                        <th class="h-10 px-2 text-left align-middle font-medium text-muted-foreground">Amount</th>
                        <th class="h-10 px-2 text-left align-middle font-medium text-muted-foreground">Recurring</th>
                        <th class="h-10 px-2 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-[2px] w-[50px]"></th>
                    </tr>
                </thead>
                <!-- Table Body -->
                <tbody class="[&_tr:last-child]:border-0  ">
                    @foreach($transactions as $transaction)
                        <!-- Transaction Rows -->
                        <tr class="border-b transition-colors hover:bg-muted/50">
    <td class="p-2 align-middle">{{ $transaction->date->format('M d, Y') }}</td>
    <td class="p-2 align-middle">{{ $transaction->description ?: 'No Description' }}</td>
    <td class="p-2 align-middle capitalize">
        <span class="px-2 py-1 rounded text-black text-sm" style="background: {{ $categoryColors[$transaction->category->name] ?? '#CCCCCC' }};">
            {{ $transaction->category->name }}
        </span>
    </td >
    <td class="p-2 align-middle font-medium  {{ $transaction->type === 'income' ? 'text-green-500' : 'text-red-500' }}">
        {{ $transaction->type === 'income' ? '+' : '-' }}Rs. {{ number_format($transaction->amount, 2) }}
    </td>
    <td class="p-2 align-middle">
        @if($transaction->is_recurring)
            <div class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold bg-purple-100 text-purple-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-3 w-3"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M8 16H3v5"></path></svg>
                {{ ucfirst($transaction->recurring_interval) }}
            </div>
        @else
            <div class="inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-3 w-3"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M8 16H3v5"></path></svg>
                One-time
            </div>
        @endif
    </td>
    <!-- Action Button and Popup Menu -->
    <td class="p-2 align-middle relative">
        <!-- Action Button -->
        <button 
            class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground h-8 w-8 p-0" 
            type="button" 
            wire:click="openPopup({{ $transaction->id }})"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ellipsis h-4 w-4">
                <circle cx="12" cy="12" r="1"></circle>
                <circle cx="19" cy="12" r="1"></circle>
                <circle cx="5" cy="12" r="1"></circle>
            </svg>
        </button>

        <!-- Popup Menu -->
        @if($openPopupId === $transaction->id)
            <div 
            class="absolute z-10 mt-2 w-[130px] rounded-md border bg-white text-card-foreground shadow"
    x-data="{ open: true }"
    x-show="open"
    @click.away="open = false"
    wire:click.away="closePopup"
    style="position: absolute; top: 100%; right: 0; overflow-y-auto max-h-[200px]"
            >
                <ul>
                    <!-- Edit Link -->
                    <li>
                        <a 
                            href="{{ route('transactions.edit', ['transactionId' => $transaction->id]) }}" 
                            class="w-full px-4 py-2 text-sm text-left hover:bg-muted/50"
                        >
                            Edit
                        </a>
                    </li>
                    <!-- Delete Button -->
                    <li>
                        <button 
                            wire:click="deleteTransaction({{ $transaction->id }})" 
                            class="w-full px-4 py-2 text-sm text-left hover:bg-muted/50"
                        >
                            Delete
                        </button>
                    </li>
                </ul>
            </div>
        @endif
    </td>
</tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="flex items-center justify-center p-6 text-muted-foreground">
            <p>No transactions found.</p>
        </div>
    @endif
</div>
                <!-- Pagination Links -->
                <div wire:navigate>
                    {{ $transactions->links('vendor.livewire.pagination') }}
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-blue-50 py-12">
        <div class="container mx-auto px-4 text-center text-gray-600">
         
        </div>
    </footer>
</div>
<script>
   document.addEventListener("DOMContentLoaded", function () {
    let transactionChart = null;
    const chartElement = document.getElementById("transactionChart");

    // Debugging: Check if chart element exists
    console.log("Chart Element:", chartElement);

    // Function to render or update the chart
    function renderChart(chartData) {
        if (!chartElement) {
            console.error("Error: Chart element not found!");
            return;
        }

        const ctx = chartElement.getContext("2d");

        // Destroy the old chart if it exists
        if (transactionChart) {
            console.log("Destroying old chart instance");
            transactionChart.destroy();
            transactionChart = null; // Reset the chart instance
        }

        // Debugging: Log chart data
        console.log("Rendering Chart with Data:", chartData);

        // Ensure the data is valid before rendering
        if (!chartData.dates || !chartData.income || !chartData.expense) {
            console.error("Error: Invalid chart data!", chartData);
            return;
        }

        // Render the new chart
        transactionChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: chartData.dates, // X-axis labels (dates)
                datasets: [
                    {
                        label: 'Income',
                        backgroundColor: '#22c55e', // Green color for income
                        data: chartData.income, // Income data
                    },
                    {
                        label: 'Expense',
                        backgroundColor: '#ef4444', // Red color for expense
                        data: chartData.expense, // Expense data
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return 'Rs. ' + value; // Y-axis pe Rs. show karenge
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: '#666',
                            boxWidth: 14,
                            padding: 20,
                        }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rs. ' + context.raw; // Tooltip mein Rs. show karenge
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Initial render if data is already available
    @if(isset($chartData) && count($chartData['dates']) > 0)
        // Convert PHP array to JSON and log it
        const initialChartData = @json($chartData);
        console.log("Initial Chart Data:", initialChartData);

        // Ensure the data is valid before rendering
        if (initialChartData.dates && initialChartData.income && initialChartData.expense) {
            renderChart(initialChartData);
        } else {
            console.error("Error: Initial chart data is invalid!", initialChartData);
        }
    @else
        console.error("Error: Initial chart data is missing or invalid!");
    @endif

    // Listen for Livewire event to update the chart
    Livewire.on('updateChart', (data) => {
        console.log("Chart Data Received:", data);

        if (!data.dates || !data.income || !data.expense) {
            console.error("Error: Invalid chart data!", data);
            return;
        }

        renderChart(data);
    });

    // Listen for Livewire event to reload the page
    Livewire.on('reload-page', () => {
        console.log("Reloading page...");
        window.location.reload();
    });
});
</script>

<script>
    // Auto-close message after 3 seconds
    setTimeout(() => {
        closeMessagePopup();
    }, 3000);

    // Function to close the message popup
    function closeMessagePopup() {
        const messagePopup = document.getElementById('messagePopup');
        if (messagePopup) {
            // Add slide-out animation class
            messagePopup.classList.add('slide-out');

            // Remove the message from DOM after animation completes
            messagePopup.addEventListener('animationend', () => {
                messagePopup.remove();
            }, { once: true }); // Ensure the event listener is removed after execution
        }
    }
</script>
