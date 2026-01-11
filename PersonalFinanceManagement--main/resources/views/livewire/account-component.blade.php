<div>
    <!-- Display Success or Error Messages -->
    @foreach (['error' => 'bg-red-600', 'message' => 'bg-green-500'] as $type => $color)
        @if (session()->has($type))
            <div class="{{ $color }} text-white p-2 rounded-md animate-bounce text-center sm:text-left">
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <form wire:submit.prevent="saveAccount" class="space-y-4 m-2 p-4 max-w-2xl mx-auto">
        <!-- Account Name -->
        <div class="space-y-2">
            <label for="name" class="text-sm font-medium leading-none">Account Name</label>
            <input 
                wire:model="name" 
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm sm:text-base shadow-sm" 
                id="name" 
                placeholder="e.g., Main Checking">
            @error('name') 
                <span class="text-sm text-red-500">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Account Type -->
        <div class="space-y-2">
            <label for="type" class="text-sm font-medium leading-none">Account Type</label>
            <select wire:model="type" class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm sm:text-base shadow-sm" id="type">
                <option value="CURRENT">Current</option>
                <option value="SAVINGS">Savings</option>
            </select>
            @error('type') 
                <span class="text-sm text-red-500">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Initial Balance -->
        <div class="space-y-2">
            <label for="balance" class="text-sm font-medium leading-none">Initial Balance</label>
            <input 
                wire:model="balance" 
                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm sm:text-base shadow-sm" 
                id="balance" 
                step="1.00" 
                placeholder="0.00" 
                type="number">
            @error('balance') 
                <span class="text-sm text-red-500">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Form Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium border border-input bg-background shadow-sm hover:bg-accent h-9 px-4 py-2 flex-1" wire:click="closeModal">Cancel</button>
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium bg-primary text-primary-foreground shadow hover:bg-primary/90 h-9 px-4 py-2 flex-1">
                {{ $isEditing ? 'Update Account' : 'Create Account' }}
            </button>
        </div>
    </form>
</div>