<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BottomNav from '@/Components/BottomNav.vue';

const props = defineProps({
    player: Object,
    activeSession: Object,
    stats: Object,
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-slate-950 text-slate-200 font-sans pb-24">
        <!-- Header Section -->
        <header class="p-6 flex items-center justify-between bg-slate-900/50 backdrop-blur-sm sticky top-0 z-40 border-b border-slate-800">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <img :src="player.avatar" :alt="player.name" class="w-14 h-14 rounded-2xl border-2 border-cyan-500 shadow-lg shadow-cyan-500/20" />
                    <div class="absolute -bottom-1 -right-1 bg-indigo-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-md border border-slate-900">
                        LVL 12
                    </div>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white leading-none">{{ player.name }}</h2>
                    <p class="text-cyan-400 text-sm font-medium mt-1">{{ player.score }} PTS</p>
                </div>
            </div>
            <div class="flex gap-2">
                <div v-for="badge in player.badges.slice(0, 2)" :key="badge.id" class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center text-xl shadow-inner border border-slate-700">
                    {{ badge.icon }}
                </div>
            </div>
        </header>

        <main class="p-6 space-y-8">
            <!-- Main Action -->
            <section>
                <button class="w-full bg-gradient-to-r from-indigo-600 to-cyan-600 p-1 rounded-3xl shadow-xl shadow-indigo-500/20 active:scale-[0.98] transition-transform">
                    <div class="bg-slate-900 rounded-[22px] p-6 flex items-center justify-between border border-white/10">
                        <div class="text-left">
                            <h3 class="text-xl font-black text-white uppercase tracking-tight">Rejoindre une aventure</h3>
                            <p class="text-slate-400 text-sm">Scanner un QR Code ou entrer un code</p>
                        </div>
                        <div class="bg-cyan-500/20 p-3 rounded-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-8 h-8 text-cyan-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-4.5ZM13.5 15.625c0-.552.448-1 1-1h2c.552 0 1 .448 1 1v2c0 .552-.448 1-1 1h-2c-.552 0-1-.448-1-1v-2Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 14.625h.5m-3 0h.5m-3 3h.5m3 0h.5m-3 3h.5m3 0h.5" />
                            </svg>
                        </div>
                    </div>
                </button>
            </section>

            <!-- Active Session -->
            <section v-if="activeSession" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500">Session en cours</h3>
                    <span class="px-2 py-1 bg-orange-500/10 text-orange-500 text-[10px] font-bold rounded-md border border-orange-500/20 uppercase tracking-tighter">Live</span>
                </div>
                <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-24 h-24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h4 class="text-2xl font-black text-white tracking-tight">{{ activeSession.city }}</h4>
                                <div class="flex gap-2 mt-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ activeSession.mode }}</span>
                                    <span class="text-slate-700">•</span>
                                    <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-wider">{{ activeSession.difficulty }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-slate-500 uppercase">Temps écoulé</p>
                                <p class="text-lg font-mono font-bold text-white">{{ activeSession.timeElapsed }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between items-end">
                                <span class="text-xs font-bold text-slate-400 uppercase">Progression</span>
                                <span class="text-xl font-black text-cyan-400">{{ activeSession.progress }}%</span>
                            </div>
                            <div class="h-3 bg-slate-800 rounded-full overflow-hidden p-0.5 border border-slate-700">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-cyan-400 rounded-full shadow-[0_0_10px_rgba(34,211,238,0.5)] transition-all duration-1000" :style="{ width: activeSession.progress + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats Grid -->
            <section class="space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500">Statistiques</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-900 p-5 rounded-3xl border border-slate-800">
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Énigmes</p>
                        <p class="text-2xl font-black text-white">{{ stats.enigmasSolved }}</p>
                        <div class="mt-3 flex items-center gap-1 text-[10px] font-bold text-cyan-400">
                            <span>+3 cette semaine</span>
                        </div>
                    </div>
                    <div class="bg-slate-900 p-5 rounded-3xl border border-slate-800">
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Villes</p>
                        <p class="text-2xl font-black text-white">{{ stats.citiesExplored }}</p>
                        <div class="mt-3 flex items-center gap-1 text-[10px] font-bold text-indigo-400">
                            <span>Explorateur confirmé</span>
                        </div>
                    </div>
                    <div class="bg-slate-900 p-5 rounded-3xl border border-slate-800">
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Rang actuel</p>
                        <p class="text-lg font-black text-orange-500 uppercase tracking-tighter">{{ stats.rank }}</p>
                        <div class="mt-2 w-full h-1 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-orange-500" style="width: 40%"></div>
                        </div>
                    </div>
                    <div class="bg-slate-900 p-5 rounded-3xl border border-slate-800">
                        <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Points total</p>
                        <p class="text-2xl font-black text-white">{{ stats.totalPoints.toLocaleString() }}</p>
                        <div class="mt-3 flex items-center gap-1 text-[10px] font-bold text-slate-400">
                            <span>Top 15% mondial</span>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <BottomNav />
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');

.font-sans {
    font-family: 'Space Grotesk', sans-serif;
}

/* Custom scrollbar for mobile feel */
::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}
</style>
