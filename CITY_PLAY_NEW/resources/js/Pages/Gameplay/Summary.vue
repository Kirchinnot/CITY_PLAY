<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';

const props = defineProps({
    session: Object,
});

const formatTime = (seconds) => {
    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${hrs > 0 ? hrs + 'h ' : ''}${mins}m ${secs}s`;
};
</script>

<template>
    <Head title="Bilan de l'Aventure" />

    <PlayerLayout>
        <div class="max-w-md mx-auto space-y-8 pb-20">
            <!-- Header Bilan -->
            <div class="text-center space-y-2">
                <div class="inline-block bg-[#d65a31]/20 p-4 rounded-full mb-4 shadow-[0_0_50px_rgba(214,90,49,0.3)] border border-[#d65a31]/30">
                    <svg class="w-12 h-12 text-[#d65a31]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h1 class="text-4xl font-black text-white italic tracking-tighter uppercase">Mission Terminée</h1>
                <p class="text-gray-500 font-bold uppercase tracking-widest text-xs">Félicitations pour votre parcours à {{ session.city_name }}</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-[#1c2128] border border-white/5 p-6 rounded-3xl shadow-xl">
                    <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Score Total</span>
                    <span class="text-3xl font-black text-[#d65a31]">{{ session.total_score }}</span>
                    <span class="text-[10px] font-bold text-gray-600 ml-1">pts</span>
                </div>
                <div class="bg-[#1c2128] border border-white/5 p-6 rounded-3xl shadow-xl">
                    <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Temps Total</span>
                    <span class="text-xl font-black text-white">{{ formatTime(session.total_time) }}</span>
                </div>
                <div class="bg-[#1c2128] border border-white/5 p-6 rounded-3xl shadow-xl">
                    <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Lieux</span>
                    <span class="text-2xl font-black text-white">{{ session.places_discovered }}/{{ session.total_places }}</span>
                </div>
                <div class="bg-[#1c2128] border border-white/5 p-6 rounded-3xl shadow-xl">
                    <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Difficulté</span>
                    <span class="text-xs font-black text-white uppercase tracking-widest">{{ session.difficulty }}</span>
                </div>
            </div>

            <!-- Achievements Section -->
            <div v-if="session.achievements && session.achievements.length > 0">
                <h3 class="text-lg font-black text-white mb-4 uppercase tracking-tighter">Badges Débloqués</h3>
                <div class="flex flex-wrap gap-3">
                    <div v-for="achievement in session.achievements" :key="achievement.id" class="bg-[#d65a31]/10 border border-[#d65a31]/20 px-4 py-2 rounded-full flex items-center space-x-2">
                        <span class="text-lg">{{ achievement.icon || '🏆' }}</span>
                        <span class="text-[10px] font-black text-white uppercase tracking-widest">{{ achievement.name }}</span>
                    </div>
                </div>
            </div>

            <!-- Detailed Scores -->
            <div>
                <h3 class="text-lg font-black text-white mb-4 uppercase tracking-tighter">Détail par Énigme</h3>
                <div class="space-y-3">
                    <div v-for="score in session.scores" :key="score.id" class="bg-[#1c2128] border border-white/5 p-4 rounded-2xl flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-white/5 rounded-xl flex items-center justify-center text-xs font-black text-gray-500 italic">
                                #{{ score.riddle?.id }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-white">{{ score.riddle?.title || 'Énigme' }}</p>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ score.hints_used }} indices utilisés</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-black text-white">+{{ score.points_earned }}</p>
                            <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest">Validé</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-4">
                <Link 
                    :href="route('dashboard')"
                    class="w-full h-16 bg-white text-black rounded-2xl font-black uppercase tracking-widest flex items-center justify-center hover:bg-gray-200 transition shadow-2xl"
                >
                    Retour à l'accueil
                </Link>
            </div>
        </div>
    </PlayerLayout>
</template>
