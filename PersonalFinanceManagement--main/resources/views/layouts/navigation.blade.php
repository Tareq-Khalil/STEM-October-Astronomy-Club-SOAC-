<nav class="container mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 bg-white z-50">
    <!-- Logo -->
    <div class="shrink-0 flex items-center">
        <a href="{{ route('home') }}">
            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
        </a>
    </div>

    <!-- Right Section -->
    <div class="flex items-center space-x-4">
        <!-- Dashboard Link -->
        <a class="hover:text-gray-600 text-black flex items-center gap-2" href="{{ route('home') }}">
            <button class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-dashboard">
                    <rect width="7" height="9" x="3" y="3" rx="1"></rect>
                    <rect width="7" height="5" x="14" y="3" rx="1"></rect>
                    <rect width="7" height="9" x="14" y="12" rx="1"></rect>
                    <rect width="7" height="5" x="3" y="16" rx="1"></rect>
                </svg>
                <span class="hidden md:inline">Dashboard</span>
            </button>
        </a>

        @auth
            <!-- Add Transaction or Add Account Button -->
            @if(auth()->user()->accounts->count() > 0)
                <a href="{{ route('transactions.index') }}" class="text-gray-600 hover:text-blue-600 flex items-center gap-2">
                    <button class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors border border-input bg-black text-white shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-pen">
                            <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
                        </svg>
                        <span class="hidden md:inline">Add Transaction</span>
                    </button>
                </a>
            @else
                <!-- Redirect to Create Account -->
                <a onclick="openAccountForm()">
                    <button class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium transition-colors border border-input bg-black text-white shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-5 w-5">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                        <span class="hidden md:inline">Add Account</span>
                    </button>
                </a>
            @endif
        @endauth

        <!-- User Profile Dropdown -->
        <div class="relative hidden sm:block">
            <button id="profileDropdownBtn" class="flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                @auth
                    <span>{{ Auth::user()->name }}</span>
                @endauth
                <svg class="ml-2 w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
            <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-md hidden">
                <a href="{{ route('navbar-account-dropdown') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Accounts</a>
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</button>
                </form>
            </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <button id="mobileMenuBtn" class="sm:hidden p-2 text-gray-400 hover:text-gray-500 focus:outline-none">
            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path id="mobileMenuOpen" class="block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path id="mobileMenuClose" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="sm:hidden hidden absolute top-16 right-0 w-full bg-white border-t border-gray-200">
        <div class="px-4 py-2">
            <a href="{{ route('home') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Dashboard</a>
            @auth
                @if(auth()->user()->accounts->count() > 0)
                    <a href="{{ route('transactions.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Add Transaction</a>
                @else
                    <a onclick="openAccountForm()" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Add Account</a>
                @endif
                <a href="{{ route('navbar-account-dropdown') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Accounts</a>
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</button>
                </form>
            @endauth
        </div>
    </div>

    <!-- Account Form -->
    <div id="accountForm" role="dialog" aria-describedby="radix-:r3:" aria-labelledby="radix-:r2:" data-state="closed" data-vaul-drawer-direction="bottom" data-vaul-drawer="" data-vaul-delayed-snap-points="false" data-vaul-snap-points="false" data-vaul-custom-container="false" data-vaul-animate="true" class="fixed inset-x-0 bottom-0 z-50 mt-24 flex h-auto flex-col rounded-t-[10px] border bg-white transform transition-transform duration-300 ease-in-out translate-y-full" tabindex="-1" style="pointer-events: auto;">
        <div class="mx-auto mt-4 h-2 w-[100px] rounded-full bg-muted"></div>
        <div class="grid gap-1.5 p-4 text-center sm:text-left">
            <h2 id="radix-:r2:" class="text-lg font-semibold leading-none tracking-tight text-center">Create New Account</h2>
        </div>
        <div class="px-4 pb-4">
            <livewire:account-component />
        </div>
    </div>
</nav>

<!-- JavaScript for Profile Dropdown and Mobile Menu -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Profile Dropdown Elements
        const profileBtn = document.getElementById("profileDropdownBtn");
        const profileMenu = document.getElementById("profileDropdown");

        // Mobile Menu Elements
        const mobileMenuBtn = document.getElementById("mobileMenuBtn");
        const mobileMenu = document.getElementById("mobileMenu");
        const mobileMenuOpen = document.getElementById("mobileMenuOpen");
        const mobileMenuClose = document.getElementById("mobileMenuClose");

        // Toggle Profile Dropdown
        if (profileBtn && profileMenu) {
            profileBtn.addEventListener("click", function (event) {
                event.stopPropagation(); // Prevent immediate closing
                profileMenu.classList.toggle("hidden");
            });

            // Close dropdown when clicking outside
            document.addEventListener("click", function (event) {
                if (!profileBtn.contains(event.target) && !profileMenu.contains(event.target)) {
                    profileMenu.classList.add("hidden");
                }
            });
        }

        // Toggle Mobile Menu
        if (mobileMenuBtn && mobileMenu && mobileMenuOpen && mobileMenuClose) {
            mobileMenuBtn.addEventListener("click", function () {
                mobileMenu.classList.toggle("hidden"); // Show/hide mobile menu
                mobileMenuOpen.classList.toggle("hidden");
                mobileMenuClose.classList.toggle("hidden");
            });
        }

        // Livewire Event Listeners for Form Pop-up
        document.addEventListener("DOMContentLoaded", function () {
            Livewire.on('openAccountForm', () => {
                console.log('Event "openAccountForm" received'); // Debugging
                const form = document.getElementById('accountForm');
                if (form) {
                    form.classList.remove('translate-y-full');
                    form.classList.add('translate-y-0');
                }
            });
        });

        Livewire.on('closeAccountForm', () => {
            console.log('closeAccountForm event received'); // Debugging
            const form = document.getElementById('accountForm');
            if (form) {
                form.classList.remove('translate-y-0');
                form.classList.add('translate-y-full');
            }
        });
    });
</script>