<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { gsap } from 'gsap';

const props = defineProps({
    session: Object,
});

const formatTime = (seconds) => {
    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return `${hrs > 0 ? hrs + 'h ' : ''}${mins}m ${secs}s`;
};

// GSAP Animations
onMounted(() => {
    gsap.from(".summary-header", { y: -50, opacity: 0, duration: 1, ease: "power4.out" });
    gsap.from(".stat-card", { 
        scale: 0.8, 
        opacity: 0, 
        duration: 0.8, 
        stagger: 0.1, 
        ease: "back.out(1.7)",
        delay: 0.5 
    });
    gsap.from(".badge-item", { 
        y: 20, 
        opacity: 0, 
        duration: 0.5, 
        stagger: 0.1, 
        delay: 1 
    });
});

// GDPR / Profile Deletion
const showProfileModal = ref(false);
const deleteForm = useForm({
    password: '',
});

const deleteProfile = () => {
    if (!confirm("Cette action supprimera définitivement votre compte et votre progression. Confirmer ?")) return;
    deleteForm.post(route('profile.logout-delete'));
};
</script>

<template>
    <Head title="Bilan de l'Aventure" />

    <PlayerLayout>
        <div class="max-w-md mx-auto space-y-12 pb-24 pt-4 px-4">
            
            <!-- Header Bilan -->
            <div class="summary-header text-center space-y-4">
                <div class="inline-block relative">
                    <div class="absolute -inset-4 bg-orange-500/20 blur-2xl rounded-full"></div>
                    <div class="relative bg-orange-500 p-6 rounded-[2.5rem] shadow-2xl shadow-orange-500/40 border-4 border-white/10">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <h1 class="text-5xl font-black text-white italic tracking-tighter uppercase leading-none">Mission Terminée</h1>
                    <p class="text-orange-500 font-black uppercase tracking-[0.2em] text-[10px] mt-2">Secteur : {{ session.city_name }}</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div class="stat-card bg-white/5 border border-white/10 p-6 rounded-[2.5rem] backdrop-blur-xl">
                    <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Score Total</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-black text-white leading-none">{{ session.total_score }}</span>
                        <span class="text-xs font-black text-orange-500 uppercase">pts</span>
                    </div>
                </div>
                
                <div class="stat-card bg-white/5 border border-white/10 p-6 rounded-[2.5rem] backdrop-blur-xl">
                    <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Temps Final</span>
                    <span class="text-xl font-black text-white leading-none">{{ formatTime(session.total_time) }}</span>
                </div>

                <div class="stat-card bg-white/5 border border-white/10 p-6 rounded-[2.5rem] backdrop-blur-xl">
                    <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Objectifs</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black text-white leading-none">{{ session.solved_places }}</span>
                        <span class="text-xs font-black text-gray-500 uppercase">/ {{ session.total_places }}</span>
                    </div>
                </div>

                <div class="stat-card bg-white/5 border border-white/10 p-6 rounded-[2.5rem] backdrop-blur-xl flex flex-col justify-center">
                    <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Niveau</span>
                    <span class="text-sm font-black text-orange-500 uppercase tracking-widest">{{ session.difficulty }}</span>
                </div>
            </div>

            <!-- Achievements -->
            <div v-if="session.achievements?.length > 0" class="space-y-4">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest text-center">Décorations de Mission</h3>
                <div class="flex flex-wrap justify-center gap-3">
                    <div v-for="achievement in session.achievements" :key="achievement.id" 
                         class="badge-item bg-orange-500/10 border border-orange-500/30 px-5 py-3 rounded-2xl flex items-center gap-3 shadow-lg shadow-orange-500/5">
                        <span class="text-2xl">{{ achievement.icon || '🏆' }}</span>
                        <span class="text-[10px] font-black text-white uppercase tracking-widest">{{ achievement.type }}</span>
                    </div>
                </div>
            </div>

            <!-- Detailed Scores -->
            <div class="space-y-4">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Rapport de Terrain</h3>
                <div class="space-y-3">
                    <div v-for="score in session.scores" :key="score.id" 
                         class="bg-white/5 border border-white/5 p-5 rounded-3xl flex items-center justify-between group hover:bg-white/10 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-white/5 rounded-2xl flex items-center justify-center text-xs font-black text-gray-500 italic border border-white/5">
                                #{{ score.riddle?.id }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-white uppercase">{{ score.riddle?.place?.name || 'Lieu' }}</p>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Secteur Validé</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-black text-orange-500 leading-none">+{{ score.points_earned }}</p>
                            <p class="text-[9px] font-black text-gray-600 uppercase tracking-widest mt-1">PTS</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unsolved Places -->
            <div v-if="session.unsolved_places?.length > 0" class="space-y-4">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Secrets Restants</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="place in session.unsolved_places" :key="place.id"
                         class="bg-white/5 border border-white/5 rounded-[2rem] overflow-hidden group">
                        <div class="h-28 relative">
                            <img :src="place.images?.[0]?.image_url || place.images?.[0]?.image_path || '/placeholder-place.svg'" class="w-full h-full object-cover grayscale opacity-40 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-500" />
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent"></div>
                            <span class="absolute bottom-3 left-4 right-4 text-[10px] font-black text-white uppercase truncate">{{ place.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outro Message -->
            <div v-if="session.outro_config" class="bg-gradient-to-br from-orange-500/20 to-transparent border border-orange-500/20 p-8 rounded-[3rem] text-center shadow-2xl">
                <h3 class="text-xl font-black text-orange-500 mb-3 uppercase italic">Message de la Ville</h3>
                <p class="text-sm text-gray-300 font-medium leading-relaxed italic">
                    "{{ session.outro_config.message || 'Merci d\'avoir exploré nos secrets !' }}"
                </p>
            </div>

            <!-- Final Actions -->
            <div class="pt-8 space-y-4">
                <Link 
                    :href="route('player.dashboard')"
                    class="w-full bg-white text-gray-900 font-black py-5 rounded-[2rem] text-xl shadow-2xl hover:scale-[1.02] active:scale-95 transition-all text-center block"
                >
                    RETOUR AU QUARTIER GÉNÉRAL
                </Link>
                
                <button 
                    @click="showProfileModal = true"
                    class="w-full text-red-500/50 hover:text-red-500 font-black text-xs uppercase tracking-[0.2em] py-4 transition-colors"
                >
                    Terminer la session & Se déconnecter
                </button>
            </div>
        </div>

        <!-- GDPR Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showProfileModal" class="fixed inset-0 z-[100] bg-gray-900/95 backdrop-blur-xl flex items-center justify-center p-6" @click.self="showProfileModal = false">
                    <div class="bg-gray-800 border border-white/10 w-full max-w-sm rounded-[3rem] p-8 shadow-2xl">
                        <div class="w-16 h-16 rounded-3xl bg-red-500/10 flex items-center justify-center mb-6 border border-red-500/20">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-3xl font-black text-white mb-2 tracking-tighter uppercase italic">Fin de Mission</h3>
                        <p class="text-sm text-gray-400 mb-8 font-medium leading-relaxed">
                            Voulez-vous supprimer définitivement votre profil et vos données de jeu conformément au RGPD ?
                        </p>
                        
                        <div class="space-y-3">
                            <button 
                                @click="deleteProfile"
                                class="w-full bg-red-500 text-white font-black py-4 rounded-2xl shadow-lg shadow-red-500/20"
                            >
                                SUPPRIMER TOUT
                            </button>
                            <button 
                                @click="showProfileModal = false"
                                class="w-full bg-white/5 text-white font-black py-4 rounded-2xl border border-white/10"
                            >
                                GARDER MON PROFIL
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </PlayerLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
