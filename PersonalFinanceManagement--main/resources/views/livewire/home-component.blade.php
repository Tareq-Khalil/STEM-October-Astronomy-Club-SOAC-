<div class="container mx-auto px-5 my-8">
        <div class="px-9">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-6xl font-bold tracking-tight gradient-title">Dashboard</h1>
            </div>
            <div class="space-y-8">
                <!-- Monthly Budget Section -->
                <div class="rounded-xl border bg-card text-card-foreground shadow">
                    <div class="p-6">
                        @if($accounts->isNotEmpty())
                            <div class="flex flex-row items-center justify-between space-y-0 pb-2">
                                <div class="flex-1">
                                    <div class="tracking-tight text-sm font-medium">Monthly Budget (Default Account)</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($isEditing)
                                            <div class="flex items-center gap-2">
                                                <!-- Editable Budget Input -->
                                                <input 
                                                    class="flex h-9 rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring w-32"
                                                    type="number" 
                                                    wire:model.lazy="newBudget"
                                                    wire:keydown.enter="updateBudget"
                                                    min="1"
                                                    placeholder="Enter amount">
                                                
                                                <!-- Save Button -->
                                                <button 
                                                    class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring hover:bg-green-200 text-green-600 h-9 w-9" 
                                                    wire:click="updateBudget">
                                                    ✅
                                                </button>
                                                
                                                <!-- Cancel Button -->
                                                <button 
                                                    class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring hover:bg-red-200 text-red-600 h-9 w-9" 
                                                    wire:click="cancelEdit">
                                                    ❌
                                                </button>
                                            </div>
                                        @else
                                            <!-- Display Budget Details -->
                                            <div class="text-sm text-muted-foreground">
                                                Rs. {{ number_format($spent, 2) }} of Rs. {{ number_format($budget, 2) }} spent
                                            </div>
                                            
                                            <!-- Edit Button -->
                                            <button class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring hover:bg-gray-200 h-6 w-6" wire:click="editBudget">
                                                ✏️
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Budget Progress Bar -->
                            <div class="p-6 pt-0">
                                <div class="space-y-2">
                                    @php
                                        $budgetPercentage = $budget > 0 ? round(($spent / $budget) * 100) : 0;
                                        $progressColor = match (true) {
                                            $budgetPercentage <= 50 => 'bg-green-500',  // Safe
                                            $budgetPercentage <= 80 => 'bg-yellow-500', // Warning
                                            default => 'bg-red-500',                     // Critical
                                        };
                                    @endphp

                                    @if ($budgetPercentage === null)
                                        <div aria-valuemax="100" aria-valuemin="0" role="progressbar" 
                                            class="relative h-2 w-full overflow-hidden rounded-full bg-primary/20">
                                            <div class="h-full w-full flex-1 bg-primary bg-green-500 animate-indeterminate"></div>
                                        </div>
                                    @else
                                        <div aria-valuemax="{{ $budget }}" aria-valuemin="0" role="progressbar" 
                                            class="relative h-2 w-full overflow-hidden rounded-full bg-primary/20">
                                            <div class="h-full {{ $progressColor }} transition-all" style="width: {{ $budgetPercentage }}%;"></div>
                                        </div>
                                    @endif

                                    <p class="text-xs text-muted-foreground text-right">
                                        {{ $budgetPercentage !== null ? $budgetPercentage . '%' : 'Calculating...' }} used
                                    </p>
                                </div>
                            </div>

                        @else
                            <div class="text-center p-6">
                                <p class="text-center py-4 text-gray-500">
                                    ⚠️ An account is required to manage a budget. Please create an account to proceed.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Show a loading state while updating -->
                <div wire:loading wire:target="updateBudget" class="text-xs text-gray-500 text-center mt-2">
                    Updating budget...
                </div>

                <!-- Recent Transactions and Monthly Expense Breakdown -->
                <div class="grid gap-4 md:grid-cols-2">
                    <!-- Recent Transactions -->
                    <div class="rounded-xl border bg-card text-card-foreground shadow">
                        <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-4">
                            <div class="tracking-tight text-base font-normal">Recent Transactions</div>

                            <!-- Account Selection Dropdown -->
                             @if ($accounts->filter(fn($account) => $account->transactions->isNotEmpty())->isNotEmpty())
                             
                             <select wire:model.live="viewingAccount"
                             class="flex h-9 items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background focus:outline-none focus:ring-1 focus:ring-ring w-[140px]">
                             @foreach($accounts->filter(fn($account) => $account->transactions->isNotEmpty()) as $account)
                             <option value="{{ $account->id }}" {{ $viewingAccount == $account->id ? 'selected' : '' }}>
                                 {{ $account->name }}
                                </option>
                                @endforeach             
                            </select>
                            @endif


                        </div>

                        <div class="p-6 pt-0">
                            <div class="space-y-4">
                                @forelse($recentTransactions as $transaction)
                                    <div class="flex items-center justify-between">
                                        <div class="space-y-1">
                                            <p class="text-sm font-medium leading-none">
                                                {{ $transaction->category->name }}
                                                @if($transaction->is_recurring)
                                                <span class="text-xs text-muted-foreground">(Recurring)</span>
                                                @endif<br>
                                             <p class="text-xs text-gray-500 text-muted-foreground">{{ $transaction->description }}</p>   
                                            </p>
                                            <p class="text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y') }}</p>
                                            </div>

                                        <!-- Amount + Icon (SVG at the top right) -->
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="text-sm font-medium {{ $transaction->type == 'expense' ? 'text-red-500' : 'text-green-500' }}">
                                                Rs. {{ number_format($transaction->amount, 2) }}
                                            </span>

                                            <!-- Expense (Arrow Down-Right) -->
                                            @if($transaction->type == 'expense')
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="lucide lucide-arrow-down-right h-4 w-4 text-red-500">
                                                    <path d="m7 7 10 10"></path>
                                                    <path d="M17 7v10H7"></path>
                                                </svg>
                                            @else
                                                <!-- Income (Arrow Right-Up) -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round" class="lucide lucide-arrow-up-right h-4 w-4 text-green-500">
                                                    <path d="m7 17 10-10"></path>
                                                    <path d="M17 17V7H7"></path>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center text-muted-foreground py-4">No recent transactions</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Expense Breakdown -->
                    <div class="rounded-xl border bg-card text-card-foreground shadow">
                        <div class="flex flex-col space-y-1.5 p-6">
                            <div class="tracking-tight text-base font-normal">Monthly Expense Breakdown</div>
                        </div>

                        <div class="p-0 pb-5">
        @if(count($expenseData) > 0)
        <div wire:ignore>
    <canvas id="expenseChart"></canvas>
