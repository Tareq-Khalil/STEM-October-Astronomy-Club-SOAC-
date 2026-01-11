<div>
    <!-- Display Success or Error Messages -->
    <div class="fixed bottom-4 right-4 z-50 space-y-2">
    @if (session()->has('error'))
        <div class="bg-red-600 text-white p-4 rounded-md shadow-lg animate-bounce">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('message'))
        <div class="bg-green-500 text-white p-4 rounded-md shadow-lg animate-bounce">
            {{ session('message') }}
        </div>
    @endif
</div>

    <form wire:submit.prevent="updateAccount" class="space-y-4 m-2 p-4 max-w-md mx-auto sm:max-w-lg md:max-w-xl lg:max-w-2xl">
        <!-- Account Name -->
        <div class="space-y-2">
            <label for="name" class="text-sm font-medium leading-none">Account Name</label>
            <input 
                wire:model="name" 
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm" 
                id="name" 
                placeholder="e.g., Main Checking">
            @error('name')
                <span class="text-sm text-red-500">
                    @if ($message === 'The account name is required.')
                        Account name is required.
                    @elseif ($message === 'The account name must not exceed 255 characters.')
                        Account name must not exceed 255 characters.
                    @elseif ($message === 'An account with this name already exists.')
                        An account with this name already exists.
                    @endif
                </span>
            @enderror
        </div>

        <!-- Account Type -->
        <div class="space-y-2">
            <label for="type" class="text-sm font-medium leading-none">Account Type</label>
            <select 
                wire:model="type" 
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                id="type">
                <option value="CURRENT">Current</option>
                <option value="SAVINGS">Savings</option>
            </select>
            @error('type')
                <span class="text-sm text-red-500">
                    @if ($message === 'The account type is required.')
                        Account type is required.
                    @elseif ($message === 'The account type must be either "Current" or "Savings".')
                        Account type must be either "Current" or "Savings".
                    @endif
                </span>
            @enderror
        </div>

        <!-- Initial Balance -->
        <div class="space-y-2">
            <label for="balance" class="text-sm font-medium leading-none">Initial Balance</label>
            <input 
                wire:model="balance" 
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-sm" 
                id="balance" 
                step="1.00" 
                placeholder="0.00" 
                type="number"
                disabled>
            @error('balance')
                <span class="text-sm text-red-500">
                    @if ($message === 'The initial balance is required.')
                        Initial balance is required.
                    @elseif ($message === 'The initial balance must be a number.')
                        Initial balance must be a number.
                    @elseif ($message === 'The initial balance must be at least 0.')
                        Initial balance must be at least 0.
                    @elseif ($message === 'The initial balance must not exceed 999,999,999,999.99.')
                        Initial balance must not exceed 999,999,999,999.99.
                    @endif
                </span>
            @enderror
        </div>

        <!-- Form Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium border border-input bg-background shadow-sm hover:bg-accent h-9 px-4 py-2 flex-1" wire:click="closeModal">Cancel</button>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2 flex-1">
                Update Account
            </button>
        </div>
    </form>
</div>