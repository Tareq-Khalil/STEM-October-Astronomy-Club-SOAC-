<div class="container mx-auto my-16">
    <div class="max-w-3xl mx-auto px-5">
        <div class="flex justify-center md:justify-normal mb-8">
            <h1 class="text-5xl gradient-title">Add Transaction</h1>
        </div>

        <!-- Flash Messages -->
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Transaction Type Dropdown -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Type</label>
                <select wire:model.live="type" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                    <option value="expense">Expense</option>
                    <option value="income">Income</option>
                </select>
                @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Amount and Account Inputs -->
            <div class="grid gap-6 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Amount</label>
                    <input type="number" wire:model.live="amount" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring" step="0.01" placeholder="0.00">
                    @error('amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Account</label>
                    <select wire:model.live="account_id" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} (Rs. {{ $account->balance }})</option>
                        @endforeach
                    </select>
                    @error('account_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Category Dropdown -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Category</label>
                <select wire:model.live="category_id" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                    <option value="" class="text-gray-500">Select Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Date Picker -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Date</label>
                <input type="date" wire:model="date" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                @error('date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Description Input -->
            <div class="space-y-2">
                <label class="text-sm font-medium">Description</label>
                <input type="text" wire:model="description" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring" placeholder="Enter description">
                @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Recurring Transaction Toggle -->
            <div class="flex flex-row items-center justify-between rounded-lg border p-4">
    <div class="space-y-0.5">
        <label class="text-base font-medium">Recurring Transaction</label>
        <div class="text-sm text-muted-foreground">Set up a recurring schedule for this transaction</div>
    </div>
    <button
        type="button"
        wire:click="$toggle('is_recurring')"
        class="peer inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent shadow-sm transition-colors 
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background 
               disabled:cursor-not-allowed disabled:opacity-50
               {{ $is_recurring ? 'bg-green-500' : 'bg-gray-300' }}"
    >
        <span
            class="pointer-events-none block h-4 w-4 rounded-full bg-white shadow-lg ring-0 transition-transform 
                   {{ $is_recurring ? 'translate-x-4' : 'translate-x-0' }}"
        ></span>
    </button>
</div>

            <!-- Recurring Interval Dropdown (Only visible if recurring is enabled) -->
            @if ($is_recurring)
                <div class="space-y-2">
                    <label class="text-sm font-medium">Recurring Interval</label>
                    <select wire:model.live="recurring_interval" class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring">
                        <option value="DAILY">Daily</option>
                        <option value="WEEKLY">Weekly</option>
                        <option value="MONTHLY">Monthly</option>
                        <option value="YEARLY">Yearly</option>
                    </select>
                    @error('recurring_interval') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            @endif

            <!-- Form Buttons -->
            <div class="flex gap-4">
            <a class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-full" href="{{ route('home') }}">Cancel</a>
                <button type="submit" class="text-white inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 bg-black text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2 w-full">Create Transaction</button>
            </div>
        </form>
    </div>
</div>