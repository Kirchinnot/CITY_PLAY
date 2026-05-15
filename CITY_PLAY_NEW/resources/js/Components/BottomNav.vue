<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-900/95 backdrop-blur-md border-t border-slate-800 pb-safe pt-2 px-6 flex justify-between items-center z-50">
        <Link :href="route('dashboard')" class="flex flex-col items-center gap-1 text-cyan-400">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            <span class="text-[10px] font-medium uppercase tracking-wider">Accueil</span>
        </Link>
        <button class="flex flex-col items-center gap-1 text-slate-400 hover:text-cyan-400 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-10.5v.75m.001 3v.75m0 3v.75m0 3V15m-15 0h15M3 9l3-3m0 0 3 3m-3-3v12m0 0h12m-12 0-3-3m3 3 3-3" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1 1 15 0Z" />
            </svg>
            <span class="text-[10px] font-medium uppercase tracking-wider">Carte</span>
        </button>
        <div class="relative -mt-10">
            <button class="bg-gradient-to-tr from-indigo-600 to-cyan-500 p-4 rounded-full shadow-lg shadow-cyan-500/40 border-4 border-slate-950 active:scale-90 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                </svg>
            </button>
        </div>
        <button class="flex flex-col items-center gap-1 text-slate-400 hover:text-cyan-400 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-2.25c-.621 0-1.125.504-1.125 1.125V18.75m9-7.5V4.875c0-.621-.504-1.125-1.125-1.125h-2.25c-.621 0-1.125.504-1.125 1.125V11.25m9 0h-9m-9 0V4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V11.25m0 0h9" />
            </svg>
            <span class="text-[10px] font-medium uppercase tracking-wider">Classement</span>
        </button>
        <Link :href="route('profile.edit')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-cyan-400 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.121C4.5 15.907 7.907 12.5 12.121 12.5s7.621 3.407 7.621 7.621" />
            </svg>
            <span class="text-[10px] font-medium uppercase tracking-wider">Profil</span>
        </Link>
    </nav>
</template>

<style scoped>
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom, 1rem);
}
</style>
