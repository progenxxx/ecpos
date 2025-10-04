<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const showContent = ref(false);

onMounted(() => {
    showContent.value = true;
});
</script>

<template>
    <Head title="ECPOS - Smart Retail Solutions">
        <meta name="theme-color" content="#001f3f" />
        <link rel="manifest" href="/manifest.json" />
        <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
        <meta name="apple-mobile-web-app-status-bar" content="#001f3f" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="mobile-web-app-capable" content="yes" />
    </Head>
    
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="fixed w-full bg-white shadow-md z-50">
            <nav class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                        <span class="text-base sm:text-lg font-bold text-white">EC</span>
                    </div>
                    <span class="text-base sm:text-xl font-semibold bg-gradient-to-r from-blue-600 to-blue-700 bg-clip-text text-transparent">
                        ECPOS
                    </span>
                </div>

                <div v-if="canLogin" class="flex items-center gap-2 sm:gap-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="px-3 sm:px-6 py-1.5 sm:py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition text-sm sm:text-base">
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="px-3 sm:px-6 py-1.5 sm:py-2 rounded-lg text-blue-600 hover:bg-blue-50 transition text-sm sm:text-base">
                            Sign In
                        </Link>
                        <Link :href="route('register')" class="px-3 sm:px-6 py-1.5 sm:py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition text-sm sm:text-base">
                            License
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="pt-16 sm:pt-20 md:pt-24">
            <section class="bg-gradient-to-b from-blue-600 to-blue-700 text-white text-center py-12 sm:py-16 md:py-20 px-4">
                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-4 sm:mb-6 animate-fade-in" :class="{ 'animate-fade-in': showContent }">
                    Elevate Your Business with Our New POS System
                </h1>
                <p class="text-sm sm:text-base md:text-lg lg:text-xl mb-6 sm:mb-8 max-w-2xl mx-auto" :class="{ 'animate-slide-up': showContent }">
                    Experience seamless transactions, advanced analytics, and user-friendly design.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4 max-w-md sm:max-w-none mx-auto">
                    <a href="#features" class="px-6 sm:px-8 py-2.5 sm:py-3 rounded-lg bg-white text-blue-700 hover:bg-gray-200 transition shadow-lg text-sm sm:text-base">
                        Explore Features
                    </a>
                    <a href="#demo" class="px-6 sm:px-8 py-2.5 sm:py-3 rounded-lg border border-white text-white hover:bg-blue-700 transition text-sm sm:text-base">
                        Watch Demo
                    </a>
                </div>
            </section>

            <!-- Features Section -->
            <div id="features" class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
                <h2 class="text-2xl sm:text-3xl font-bold text-center mb-8 sm:mb-10">Key Features</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    <!-- Feature Cards -->
                    <div class="feature-card group">
                        <div class="h-36 sm:h-48 mb-4 sm:mb-6 bg-white rounded-lg shadow-lg flex items-center justify-center">
                            <img src="../../../public/images/1.webp" alt="Intuitive Interface" class="h-20 sm:h-24">
                        </div>
                        <h3 class="text-lg sm:text-xl font-semibold mb-2 sm:mb-3 text-blue-600 group-hover:text-blue-700">Intuitive Interface</h3>
                        <p class="text-sm sm:text-base text-gray-700">Modern, touch-friendly design that your staff will love.</p>
                    </div>

                    <div class="feature-card group">
                        <div class="h-36 sm:h-48 mb-4 sm:mb-6 bg-white rounded-lg shadow-lg flex items-center justify-center">
                            <img src="../../../public/images/3.webp" alt="Smart Inventory" class="h-20 sm:h-24">
                        </div>
                        <h3 class="text-lg sm:text-xl font-semibold mb-2 sm:mb-3 text-blue-600 group-hover:text-blue-700">Smart Inventory</h3>
                        <p class="text-sm sm:text-base text-gray-700">AI-powered stock management and automated reordering.</p>
                    </div>

                    <div class="feature-card group">
                        <div class="h-36 sm:h-48 mb-4 sm:mb-6 bg-white rounded-lg shadow-lg flex items-center justify-center">
                            <img src="../../../public/images/2.webp" alt="Real-time Analytics" class="h-20 sm:h-24">
                        </div>
                        <h3 class="text-lg sm:text-xl font-semibold mb-2 sm:mb-3 text-blue-600 group-hover:text-blue-700">Real-time Analytics</h3>
                        <p class="text-sm sm:text-base text-gray-700">Make data-driven decisions with powerful insights.</p>
                    </div>
                </div>
            </div>

            <!-- Call to Action Section -->
            <section id="demo" class="bg-blue-700 text-white text-center py-12 sm:py-16 px-4">
                <h2 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-4">Ready to Transform Your Business?</h2>
                <p class="mb-6 sm:mb-8 text-sm sm:text-base">Join countless businesses that trust ECPOS for their POS needs.</p>
                <Link :href="route('register')" class="inline-block px-5 sm:px-6 py-2.5 sm:py-3 rounded-lg bg-white text-blue-700 hover:bg-gray-200 transition shadow-md text-sm sm:text-base">
                    Get Started Today
                </Link>
            </section>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 text-center text-gray-600">
                <p class="text-xs sm:text-sm">Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</p>
                <p class="mt-2 text-xs sm:text-sm">© 2024 ECPOS. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.feature-card {
    @apply p-6 rounded-lg bg-white border border-gray-100 hover:border-blue-200 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl;
}

.animate-fade-in {
    opacity: 0;
    animation: fadeIn 1s ease-out forwards;
}

.animate-slide-up {
    opacity: 0;
    transform: translateY(20px);
    animation: slideUp 1s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