</div>
        @else
            <p class="text-center text-muted-foreground py-4">No expense data available</p>
        @endif
    </div>
                    </div>
                </div>

                <!-- Add New Account and Account Cards -->
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Add New Account Card -->
                    <div class="rounded-xl border bg-card text-card-foreground shadow hover:shadow-md transition-shadow cursor-pointer border-dashed" type="button" aria-haspopup="dialog" aria-expanded="false" aria-controls="radix-:r1:" data-state="closed" onclick="openAccountForm()">
                        <div class="p-6 flex flex-col items-center justify-center text-muted-foreground h-full pt-5">
                            <svg style="color: #737373;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-10 w-10 mb-2">
                                <path d="M5 12h14"></path>
                                <path d="M12 5v14"></path>
                            </svg>
                            <p class="text-sm font-medium" style="color: #737373;">Add New Account</p>
                        </div>
                    </div>

                    <!-- Display Accounts -->
                    @foreach ($accounts as $account)
    <div wire:click="goToAccount({{ $account->id }})"
         class="rounded-xl border bg-card text-card-foreground shadow hover:shadow-md transition-shadow group relative cursor-pointer">
        
        <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
            <div class="tracking-tight text-sm font-medium capitalize">{{ $account->name }}</div>
            
            <!-- Toggle Button (Prevent click propagation) -->
            <button type="button" 
        wire:click.stop="selectAccount({{ $account->id }})"
        wire:init="loadAccounts" 
        class="peer inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent shadow-sm transition-colors 
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background 
               disabled:cursor-not-allowed disabled:opacity-50
               {{ $selectedAccount == $account->id ? 'bg-green-500' : 'bg-gray-300' }}">
    
    <span class="pointer-events-none block h-4 w-4 rounded-full bg-white shadow-lg ring-0 transition-transform
                {{ $selectedAccount == $account->id ? 'translate-x-4' : 'translate-x-0' }}">
    </span>
