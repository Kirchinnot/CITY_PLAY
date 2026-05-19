<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
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

// GDPR / Profile Deletion
const showProfileModal = ref(false);
const retentionDays = ref(props.session.outro_config?.retention_days || 30);
const deleteForm = useForm({
    password: '',
});

const deleteProfile = () => {
    // In a real flow, we'd prompt for password or directly delete if 2FA/auth allows.
    // For this prototype, we'll route to a dedicated GDPR purge route or standard profile destroy.
    deleteForm.delete(route('player.profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => showProfileModal.value = false,
    });
};

const userLat = ref(null);
const userLng = ref(null);
const selectedUnsolvedPlace = ref(null);

onMounted(() => {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition((position) => {
            userLat.value = position.coords.latitude;
            userLng.value = position.coords.longitude;
        });
    }
});

const calculateDistance = (lat2, lon2) => {
    if (!userLat.value || !userLng.value) return 'Calcul...';
    const lat1 = userLat.value;
    const lon1 = userLng.value;
    const R = 6371; // Rayon de la terre en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
    const distanceKm = R * c;
    
    if (distanceKm < 1) {
        return Math.round(distanceKm * 1000) + ' m';
    }
    return distanceKm.toFixed(1) + ' km';
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

            <!-- Unsolved Places Section -->
            <div v-if="session.unsolved_places && session.unsolved_places.length > 0" class="mt-8">
                <h3 class="text-lg font-black text-white mb-4 uppercase tracking-tighter">Lieux Non Découverts</h3>
                <p class="text-xs text-gray-400 mb-4">Découvrez les secrets que vous avez manqués :</p>
                <div class="grid grid-cols-2 gap-3">
                    <div v-for="place in session.unsolved_places" :key="place.id"
                         @click="selectedUnsolvedPlace = place"
                         class="bg-[#1c2128] border border-white/5 rounded-2xl overflow-hidden shadow-lg cursor-pointer hover:border-[#d65a31]/50 transition group">
                        <div class="h-24 bg-gray-800 relative">
                            <img v-if="place.images && place.images[0]" :src="'/storage/' + place.images[0].path" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition" />
                            <div v-else class="w-full h-full flex items-center justify-center text-3xl bg-gray-900">🏛️</div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                            <span class="absolute bottom-2 left-2 text-xs font-black text-white uppercase truncate pr-2 w-full">{{ place.name }}</span>
                        </div>
                        <div class="p-3 bg-[#1c2128]">
                            <span class="text-[10px] font-bold text-[#d65a31] uppercase tracking-widest flex items-center gap-1">
                                📍 {{ calculateDistance(place.latitude, place.longitude) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conclusion Mairie (Outro) -->
            <div v-if="session.outro_config" class="mt-8 bg-gradient-to-br from-[#d65a31]/10 to-transparent border border-[#d65a31]/20 p-6 rounded-3xl shadow-xl text-center">
                <h3 class="text-lg font-black text-[#d65a31] mb-2 uppercase tracking-tighter">Mot de la fin</h3>
                <p class="text-sm text-gray-300 mb-4 leading-relaxed font-medium">
                    {{ session.outro_config.message || 'Merci d\'avoir joué !' }}
                </p>
                <div v-if="session.outro_config.recommendations" class="text-left bg-black/20 rounded-xl p-4 border border-white/5">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Recommandations de la Mairie :</h4>
                    <p class="text-xs text-white leading-relaxed whitespace-pre-line">{{ session.outro_config.recommendations }}</p>
                </div>
            </div>

            <!-- Actions & GDPR -->
            <div class="pt-4 space-y-4">
                <Link 
                    :href="route('player.dashboard')"
                    class="w-full h-16 bg-white text-black rounded-2xl font-black uppercase tracking-widest flex items-center justify-center hover:bg-gray-200 transition shadow-2xl"
                >
                    Retour à l'accueil
                </Link>
                
                <button 
                    @click="showProfileModal = true"
                    class="w-full h-16 bg-transparent border border-red-500/30 text-red-500 rounded-2xl font-black uppercase tracking-widest flex items-center justify-center hover:bg-red-500/10 transition"
                >
                    Déconnexion & Gestion Profil
                </button>
            </div>
        </div>

        <!-- Modal GDPR : Conservation ou Suppression -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition-all duration-150" leave-to-class="opacity-0 translate-y-4">
                <div v-if="showProfileModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="background: rgba(13,17,23,0.9); backdrop-filter: blur(12px);" @click.self="showProfileModal = false">
                    <div class="bg-[#1c2128] border border-white/10 w-full max-w-sm rounded-[28px] overflow-hidden shadow-2xl p-6">
                        <div class="w-12 h-12 rounded-full bg-red-500/10 flex items-center justify-center mb-4 border border-red-500/20">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-white italic tracking-tighter uppercase mb-2">Fin de session</h3>
                        <p class="text-sm text-gray-300 mb-6 font-medium leading-relaxed">
                            Que souhaitez-vous faire de votre compte joueur ?<br><br>
                            Si vous choisissez de le conserver, vos données seront gardées pendant <strong class="text-white">{{ retentionDays }} jours</strong> conformément aux règles de la mairie.
                        </p>
                        
                        <div class="space-y-3">
                            <Link :href="route('logout')" method="post" as="button" class="w-full py-3 bg-[#d65a31] text-white rounded-xl font-black uppercase tracking-widest text-xs hover:bg-[#b84a24] transition">
                                Conserver & Se déconnecter
                            </Link>
                            
                            <button @click="deleteProfile" class="w-full py-3 bg-transparent border border-white/10 text-gray-400 rounded-xl font-bold uppercase tracking-widest text-xs hover:bg-red-500/10 hover:text-red-500 hover:border-red-500/30 transition">
                                {{ deleteForm.processing ? 'Suppression...' : 'Supprimer mon profil' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Modal Lieu Non Résolu -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition-all duration-150" leave-to-class="opacity-0 translate-y-4">
                <div v-if="selectedUnsolvedPlace" class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="background: rgba(13,17,23,0.9); backdrop-filter: blur(12px);" @click.self="selectedUnsolvedPlace = null">
                    <div class="bg-[#1c2128] border border-white/10 w-full max-w-sm rounded-[28px] overflow-hidden shadow-2xl animate-bounce-in flex flex-col max-h-[80vh]">
                        <div class="relative h-48 shrink-0 bg-gray-800">
                            <img v-if="selectedUnsolvedPlace.images && selectedUnsolvedPlace.images[0]" :src="'/storage/' + selectedUnsolvedPlace.images[0].path" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center text-5xl bg-gray-900">🏛️</div>
                            <div class="absolute top-4 right-4 bg-black/50 backdrop-blur-md px-3 py-1 rounded-full border border-white/10 text-xs font-black text-white">
                                📍 À {{ calculateDistance(selectedUnsolvedPlace.latitude, selectedUnsolvedPlace.longitude) }}
                            </div>
                            <button @click="selectedUnsolvedPlace = null" class="absolute top-4 left-4 w-8 h-8 bg-black/50 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/10 transition hover:bg-black/80">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="p-6 overflow-y-auto scrollbar-hide">
                            <h3 class="text-2xl font-black text-white italic tracking-tighter uppercase mb-2">{{ selectedUnsolvedPlace.name }}</h3>
                            <h4 class="text-[10px] font-black text-[#d65a31] uppercase tracking-widest mb-4">Ce que vous avez manqué</h4>
                            <p class="text-sm text-gray-300 leading-relaxed font-medium">
                                {{ selectedUnsolvedPlace.description || 'Aucune description disponible pour ce lieu.' }}
                            </p>
                            
                            <div v-if="selectedUnsolvedPlace.images && selectedUnsolvedPlace.images.length > 1" class="mt-6">
                                <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-2">Autres photos</h4>
                                <div class="flex gap-2 overflow-x-auto snap-x scrollbar-hide pb-2">
                                    <img v-for="(img, idx) in selectedUnsolvedPlace.images.slice(1, 4)" :key="idx" :src="'/storage/' + img.path" class="w-24 h-24 object-cover rounded-xl shrink-0 snap-center shadow-md border border-white/10" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </PlayerLayout>
</template>
