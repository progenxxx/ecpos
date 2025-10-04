<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in">
        <meta name="theme-color" content="#000000" />
        <link rel="manifest" href="/manifest.json" />
        <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
        <meta name="apple-mobile-web-app-status-bar" content="#000000" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="mobile-web-app-capable" content="yes" />
    </Head>

    <header class="fixed w-full bg-white shadow-md z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex justify-between items-center">
            <div class="flex justify-between items-center w-full">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-lg">
                        <span class="text-base sm:text-lg font-bold text-white">EC</span>
                    </div>
                    <span class="text-base sm:text-xl font-semibold bg-gradient-to-r from-blue-600 to-blue-700 bg-clip-text text-transparent">
                        <a href="/">ECPOS</a>
                    </span>
                </div>

                <span class="hidden md:block text-lg sm:text-xl font-semibold bg-gradient-to-r from-gray-600 to-gray-700 bg-clip-text text-transparent">
                    <a href="/">Transform your business now!</a>
                </span>
            </div>


            <div v-if="canLogin" class="hidden sm:flex items-center gap-4">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="px-4 sm:px-6 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition text-sm sm:text-base">
                    Dashboard
                </Link>
                <template v-else>
                    <Link :href="route('login')" class="px-4 sm:px-6 py-2 rounded-lg text-blue-600 hover:bg-blue-50 transition text-sm sm:text-base">
                        Sign In
                    </Link>
                    <Link :href="route('register')" class="px-4 sm:px-6 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition text-sm sm:text-base">
                        Get Started
                    </Link>
                </template>
            </div>
        </nav>
    </header>

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
        {{ status }}
    </div>

    <div class="font-sans flex justify-center items-center min-h-screen bg-gray-100">
        <section class="flex justify-center items-center bg-gradient-to-b from-blue-300 to-blue-700 text-white text-center py-12 sm:py-20 w-full relative overflow-hidden">
    <!-- Background Video -->
    <video autoplay muted loop class="absolute top-0 left-0 w-full h-full object-cover opacity-30">
        <source src="../../../../public/images/videos/ads.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="relative sm:max-w-sm w-full max-w-[90%] flex flex-col items-center z-10 px-4">
        <div class="relative w-full rounded-2xl sm:rounded-3xl px-4 sm:px-6 py-4 bg-gray-100 shadow-md">
            <label for="" class="block mt-3 text-sm sm:text-base text-gray-700 text-center font-semibold">Login</label>
            <form @submit.prevent="submit">
                <div>
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Email"
                        class="mt-1 block w-full border-none !bg-gray-100 !text-navy h-10 sm:h-11 rounded-xl shadow-lg hover:bg-blue-100 focus:bg-blue-100 focus:ring-0 text-sm sm:text-base"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-5 sm:mt-7">
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Password"
                        class="mt-1 block w-full border-none bg-gray-100 h-10 sm:h-11 rounded-xl shadow-lg hover:bg-blue-100 focus:bg-blue-100 focus:ring-0 text-sm sm:text-base"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-5 sm:mt-7 flex flex-col sm:flex-row gap-2 sm:gap-0">
                    <label for="remember_me" class="inline-flex items-center w-full cursor-pointer">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" name="remember">
                        <span class="ml-2 text-xs sm:text-sm text-gray-600">Remember me</span>
                    </label>
                    <div class="w-full text-left sm:text-right">
                        <a class="underline text-xs sm:text-sm text-gray-600 hover:text-gray-900" href="#">Forgot your password?</a>
                    </div>
                </div>

                <div class="mt-5 sm:mt-7 mb-2">
                    <button
                        class="bg-blue-500 w-full py-2.5 sm:py-3 rounded-xl text-white shadow-xl hover:shadow-inner focus:outline-none transition duration-500 ease-in-out transform hover:-translate-x hover:scale-105 text-sm sm:text-base"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                    >
                        Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

    </div>

    <footer class="bg-white shadow-md text-gray-500 py-3 sm:py-4 fixed bottom-0 w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-center">
            <p class="text-xs sm:text-sm">&copy; 2024 Eljin Corp. | MRPA</p>
        </div>
    </footer>
</template>

