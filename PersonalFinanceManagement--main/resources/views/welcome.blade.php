<x-layout>
    <x-slot:title>Home</x-slot:title>

    <main class="flex flex-col items-center p-6 space-y-10">
        <!-- Hero Section -->
        <section class="w-full max-w-5xl p-12 text-white rounded-lg shadow-xl bg-gradient-to-r from-gray-700 to-black">
            <div class="text-center">
                <h1 class="mb-6 text-4xl font-extrabold">Welcome to TruWealth</h1>
                <p class="text-xl">Your trusted partner in achieving financial freedom and security. At TruWealth, we provide personalized wealth management solutions to help you grow, protect, and manage your finances with confidence. Let us guide you toward a brighter financial future.</p>
            </div>
        </section>

        <!-- Features Section -->
        <section class="grid w-full max-w-5xl grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            <!-- Feature 1 -->
            <div class="p-8 transition-shadow duration-300 bg-white rounded-lg shadow-lg hover:shadow-xl">
                <h2 class="mb-4 text-2xl font-semibold text-gray-800">Personalized Financial Planning</h2>
                <p class="text-gray-600">We offer tailored financial plans designed to meet your unique goals and needs. Whether you're saving for retirement, planning for education, or building wealth, our experts will create a roadmap to help you succeed.</p>
            </div>
            <!-- Feature 2 -->
            <div class="p-8 transition-shadow duration-300 bg-white rounded-lg shadow-lg hover:shadow-xl">
                <h2 class="mb-4 text-2xl font-semibold text-gray-800">Investment Management</h2>
                <p class="text-gray-600">Our team of investment professionals will help you build and manage a diversified portfolio. We focus on maximizing returns while minimizing risks, ensuring your wealth grows steadily over time.</p>
            </div>
            <!-- Feature 3 -->
            <div class="p-8 transition-shadow duration-300 bg-white rounded-lg shadow-lg hover:shadow-xl">
                <h2 class="mb-4 text-2xl font-semibold text-gray-800">Retirement Solutions</h2>
                <p class="text-gray-600">Plan for a secure and comfortable retirement with our comprehensive retirement solutions. We’ll help you save, invest, and strategize to ensure you can enjoy your golden years worry-free.</p>
            </div>
            <!-- Feature 4 -->
            <div class="p-8 transition-shadow duration-300 bg-white rounded-lg shadow-lg hover:shadow-xl">
                <h2 class="mb-4 text-2xl font-semibold text-gray-800">Tax Optimization</h2>
                <p class="text-gray-600">Our tax experts will help you minimize your tax liabilities and maximize your savings. We provide strategies to ensure you keep more of your hard-earned money.</p>
            </div>
            <!-- Feature 5 -->
            <div class="p-8 transition-shadow duration-300 bg-white rounded-lg shadow-lg hover:shadow-xl">
                <h2 class="mb-4 text-2xl font-semibold text-gray-800">Estate Planning</h2>
                <p class="text-gray-600">Protect your legacy with our estate planning services. We’ll help you create a plan to ensure your assets are distributed according to your wishes, while minimizing legal complexities.</p>
            </div>
            <!-- Feature 6 -->
            <div class="p-8 transition-shadow duration-300 bg-white rounded-lg shadow-lg hover:shadow-xl">
                <h2 class="mb-4 text-2xl font-semibold text-gray-800">Risk Management</h2>
                <p class="text-gray-600">Safeguard your wealth with our risk management strategies. We’ll help you identify potential risks and provide solutions to protect your financial future.</p>
            </div>
        </section>

        <!-- Call-to-Action Section -->
        <section class="w-full max-w-5xl p-12 text-center text-white rounded-lg shadow-xl bg-gradient-to-r  from-black to-gray-700">
            <h2 class="mb-6 text-3xl font-extrabold">Ready to Take Control of Your Financial Future?</h2>
            <p class="mb-6 text-xl">Join TruWealth today and start your journey toward financial success. Our team of experts is here to guide you every step of the way. Sign up now to create your personalized wealth management plan!</p>
            <a href="{{ route('register') }}" class="px-6 py-3 font-semibold text-white transition-colors duration-300 bg-gray-600 rounded-md hover:text-white hover:bg-gray-700">
                Get Started
            </a>
        </section>
    </main>
</x-layout>