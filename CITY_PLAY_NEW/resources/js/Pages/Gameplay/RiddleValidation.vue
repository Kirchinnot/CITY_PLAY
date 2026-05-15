<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    riddle: Object,
    session: Object,
    unlockedHintIds: Array,
});

const userLocation = ref(null);
const distanceToTarget = ref(null);
const isValidating = ref(false);
const isUnlockingHint = ref(false);
const showResult = ref(false);
const showHints = ref(false);
const resultData = ref(null);
const localUnlockedHintIds = ref([...props.unlockedHintIds]);
const watchId = ref(null);
const geoError = ref(null);
const answerError = ref(null);
const answerInput = ref('');

const unlockedHintsCount = computed(() => localUnlockedHintIds.value.length);

const currentLat = ref(null);
const currentLng = ref(null);

const selectOption = (option) => {
    answerInput.value = option;
    submitValidation();
};

const unlockNextHint = async () => {
    const nextHintIndex = localUnlockedHintIds.value.length;
    if (nextHintIndex < props.riddle.hints.length) {
        const nextHint = props.riddle.hints[nextHintIndex];
        
        isUnlockingHint.value = true;
        try {
            const response = await axios.post(route('riddle.unlock-hint', props.riddle.id), {
                session_id: props.session.id,
                hint_id: nextHint.id
            });
            
            localUnlockedHintIds.value.push(nextHint.id);
            isUnlockingHint.value = false;
        } catch (error) {
            console.error("Erreur lors du déblocage de l'indice:", error);
            isUnlockingHint.value = false;
        }
    }
};

// Suivi de la position GPS
const startWatchingLocation = () => {
    if ("geolocation" in navigator) {
        geoError.value = null;
        watchId.value = navigator.geolocation.watchPosition(
            (position) => {
                userLocation.value = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                currentLat.value = position.coords.latitude;
                currentLng.value = position.coords.longitude;
                calculateDistanceToTarget();
            },
            (error) => {
                console.error("Erreur géolocalisation:", error);
                if (error.code === 1) {
                    geoError.value = "Vous devez autoriser la géolocalisation pour jouer.";
                } else {
                    geoError.value = "Impossible de récupérer votre position.";
                }
            },
            { enableHighAccuracy: true }
        );
    } else {
        geoError.value = "Votre navigateur ne supporte pas la géolocalisation.";
    }
};

const calculateDistanceToTarget = () => {
    if (!userLocation.value || !props.riddle.place) return;
    
    const R = 6371e3; // mètres
    const φ1 = userLocation.value.lat * Math.PI/180;
    // Place utilise lat/lng (pas latitude/longitude)
    const placeLat = props.riddle.place.lat ?? props.riddle.place.latitude;
    const placeLng = props.riddle.place.lng ?? props.riddle.place.longitude;
    const φ2 = placeLat * Math.PI/180;
    const Δφ = (placeLat - userLocation.value.lat) * Math.PI/180;
    const Δλ = (placeLng - userLocation.value.lng) * Math.PI/180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    distanceToTarget.value = R * c;
};

const submitValidation = async () => {
    isValidating.value = true;
    answerError.value = null;
    try {
        const response = await axios.post(route('riddle.validate', props.riddle.id), {
            session_id: props.session.id,
            latitude: currentLat.value,
            longitude: currentLng.value,
            answer: answerInput.value,
        });
        resultData.value = response.data;
        showResult.value = true;
    } catch (error) {
        const msg = error.response?.data?.message || 'Une erreur est survenue.';
        if (error.response?.status === 422) {
            answerError.value = msg;
        } else {
            alert(msg);
        }
    } finally {
        isValidating.value = false;
    }
};

onMounted(() => {
    startWatchingLocation();
});

onUnmounted(() => {
    if (watchId.value) navigator.geolocation.clearWatch(watchId.value);
});
</script>

