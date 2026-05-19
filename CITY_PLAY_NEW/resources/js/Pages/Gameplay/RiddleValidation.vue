<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { gsap } from 'gsap';

const page = usePage();
const gameState = computed(() => page.props.gameState);

const props = defineProps({
    riddle: Object,
    unlockedHintIds: Array,
});

// ── GPS & State ─────────────────────────────────────────────────────────────
const userLocation = ref(null);
const distanceToTarget = ref(null);
const watchId = ref(null);
const geoError = ref(null);

const isValidating = ref(false);
const isUnlockingHint = ref(false);
const showResult = ref(false);
const showHints = ref(false);
const resultData = ref(null);
const answerInput = ref('');
const localUnlockedHintIds = ref([...props.unlockedHintIds]);

// ── Lifecycle ───────────────────────────────────────────────────────────────
onMounted(() => {
    startWatchingLocation();
    
    // GSAP Intro
    gsap.from(".riddle-card", { scale: 0.9, opacity: 0, duration: 0.8, ease: "back.out(1.7)" });
    gsap.from(".riddle-image", { y: 20, opacity: 0, duration: 1, ease: "power4.out", delay: 0.3 });
});

onUnmounted(() => {
    if (watchId.value) navigator.geolocation.clearWatch(watchId.value);
});

// ── GPS Logic ───────────────────────────────────────────────────────────────
const startWatchingLocation = () => {
    if (!('geolocation' in navigator)) return;
    
    watchId.value = navigator.geolocation.watchPosition(
        (pos) => {
            userLocation.value = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            calculateDistance();
        },
        (err) => { geoError.value = "GPS requis pour valider."; },
        { enableHighAccuracy: true }
    );
};

const calculateDistance = () => {
    if (!userLocation.value || !props.riddle.place) return;
    const R = 6371e3;
    const φ1 = userLocation.value.lat * Math.PI / 180;
    const φ2 = props.riddle.place.lat * Math.PI / 180;
    const Δφ = (props.riddle.place.lat - userLocation.value.lat) * Math.PI / 180;
    const Δλ = (props.riddle.place.lng - userLocation.value.lng) * Math.PI / 180;
    const a = Math.sin(Δφ/2)**2 + Math.cos(φ1)*Math.cos(φ2)*Math.sin(Δλ/2)**2;
    distanceToTarget.value = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
};

// ── Actions ─────────────────────────────────────────────────────────────────
const submitValidation = async () => {
    if (isValidating.value) return;
    isValidating.value = true;

    try {
        const res = await axios.post(route('player.riddle.validate', props.riddle.id), {
            answer: answerInput.value,
            lat: userLocation.value?.lat,
            lng: userLocation.value?.lng,
        });

        if (res.data.status === 'success') {
            resultData.value = res.data;
            showResult.value = true;
            gsap.to(".riddle-card", { y: -50, opacity: 0, duration: 0.5 });
        }
    } catch (e) {
        alert(e.response?.data?.message || "Erreur de validation");
    } finally {
        isValidating.value = false;
    }
};

const unlockHint = async (hintId) => {
    if (isUnlockingHint.value || localUnlockedHintIds.value.includes(hintId)) return;
    
    isUnlockingHint.value = true;
    try {
        await axios.post(route('player.riddle.unlock-hint', props.riddle.id), { hint_id: hintId });
        localUnlockedHintIds.value.push(hintId);
    } catch (e) {
        console.error(e);
    } finally {
        isUnlockingHint.value = false;
    }
};

const skipRiddle = async () => {
    if (!confirm("Passer cette énigme ? Vous ne gagnerez aucun point.")) return;
    router.post(route('player.riddle.skip', props.riddle.id));
};
</script>

