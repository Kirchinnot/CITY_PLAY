<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import NotificationDescartes from '@/Components/NotificationDescartes.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const session = computed(() => page.props.session || {});

const isMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#0f111a] text-white font-sans selection:bg-[#d65a31] selection:text-white">
        <!-- Descartes Notifications -->
        <NotificationDescartes />

        <!-- Top Navigation (Desktop style header from mockup) -->
        <nav class="sticky top-0 z-40 bg-[#161b22] border-b border-white/5">
            <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <Link :href="route('dashboard')" class="flex items-center bg-[#d65a31] p-2 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </Link>
                    <span class="font-black text-xl uppercase tracking-tighter">City<span class="text-[#d65a31]">Play</span></span>
                    <span class="hidden sm:inline-block px-3 py-0.5 rounded-full bg-white/5 text-xs text-gray-500 font-bold border border-white/10 ml-2">Bénin</span>
                </div>

                <!-- Nav Tabs Desktop -->
                <div class="hidden md:flex items-center bg-black/20 p-1 rounded-xl border border-white/5">
                    <Link :href="route('dashboard')" :class="[route().current('dashboard') ? 'bg-white/10 text-white shadow-lg' : 'text-gray-500 hover:text-gray-300']" class="px-6 py-2 rounded-lg text-sm font-black uppercase tracking-widest transition">Accueil</Link>
                    <Link v-if="session?.current_riddle" :href="route('riddle.show', session.current_riddle.id)" :class="[route().current('riddle.show') ? 'bg-white/10 text-white shadow-lg' : 'text-gray-500 hover:text-gray-300']" class="px-6 py-2 rounded-lg text-sm font-black uppercase tracking-widest transition">En jeu</Link>
                    <Link :href="route('game.map')" :class="[route().current('game.map') ? 'bg-white/10 text-white shadow-lg' : 'text-gray-500 hover:text-gray-300']" class="px-6 py-2 rounded-lg text-sm font-black uppercase tracking-widest transition">Carte</Link>
                    <Link :href="route('profile.edit')" :class="[route().current('profile.edit') ? 'bg-white/10 text-white shadow-lg' : 'text-gray-500 hover:text-gray-300']" class="px-6 py-2 rounded-lg text-sm font-black uppercase tracking-widest transition">Profil</Link>
                    <Link v-if="user.role === 'admin'" :href="route('dashboard')" class="px-6 py-2 rounded-lg text-sm font-black uppercase tracking-widest text-gray-500 hover:text-gray-300 transition border-l border-white/5">Admin</Link>
                </div>

                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full bg-white/5 relative">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-[#d65a31] rounded-full"></span>
                    </button>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="max-w-7xl mx-auto px-4 py-6 pb-32">
            <slot />
        </main>

        <!-- Bottom Navigation (Mobile style from mockup) -->
        <nav class="fixed bottom-0 inset-x-0 z-40 bg-[#161b22]/95 backdrop-blur-xl border-t border-white/5 pb-safe">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link :href="route('dashboard')" :class="[route().current('dashboard') ? 'text-[#d65a31]' : 'text-gray-500']" class="flex flex-col items-center space-y-1 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">Accueil</span>
                </Link>
                <Link v-if="session?.current_riddle" :href="route('riddle.show', session.current_riddle.id)" :class="[route().current('riddle.show') ? 'text-[#d65a31]' : 'text-gray-500']" class="flex flex-col items-center space-y-1 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">Jeu</span>
                </Link>
                <div v-else class="flex flex-col items-center space-y-1 text-gray-800 cursor-not-allowed">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">Jeu</span>
                </div>
                <Link :href="route('game.map')" :class="[route().current('game.map') ? 'text-[#d65a31]' : 'text-gray-500']" class="flex flex-col items-center space-y-1 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 7"/></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">Carte</span>
                </Link>
                <Link :href="route('profile.edit')" :class="[route().current('profile.edit') ? 'text-[#d65a31]' : 'text-gray-500']" class="flex flex-col items-center space-y-1 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4.58 18.58A14.98 14.98 0 0112 17a14.98 14.98 0 017.42 1.58M4.58 18.58A14.98 14.98 0 0112 20a14.98 14.98 0 007.42-1.42M4.58 18.58L3 21h18l-1.58-2.42"/></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">Profil</span>
                </Link>
            </div>
        </nav>
    </div>
</template>

<style>
.pb-safe {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