<template>
    <Head :title="`Énigme : ${riddle.title}`" />

    <PlayerLayout>
        <div class="max-w-md mx-auto space-y-6 pb-20">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-black text-white italic tracking-tighter uppercase">Énigme en cours</h1>
                <span class="bg-[#d65a31] text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">
                    Force {{ riddle.difficulty === 'force_3' ? '3' : riddle.difficulty === 'force_2' ? '2' : '1' }}
                </span>
            </div>

            <!-- Geolocation Error Alert -->
            <div v-if="geoError" class="bg-red-500/20 border border-red-500/50 rounded-2xl p-4 text-center">
                <p class="text-red-500 text-xs font-black uppercase tracking-widest mb-3">{{ geoError }}</p>
                <button @click="startWatchingLocation" class="text-[10px] font-black text-white underline uppercase tracking-widest">Réessayer</button>
            </div>

            <!-- Riddle Card -->
            <div class="bg-[#1c2128] rounded-3xl p-6 border border-white/5 shadow-2xl relative overflow-hidden">
                <!-- Decorative Border Left -->
                <div class="absolute left-0 top-6 bottom-6 w-1 bg-[#d65a31] rounded-r-full"></div>
                
                <div class="pl-4 space-y-4">
                    <div class="flex items-center space-x-2 text-[10px] font-black text-gray-500 uppercase tracking-widest">
                        <span>Lieu {{ session.solved_places + 1 }} / {{ session.total_places }}</span>
                        <span>—</span>
                        <span class="text-[#d65a31]">{{ riddle.place?.name }}</span>
                    </div>

                    <p class="text-white text-xl font-bold leading-relaxed italic">
                        "{{ riddle.question }}"
                    </p>

                    <div class="bg-white/5 rounded-xl p-4 flex items-center space-x-3 border border-white/10">
                        <div class="bg-[#d65a31]/20 p-2 rounded-lg">
                            <svg class="w-4 h-4 text-[#d65a31]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-tight">
                            Rends-toi physiquement sur le lieu pour valider
                        </p>
                    </div>
                </div>
            </div>

            <!-- Map Placeholder / Area -->
            <div class="h-48 bg-[#161b22] rounded-3xl border border-white/5 relative overflow-hidden">
                <div class="absolute inset-0 flex items-center justify-center opacity-20">
                    <svg class="w-full h-full" viewBox="0 0 200 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 50h200M100 0v100M50 0v100M150 0v100" stroke="white" stroke-width="0.5" />
                        <circle cx="100" cy="50" r="10" stroke="#d65a31" stroke-width="2" stroke-dasharray="4 4" />
                        <circle cx="100" cy="50" r="4" fill="#d65a31" />
                    </svg>
                </div>
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-[#1c2128]/80 backdrop-blur px-4 py-2 rounded-full border border-white/5">
                    <span class="text-[10px] font-black text-white uppercase tracking-widest">{{ riddle.place?.name }}</span>
                </div>
            </div>

            <!-- Action Area -->
            <div class="space-y-4">
                <!-- Multiple Choice Options (QCM) -->
                <div v-if="riddle.options && riddle.options.length > 0" class="grid grid-cols-1 gap-3">
                    <button 
                        v-for="(option, index) in riddle.options" 
                        :key="index"
                        @click="selectOption(option)"
                        :disabled="distanceToTarget === null || distanceToTarget > (riddle.place?.validation_radius || 30) || isValidating"
                        class="w-full h-14 bg-[#1c2128] border border-white/10 rounded-2xl text-white px-6 font-bold text-left hover:border-[#d65a31] transition-all flex items-center justify-between group disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span>{{ option }}</span>
                        <div class="w-6 h-6 rounded-full border-2 border-white/10 group-hover:border-[#d65a31] flex items-center justify-center transition">
                            <div class="w-2 h-2 rounded-full bg-[#d65a31] scale-0 group-hover:scale-100 transition"></div>
                        </div>
                    </button>
                </div>

                <!-- Text Input (if no options) -->
                <div v-else-if="riddle.answer" class="space-y-2">
                    <input
                        type="text"
                        v-model="answerInput"
                        placeholder="Tapez votre réponse ici..."
                        class="w-full h-14 bg-[#1c2128] border-white/5 rounded-2xl text-white px-6 font-bold placeholder-gray-600 focus:border-[#d65a31] focus:ring-0 transition-all"
                    />
                    <InputError :message="answerError" />
                </div>

                <!-- GPS Validation Button (affiché uniquement si pas de QCM) -->
                <div v-if="!riddle.options || riddle.options.length === 0" class="flex items-center space-x-3">
                    <button 
                        @click="submitValidation"
                        :disabled="distanceToTarget === null || distanceToTarget > (riddle.place?.validation_radius || 30) || isValidating"
                        class="flex-1 h-16 rounded-2xl flex items-center justify-center space-x-3 transition-all font-black uppercase tracking-widest active:scale-95"
                        :class="[
                            distanceToTarget !== null && distanceToTarget <= (riddle.place?.validation_radius || 30)
                            ? 'bg-white text-black shadow-[0_10px_30px_rgba(255,255,255,0.1)] hover:bg-gray-100' 
                            : 'bg-white/5 text-gray-500 border border-white/5 cursor-not-allowed'
                        ]"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        <span>{{ isValidating ? 'Vérification...' : 'Je suis sur place' }}</span>
                    </button>

                    <!-- Hint Button (Lightbulb) -->
                    <button 
                        @click="showHints = true"
                        class="w-16 h-16 bg-[#1c2128] border border-white/10 rounded-2xl flex items-center justify-center text-[#d65a31] hover:bg-white/5 transition relative"
                    >
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <span v-if="unlockedHintsCount < riddle.hints?.length" class="absolute -top-1 -right-1 w-5 h-5 bg-[#d65a31] text-white text-[10px] font-black flex items-center justify-center rounded-full border-2 border-[#0f111a]">
                            {{ riddle.hints.length - unlockedHintsCount }}
                        </span>
                    </button>
                </div>

                <!-- Pour les QCM : bouton indice seul -->
                <div v-else class="flex justify-end">
                    <button 
                        @click="showHints = true"
                        class="w-16 h-16 bg-[#1c2128] border border-white/10 rounded-2xl flex items-center justify-center text-[#d65a31] hover:bg-white/5 transition relative"
                    >
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <span v-if="unlockedHintsCount < riddle.hints?.length" class="absolute -top-1 -right-1 w-5 h-5 bg-[#d65a31] text-white text-[10px] font-black flex items-center justify-center rounded-full border-2 border-[#0f111a]">
                            {{ riddle.hints.length - unlockedHintsCount }}
                        </span>
                    </button>
                </div>

                <!-- Distance indicator -->
                <div v-if="distanceToTarget !== null" class="text-center">
                    <span v-if="distanceToTarget > (riddle.place?.validation_radius || 30)" class="text-xs font-bold text-gray-500">
                        📍 {{ Math.round(distanceToTarget) }}m du lieu cible
                    </span>
                    <span v-else class="text-xs font-bold text-green-500">
                        ✓ Vous êtes sur place !
                    </span>
                </div>
                <div v-else-if="!geoError" class="text-center">
                    <span class="text-xs font-bold text-gray-600">Localisation en cours...</span>
                </div>

                <!-- Footer Stats -->
                <div class="flex items-center justify-between pt-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-1">
                            <span class="text-green-500 font-black text-xs">{{ session.total_score || 0 }} pts</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span class="text-gray-500 font-black text-xs">{{ session.available_minutes }}min</span>
                        </div>
                    </div>
                    <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Mode: {{ session.mode }}</div>
                </div>
            </div>
        </div>

        <!-- Result Modal (Same as before but styled for new theme) -->
        <div v-if="showResult" class="fixed inset-0 z-[60] flex items-center justify-center p-6 bg-[#0f111a]/95 backdrop-blur-xl">
            <div class="max-w-xs w-full text-center animate-bounce-in">
                <div class="w-24 h-24 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-8 shadow-[0_0_50px_rgba(34,197,94,0.4)]">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h2 class="text-4xl font-black text-white italic tracking-tighter uppercase mb-2">Gagné !</h2>
                <p class="text-[#d65a31] font-black uppercase tracking-widest text-xs mb-10">Lieu découvert avec succès</p>
                
                <div class="grid grid-cols-2 gap-4 mb-10">
                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl">
                        <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Points</span>
                        <span class="text-2xl font-black text-white">+{{ resultData?.score }}</span>
                    </div>
                    <div class="bg-white/5 border border-white/10 p-4 rounded-2xl">
                        <span class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Temps</span>
                        <span class="text-2xl font-black text-white">{{ Math.floor(resultData?.details?.time_taken / 60) }}m {{ resultData?.details?.time_taken % 60 }}s</span>
                    </div>
                </div>

                <button 
                    @click="resultData?.is_finished ? router.visit(route('game-sessions.summary', resultData.session_id)) : router.visit(route('dashboard'))" 
                    class="w-full h-16 bg-white text-black rounded-2xl font-black uppercase tracking-widest hover:bg-gray-200 transition"
                >
                    {{ resultData?.is_finished ? 'Voir le bilan' : 'Continuer' }}
                </button>
            </div>
        </div>

        <!-- Hints Modal -->
        <div v-if="showHints" class="fixed inset-0 z-[70] flex items-center justify-center p-6 bg-[#0f111a]/95 backdrop-blur-xl">
            <div class="max-w-md w-full bg-[#1c2128] rounded-3xl border border-white/10 p-8 shadow-2xl">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-black text-white italic tracking-tighter uppercase">Indices</h2>
                    <button @click="showHints = false" class="text-gray-500 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <div v-for="(hint, index) in riddle.hints" :key="hint.id" class="p-4 rounded-2xl border transition-all" :class="index < unlockedHintsCount ? 'bg-white/5 border-white/10' : 'bg-black/20 border-white/5 opacity-50'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black text-[#d65a31] uppercase tracking-widest">Indice {{ index + 1 }}</span>
                            <svg v-if="index >= unlockedHintsCount" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <p v-if="index < unlockedHintsCount" class="text-white font-bold leading-relaxed">
                            {{ hint.content }}
                        </p>
                        <p v-else class="text-gray-600 text-sm font-bold italic">
                            Débloquez cet indice pour obtenir de l'aide
                        </p>
                    </div>

                    <button 
                        v-if="unlockedHintsCount < riddle.hints?.length"
                        @click="unlockNextHint"
                        :disabled="isUnlockingHint"
                        class="w-full h-14 bg-[#d65a31] text-white rounded-2xl font-black uppercase tracking-widest hover:bg-[#b84a26] transition active:scale-95 flex items-center justify-center space-x-2 shadow-[0_10px_30px_rgba(214,90,49,0.2)] disabled:opacity-50"
                    >
                        <svg v-if="!isUnlockingHint" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <svg v-else class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>{{ isUnlockingHint ? 'Déblocage...' : `Débloquer l'indice (${(unlockedHintsCount + 1) * 30} pts)` }}</span>
                    </button>
                </div>
            </div>
        </div>
    </PlayerLayout>
</template>

<style scoped>
.animate-bounce-in {
    animation: bounceIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.05); }
    70% { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}
</style>