<template>
    <Head :title="`Énigme : ${riddle.place?.name}`" />

    <PlayerLayout>
        <div class="min-h-[calc(100vh-160px)] flex flex-col py-6 px-4">
            
            <!-- Result Overlay (Success) -->
            <Transition name="scale">
                <div v-if="showResult" class="fixed inset-0 z-50 bg-[#0f111a] flex flex-col items-center justify-center p-8 text-center">
                    <div class="w-32 h-32 bg-green-500 rounded-full flex items-center justify-center mb-8 shadow-[0_0_50px_rgba(34,197,94,0.3)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-4xl font-black text-white mb-2 tracking-tighter uppercase italic">Mission Accomplie</h2>
                    <p class="text-gray-400 mb-12 font-medium">Vous avez percé le mystère de {{ riddle.place.name }}.</p>
                    
                    <Link 
                        :href="route('player.game.map')"
                        class="w-full max-w-xs bg-white text-gray-900 font-black py-5 rounded-[2rem] text-xl shadow-2xl hover:scale-105 active:scale-95 transition-all"
                    >
                        CONTINUER
                    </Link>
                </div>
            </Transition>

            <!-- Main Riddle Card -->
            <div v-if="!showResult" class="riddle-card flex-grow bg-gray-900/50 backdrop-blur-xl border border-white/10 rounded-[40px] overflow-hidden shadow-2xl flex flex-col">
                
                <!-- Image Header -->
                <div class="riddle-image h-64 relative flex-shrink-0">
                    <img 
                        :src="riddle.images?.[0]?.image_path || '/placeholder-place.jpg'" 
                        class="w-full h-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent"></div>
                    
                    <div class="absolute top-6 left-6 right-6 flex justify-between items-center">
                        <Link :href="route('player.game.map')" class="w-10 h-10 bg-black/40 backdrop-blur-md rounded-xl flex items-center justify-center text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <div class="px-4 py-2 bg-black/40 backdrop-blur-md rounded-xl text-white text-xs font-black uppercase tracking-widest border border-white/10">
                            {{ riddle.difficulty }}
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-8 flex flex-col flex-grow">
                    <h1 class="text-2xl font-black text-white mb-4 leading-tight">{{ riddle.question }}</h1>
                    
                    <div class="flex-grow">
                        <div v-if="riddle.options && riddle.options.length > 0" class="grid grid-cols-1 gap-3">
                            <button 
                                v-for="(option, index) in riddle.options" 
                                :key="index"
                                @click="answerInput = option"
                                :class="[
                                    'w-full p-5 rounded-2xl text-left font-bold transition-all border-2',
                                    answerInput === option 
                                        ? 'bg-orange-500/20 border-orange-500 text-white' 
                                        : 'bg-white/5 border-white/10 text-gray-400 hover:bg-white/10 hover:border-white/20'
                                ]"
                            >
                                <div class="flex items-center gap-4">
                                    <div :class="[
                                        'w-8 h-8 rounded-full flex items-center justify-center text-xs font-black',
                                        answerInput === option ? 'bg-orange-500 text-white' : 'bg-gray-800 text-gray-500'
                                    ]">
                                        {{ String.fromCharCode(65 + index) }}
                                    </div>
                                    {{ option }}
                                </div>
                            </button>
                        </div>

                        <textarea 
                            v-else
                            v-model="answerInput"
                            placeholder="Votre réponse ici..."
                            class="w-full bg-white/5 border-2 border-white/10 rounded-3xl p-6 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-0 transition-all text-lg font-medium"
                            rows="3"
                        ></textarea>
                        
                        <!-- GPS Status -->
                        <div class="mt-4 flex items-center gap-3 px-4 py-3 bg-white/5 rounded-2xl border border-white/5">
                            <div class="w-2 h-2 rounded-full" :class="distanceToTarget <= 30 ? 'bg-green-500' : 'bg-red-500 animate-pulse'"></div>
                            <span class="text-xs font-bold uppercase tracking-wider" :class="distanceToTarget <= 30 ? 'text-green-500' : 'text-gray-400'">
                                {{ distanceToTarget <= 30 ? 'Zone de validation atteinte' : `À ${Math.round(distanceToTarget || 0)}m de la cible` }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-8 space-y-4">
                        <button 
                            @click="submitValidation"
                            :disabled="isValidating || !answerInput || distanceToTarget > 30"
                            class="w-full bg-orange-500 disabled:bg-gray-800 disabled:text-gray-600 text-white font-black py-5 rounded-[2rem] text-xl shadow-xl shadow-orange-500/20 active:scale-95 transition-all"
                        >
                            {{ isValidating ? 'VÉRIFICATION...' : 'VALIDER' }}
                        </button>

                        <div class="flex gap-4">
                            <button 
                                @click="showHints = !showHints"
                                class="flex-grow bg-white/5 text-white font-bold py-4 rounded-2xl border border-white/10 hover:bg-white/10 transition-all flex items-center justify-center gap-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                INDICES ({{ localUnlockedHintIds.length }}/{{ riddle.hints?.length }})
                            </button>
                            <button 
                                @click="skipRiddle"
                                class="w-16 bg-white/5 text-gray-500 rounded-2xl border border-white/10 flex items-center justify-center"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hints Drawer -->
            <Transition name="slide-up">
                <div v-if="showHints" class="fixed inset-x-0 bottom-0 z-40 bg-gray-900/95 backdrop-blur-2xl border-t border-white/10 rounded-t-[40px] p-8 max-h-[70vh] overflow-y-auto">
                    <div class="w-12 h-1.5 bg-gray-700 rounded-full mx-auto mb-8"></div>
                    <h3 class="text-2xl font-black text-white mb-6 tracking-tight">Besoin d'aide ?</h3>
                    
                    <div class="space-y-4">
                        <div v-for="(hint, index) in riddle.hints" :key="hint.id" class="p-6 rounded-3xl border border-white/10 bg-white/5 transition-all">
                            <div v-if="localUnlockedHintIds.includes(hint.id)">
                                <div class="text-[10px] font-black text-orange-500 uppercase mb-2">Indice {{ index + 1 }}</div>
                                <p class="text-white font-medium">{{ hint.content }}</p>
                            </div>
                            <div v-else class="flex items-center justify-between">
                                <span class="text-gray-400 font-bold italic">Indice caché</span>
                                <button 
                                    @click="unlockHint(hint.id)"
                                    class="bg-white text-gray-900 px-4 py-2 rounded-xl font-black text-xs uppercase"
                                >
                                    DÉBLOQUER (-25 PTS)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </PlayerLayout>
</template>

<style scoped>
.scale-enter-active, .scale-leave-active { transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
.scale-enter-from, .scale-leave-to { opacity: 0; transform: scale(0.9); }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); }
</style>
