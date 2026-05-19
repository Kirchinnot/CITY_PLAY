<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { gsap } from 'gsap';

const page = usePage();
const gameState = computed(() => page.props.gameState);

// ── State ───────────────────────────────────────────────────────────────────
const mapContainer = ref(null);
const userLocation = ref(null);
const geoError = ref(null);
const selectedMapPlace = ref(null);
const isProcessingAction = ref(false);
const remainingTimeSeconds = ref(0);

let map = null;
let userMarker = null;
let watchId = null;
let timerInterval = null;
let placeMarkers = [];

// ── Getters ─────────────────────────────────────────────────────────────────
const visiblePlaces = computed(() => {
    if (!gameState.value?.city?.places) return [];
    
    // On récupère les lieux de la session (si disponibles dans gameState ou via une autre prop)
    // Pour simplifier, on utilise les lieux de la ville et on filtre par ceux qui sont dans la session
    return gameState.value.city.places
        .filter(p => {
            // Logique de filtrage : soit complété, soit actuel
            // Note: Il faudrait idéalement que gameState contienne la liste des session_places
            return true; // Pour l'instant on montre tout pour le dev
        });
});

// ── Timer Logic ─────────────────────────────────────────────────────────────
const calculateRemainingTime = () => {
    const timer = gameState.value?.timer;
    if (!timer?.started_at || ['completed', 'abandoned'].includes(gameState.value.status)) {
        remainingTimeSeconds.value = 0;
        return;
    }

    const startedAt = new Date(timer.started_at).getTime();
    const now = gameState.value.status === 'paused' 
        ? new Date(timer.paused_at).getTime() 
        : new Date().getTime();
    
    const totalPauseMs = (timer.total_pause_seconds || 0) * 1000;
    const elapsedMs = now - startedAt - totalPauseMs;
    const availableMs = (timer.available_minutes || 0) * 60 * 1000;
    
    remainingTimeSeconds.value = Math.max(0, Math.floor((availableMs - elapsedMs) / 1000));
};

const formatTime = (seconds) => {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return h > 0 
        ? `${h}h ${m.toString().padStart(2, '0')}m` 
        : `${m}:${s.toString().padStart(2, '0')}`;
};

const timerColorClass = computed(() => {
    if (remainingTimeSeconds.value < 300) return 'text-red-500 animate-pulse';
    if (remainingTimeSeconds.value < 900) return 'text-amber-500';
    return 'text-[#d65a31]';
});

// ── Map & Markers ───────────────────────────────────────────────────────────
const drawMarkers = () => {
    if (!map || !gameState.value) return;
    
    placeMarkers.forEach(m => map.removeLayer(m));
    placeMarkers = [];

    // On utilise les données de gameState pour les marqueurs
    // ...
};

const initMap = () => {
    if (!mapContainer.value || !gameState.value) return;

    const center = [gameState.value.city?.lat || 6.3654, gameState.value.city?.lng || 2.4183];
    map = L.map(mapContainer.value, { zoomControl: false, attributionControl: false }).setView(center, 14);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map);
    
    drawMarkers();
    startTracking();
    
    nextTick(() => map.invalidateSize());
};

const startTracking = () => {
    if (!("geolocation" in navigator)) return;

    watchId = navigator.geolocation.watchPosition(
        (pos) => {
            const { latitude, longitude } = pos.coords;
            userLocation.value = { lat: latitude, lng: longitude };

            if (!userMarker) {
                const icon = L.divIcon({
                    className: 'user-icon',
                    html: `<div class="relative">
                        <div class="absolute -inset-2 bg-blue-500/30 rounded-full animate-ping"></div>
                        <div class="bg-blue-500 w-4 h-4 rounded-full border-2 border-white shadow-lg"></div>
                    </div>`,
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });
                userMarker = L.marker([latitude, longitude], { icon }).addTo(map);
                map.setView([latitude, longitude], 16);
            } else {
                userMarker.setLatLng([latitude, longitude]);
            }
        },
        (err) => { geoError.value = "Activez le GPS pour une meilleure expérience."; },
        { enableHighAccuracy: true }
    );
};

// ── Session Actions ─────────────────────────────────────────────────────────
const handleAction = async (action) => {
    if (isProcessingAction.value) return;
    
    if (action === 'abandon' && !confirm("Abandonner l'aventure ?")) return;

    isProcessingAction.value = true;
    try {
        await axios.post(route(`player.game-sessions.${action}`, gameState.value.id));
        if (action === 'abandon') router.visit(route('player.dashboard'));
        else router.reload();
    } catch (e) {
        console.error(e);
    } finally {
        isProcessingAction.value = false;
    }
};

// ── Lifecycle ───────────────────────────────────────────────────────────────
onMounted(() => {
    setTimeout(initMap, 100);
    timerInterval = setInterval(calculateRemainingTime, 1000);
    calculateRemainingTime();

    // GSAP Intro
    gsap.from(".hud-top", { y: -100, opacity: 0, duration: 1, ease: "power4.out" });
    gsap.from(".hud-bottom", { y: 100, opacity: 0, duration: 1, ease: "power4.out", delay: 0.5 });
});

onUnmounted(() => {
    if (watchId) navigator.geolocation.clearWatch(watchId);
    if (map) map.remove();
    if (timerInterval) clearInterval(timerInterval);
});

