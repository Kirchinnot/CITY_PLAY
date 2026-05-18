<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col md:flex-row bg-cityplay-lime/20 overflow-hidden font-sans">
        <!-- Left Side: Illustration & Branding -->
        <div class="hidden md:flex md:w-1/2 bg-cityplay-lime relative items-center justify-center p-12 overflow-hidden">
            <!-- Decorative Waves -->
            <div class="absolute bottom-0 left-0 w-full">
                <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg" class="w-full fill-cityplay-yellow">
                    <path d="M0,192L48,197.3C96,203,192,213,288,192C384,171,480,117,576,112C672,107,768,149,864,154.7C960,160,1056,128,1152,112C1248,96,1344,96,1392,96L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>

            <div class="relative z-10 text-center w-full max-w-lg">
                <div class="aspect-[9/16] w-full max-w-[320px] mx-auto mb-8 transform rotate-1 hover:rotate-0 transition-transform duration-500 shadow-2xl rounded-[2rem] overflow-hidden border-8 border-white bg-white">
                    <img src="/images/auth-explorer.png" alt="CityPlay Explorer" class="w-full h-full object-cover" />
                </div>
                <h1 class="text-5xl font-black text-cityplay-brown mb-2 uppercase tracking-tighter drop-shadow-sm">CityPlay</h1>
                <p class="text-cityplay-brown/80 font-bold text-lg max-w-sm mx-auto leading-tight">
                    Transforme ta ville en terrain de jeu interactif !
                </p>
            </div>
        </div>

        <!-- Right Side: Auth Forms -->
        <div class="flex-1 flex flex-col items-center justify-center p-6 md:p-12 bg-white relative">
            <!-- Mobile Illustration (visible only on small screens) -->
            <div class="md:hidden w-40 aspect-[9/16] mb-6 shadow-xl rounded-2xl overflow-hidden border-4 border-cityplay-lime bg-white">
                <img src="/images/auth-explorer.png" alt="CityPlay Explorer" class="w-full h-full object-cover" />
            </div>

            <div class="w-full max-w-md">
                <div class="mb-8 flex justify-center md:justify-start">
                    <Link href="/">
                        <ApplicationLogo class="h-12 w-auto fill-current text-cityplay-orange" />
                    </Link>
                </div>

                <div class="bg-white p-2">
                    <slot />
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 p-4 opacity-10 pointer-events-none">
                <svg width="100" height="100" viewBox="0 0 100 100" class="text-cityplay-orange fill-current">
                    <circle cx="50" cy="50" r="40" />
                </svg>
            </div>
        </div>
    </div>
</template>
