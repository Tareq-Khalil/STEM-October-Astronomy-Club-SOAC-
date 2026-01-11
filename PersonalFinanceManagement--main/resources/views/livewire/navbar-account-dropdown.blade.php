<main class="min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 my-4 mt-8 sm:mt-16">
        <div class="px-4 sm:px-6 lg:px-28">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between mb-5 space-y-4 sm:space-y-0">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight gradient-title">Accounts</h1>

                <!-- Add Account Button -->
                <button onclick="openAccountForm()"
                    class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors border border-input bg-black text-white shadow-sm hover:bg-gray-900 px-4 py-2 sm:px-5 sm:py-2.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-plus h-5 w-5">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg>
                    Add Account
                </button>
            </div>

            <div class="space-y-8">
                <!-- Account List -->
                <div class="rounded-xl border bg-card text-card-foreground shadow">
                    <div class="p-4 sm:p-6">
                        @if($accounts->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse">
                                    <thead class="bg-gray-100">
                                        <tr class="text-left text-gray-700 uppercase text-sm tracking-wide">
                                            <th class="px-4 py-3 sm:px-6 sm:py-4">Name</th>
                                            <th class="px-4 py-3 sm:px-6 sm:py-4">Type</th>
                                            <th class="px-4 py-3 sm:px-6 sm:py-4">Balance</th>
                                            <th class="px-4 py-3 sm:px-6 sm:py-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($accounts as $account)
                                            <tr wire:key="{{ $account->id }}" class="hover:bg-gray-50 transition">
                                                <td class="px-4 py-3 sm:px-6 sm:py-4 font-medium text-gray-900">{{ $account->name }}</td>
                                                <td class="px-4 py-3 sm:px-6 sm:py-4 text-gray-600">{{ ucfirst($account->type) }}</td>
                                                <td class="px-4 py-3 sm:px-6 sm:py-4 text-gray-600">Rs.{{ number_format($account->balance, 2) }}</td>
                                                <td class="px-4 py-3 sm:px-6 sm:py-4 text-sm flex items-center space-x-3">
                                                    @if($confirmingDelete === $account->id)
                                                        <button wire:click.prevent="deleteAccount({{ $account->id }})"
                                                            class="bg-red-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 text-sm rounded-md">
                                                            Yes
                                                        </button>
                                                        <button wire:click.prevent="cancelDelete"
                                                            class="bg-gray-600 text-white px-3 py-1.5 sm:px-4 sm:py-2 text-sm rounded-md">
                                                            No
                                                        </button>
                                                    @else
                                                        <button wire:click="showEditForm({{ $account->id }})"
                                                            onclick="openEditAccountForm()"
                                                            class="text-blue-500 hover:text-blue-700 text-sm">
                                                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 48 48">
                                                                <path d="M38.657 18.536l2.44-2.44c2.534-2.534 2.534-6.658 0-9.193-1.227-1.226-2.858-1.9-4.597-1.9s-3.371.675-4.597 1.901l-2.439 2.439L38.657 18.536zM27.343 11.464L9.274 29.533c-.385.385-.678.86-.848 1.375L5.076 41.029c-.179.538-.038 1.131.363 1.532C5.726 42.847 6.108 43 6.5 43c.158 0 .317-.025.472-.076l10.118-3.351c.517-.17.993-.463 1.378-.849l18.068-18.068L27.343 11.464z"></path>
                                                            </svg>
                                                        </button>
                                                        <button wire:click="confirmDelete({{ $account->id }})"
                                                            class="text-red-500 hover:text-red-700 text-sm">
                                                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 24 24">
                                                                <path d="M 10 2 L 9 3 L 4 3 L 4 5 L 5 5 L 5 20 C 5 20.522222 5.1913289 21.05461 5.5683594 21.431641 C 5.9453899 21.808671 6.4777778 22 7 22 L 17 22 C 17.522222 22 18.05461 21.808671 18.431641 21.431641 C 18.808671 21.05461 19 20.522222 19 20 L 19 5 L 20 5 L 20 3 L 15 3 L 14 2 L 10 2 z M 7 5 L 17 5 L 17 20 L 7 20 L 7 5 z M 9 7 L 9 18 L 11 18 L 11 7 L 9 7 z M 13 7 L 13 18 L 15 18 L 15 7 L 13 7 z"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center p-6">
                                <p class="text-gray-500">⚠️ No accounts found. Create an account to start managing your finances.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Account Modal -->
    <div id="accountForm" role="dialog"
        class="fixed inset-x-0 bottom-0 z-50 mt-24 flex h-auto flex-col rounded-t-lg border bg-white transform transition-transform duration-300 ease-in-out translate-y-full p-4 sm:p-6">
        <div class="mx-auto mt-4 h-2 w-24 rounded-full bg-gray-300"></div>
        <div class="grid gap-2 p-4 text-center sm:text-left">
            <h2 class="text-lg font-semibold leading-none tracking-tight">Create New Account</h2>
        </div>
        <div class="px-4 pb-4">
            <livewire:account-component />
        </div>
    </div>

    <!-- Edit Account Modal -->
    <div id="editAccountForm" role="dialog"
        class="fixed inset-x-0 bottom-0 z-50 mt-24 flex h-auto flex-col rounded-t-lg border bg-white transform transition-transform duration-300 ease-in-out {{ $showEditModal ? 'translate-y-0' : 'translate-y-full' }} p-4 sm:p-6">
        <div class="mx-auto mt-4 h-2 w-24 rounded-full bg-gray-300"></div>
        <div class="grid gap-2 p-4 text-center sm:text-left">
            <h2 class="text-lg font-semibold leading-none tracking-tight text-center">Edit Account</h2>
        </div>
        <div class="px-4 pb-4">
            @if($editingAccount)
                <livewire:edit-account-component :accountId="$editingAccount" />
            @endif
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

    function openEditAccountForm() {
        const form = document.getElementById('editAccountForm');
        form.classList.remove('translate-y-full');
        form.classList.add('translate-y-0');
    }

    function closeEditAccountForm() {
        const form = document.getElementById('editAccountForm');
        form.classList.remove('translate-y-0');
        form.classList.add('translate-y-full');
    }

    // Listen for Livewire events
    Livewire.on('closeAccountForm', () => {
        closeAccountForm();
    });

    Livewire.on('closeEditAccountModal', () => {
        closeEditAccountForm();
    });
</script>