watch(() => gameState.value, drawMarkers, { deep: true });
</script>

<template>
    <Head title="CityPlay - Mission Tactique" />

    <PlayerLayout>
        <div class="h-[calc(100vh-160px)] -mt-6 -mx-4 relative overflow-hidden bg-[#0f111a]">
            <!-- Map Container -->
            <div ref="mapContainer" class="w-full h-full z-0"></div>

            <!-- HUD Top: Timer & Score -->
            <div class="hud-top absolute top-6 left-4 right-4 z-10 flex justify-between items-start pointer-events-none">
                <div class="bg-gray-900/90 backdrop-blur-xl border border-white/10 p-4 rounded-3xl shadow-2xl pointer-events-auto">
                    <div class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1">Temps Restant</div>
                    <div class="text-3xl font-black font-mono tracking-tighter" :class="timerColorClass">
                        {{ formatTime(remainingTimeSeconds) }}
                    </div>
                </div>

                <div class="bg-gray-900/90 backdrop-blur-xl border border-white/10 p-4 rounded-3xl shadow-2xl text-right pointer-events-auto">
                    <div class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1">Score Actuel</div>
                    <div class="text-3xl font-black text-white tracking-tighter">
                        {{ gameState?.total_score || 0 }} <span class="text-xs text-orange-500">PTS</span>
                    </div>
                </div>
            </div>

            <!-- HUD Bottom: Current Target -->
            <div class="hud-bottom absolute bottom-10 left-4 right-4 z-10 pointer-events-none">
                <div class="max-w-md mx-auto bg-gray-900/95 backdrop-blur-2xl border border-white/10 rounded-[40px] shadow-[0_32px_64px_-12px_rgba(0,0,0,0.8)] p-6 pointer-events-auto">
                    <div v-if="gameState?.current_riddle" class="flex items-center gap-6">
                        <div class="relative flex-shrink-0">
                            <div class="w-20 h-20 rounded-3xl overflow-hidden border-2 border-orange-500/50 shadow-2xl">
                                <img 
                                    :src="gameState.current_riddle.place?.images?.[0]?.image_path || '/placeholder-place.jpg'" 
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="absolute -bottom-2 -right-2 bg-orange-500 text-white text-[10px] font-black px-2 py-1 rounded-lg shadow-lg">
                                {{ gameState.solved_places + 1 }}/{{ gameState.total_places }}
                            </div>
                        </div>

                        <div class="flex-grow min-w-0">
                            <h3 class="text-white font-black text-xl leading-tight mb-1 truncate">
                                {{ gameState.current_riddle.place?.name || 'Lieu Mystère' }}
                            </h3>
                            <p class="text-gray-400 text-xs font-medium line-clamp-2 mb-4">
                                {{ gameState.current_riddle.question }}
                            </p>
                            
                            <div class="flex gap-3">
                                <Link 
                                    :href="route('player.riddle.show', gameState.current_riddle.id)"
                                    class="flex-grow bg-orange-500 hover:bg-orange-600 text-white text-sm font-black py-3 rounded-2xl transition-all duration-300 text-center shadow-lg shadow-orange-500/20 active:scale-95"
                                >
                                    RÉSOUDRE
                                </Link>
                                <button 
                                    @click="showPauseMenu = true"
                                    class="w-12 bg-white/5 hover:bg-white/10 text-white rounded-2xl flex items-center justify-center transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-4">
                        <div class="animate-pulse flex flex-col items-center">
                            <div class="w-12 h-12 bg-gray-800 rounded-full mb-3"></div>
                            <div class="h-4 w-32 bg-gray-800 rounded mb-2"></div>
                            <div class="h-3 w-48 bg-gray-800 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overlay Pause -->
            <Transition name="fade">
                <div v-if="gameState?.status === 'paused'" class="absolute inset-0 z-50 bg-gray-900/90 backdrop-blur-md flex flex-col items-center justify-center p-8 text-center">
                    <div class="w-24 h-24 bg-orange-500/20 rounded-full flex items-center justify-center mb-8 animate-pulse">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 9v6m4-6v6" />
                        </svg>
                    </div>
                    <h2 class="text-4xl font-black text-white mb-4 tracking-tighter uppercase italic">Mission Suspendue</h2>
                    <p class="text-gray-400 mb-12 max-w-xs font-medium">Le temps est arrêté. Reprenez votre souffle avant de continuer l'aventure.</p>
                    
                    <button 
                        v-if="gameState.is_host"
                        @click="handleAction('resume')"
                        class="w-full max-w-xs bg-white text-gray-900 font-black py-5 rounded-[2rem] text-xl shadow-2xl hover:scale-105 active:scale-95 transition-all mb-4"
                    >
                        REPRENDRE
                    </button>
                    <button 
                        v-if="gameState.is_host"
                        @click="handleAction('abandon')"
                        class="text-red-500 font-bold py-4 hover:underline"
                    >
                        ABANDONNER LA MISSION
                    </button>
                    <div v-else class="text-amber-500 font-bold animate-bounce">
                        En attente du chef de clan...
                    </div>
                </div>
            </Transition>
        </div>
    </PlayerLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.custom-div-icon { background: none; border: none; }

.marker-anim {
    animation: marker-bounce 2s infinite ease-in-out;
}

@keyframes marker-bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
</style>