</button>
        </div>

        <div class="p-6 pt-0">
            <div class="text-2xl font-bold">Rs. {{ $account->balance }}</div>
            <p class="text-xs text-muted-foreground">{{ $account->type }}</p>
        </div>

        <div class="items-center p-6 pt-0 flex justify-between text-sm text-muted-foreground">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-right mr-1 h-4 w-4 text-green-500">
                    <path d="M7 7h10v10"></path>
                    <path d="M7 17 17 7"></path>
                </svg>Income
            </div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-right mr-1 h-4 w-4 text-red-500">
                    <path d="m7 7 10 10"></path>
                    <path d="M17 7v10H7"></path>
                </svg>Expense
            </div>
        </div>
    </div>
@endforeach


                </div>

                <!-- Account Form Pop-up -->
                <div id="accountForm" role="dialog" aria-describedby="radix-:r3:" aria-labelledby="radix-:r2:" data-state="closed" data-vaul-drawer-direction="bottom" data-vaul-drawer="" data-vaul-delayed-snap-points="false" data-vaul-snap-points="false" data-vaul-custom-container="false" data-vaul-animate="true" class="fixed inset-x-0 bottom-0 z-50 mt-24 flex h-auto flex-col rounded-t-[10px] border bg-white transform transition-transform duration-300 ease-in-out translate-y-full" tabindex="-1" style="pointer-events: auto;">
                    <div class="mx-auto mt-4 h-2 w-[100px] rounded-full bg-muted"></div>
                    <div class="grid gap-1.5 p-4 text-center sm:text-left">
                        <h2 id="radix-:r2:" class="text-lg font-semibold leading-none tracking-tight">Create New Account</h2>
                    </div>
                    <div class="px-4 pb-4">
                        <livewire:account-component />
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function openAccountForm() {
        const form = document.getElementById('accountForm');
        form.classList.remove('translate-y-full');
        form.classList.add('translate-y-0');
    }

    function closeAccountForm() {
        const form = document.getElementById('accountForm');
        form.classList.remove('translate-y-0');
        form.classList.add('translate-y-full');
    }

    // Listen for Livewire event to close the form
    Livewire.on('closeAccountForm', () => {
        closeAccountForm();
    });
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    let expenseChart = null;
    const chartElement = document.getElementById("expenseChart");

    function renderChart(expenseData) {
        if (!chartElement) return;

        const ctx = chartElement.getContext("2d");

        // Destroy the old chart if it exists
        if (expenseChart) {
            expenseChart.destroy();
        }

        // Render the new chart
        expenseChart = new Chart(ctx, {
            type: "pie",
            data: {
                labels: expenseData.map(item => item.name),
                datasets: [{
                    data: expenseData.map(item => item.total),
                    backgroundColor: expenseData.map(item => item.color),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "bottom" }
                }
            }
        });
    }

    // Initial render if data is already available
    @if(count($expenseData) > 0)
        renderChart(@json($expenseData));
    @endif

    // Listen for Livewire event to update the chart
    Livewire.on('updateChart', (data) => {
        console.log("Chart Data Received:", data); // Debugging line

        // Ensure expenseData is an array before updating the chart
        if (!Array.isArray(data.expenseData)) {
            console.error("Error: expenseData is not an array!", data.expenseData);
            return;
        }

        renderChart(data.expenseData);
    });
    
});
</script>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('accountSelected', (accountId) => {
            // Smoothly update the UI without a full page reload
            console.log('Account selected:', accountId);
            // You can add custom JavaScript here to animate or update specific parts of the UI
        });
    });
</script>