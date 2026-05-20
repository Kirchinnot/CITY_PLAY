<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import { alertModal, confirmModal } from '@/composables/usePrimeDialogs';
import 'leaflet/dist/leaflet.css';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { gsap } from 'gsap';
import { useGameTimer } from '@/composables/useGameTimer';

const page = usePage();
const gameState = computed(() => page.props.gameState);

// ── State ───────────────────────────────────────────────────────────────────
const mapContainer = ref(null);
const userLocation = ref(null);
const geoError = ref(null);
const selectedMapPlace = ref(null);
const isProcessingAction = ref(false);

let map = null;
let userMarker = null;
let watchId = null;
let placeMarkers = [];

const {
    remainingSeconds: remainingTimeSeconds,
    timerColorClass,
    timeAlertMessage,
    formatTime,
    start: startTimer,
    stop: stopTimer,
} = useGameTimer(gameState);

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

const remainingPlaces = computed(() => {
    const total = gameState.value?.total_places ?? 0;
    const solved = gameState.value?.solved_places ?? 0;
    return Math.max(total - solved, 0);
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
    if (isProcessingAction.value || !gameState.value?.is_host) return;

    if (action === 'abandon' && !(await confirmModal({
        header: 'Abandon de la partie',
        message: 'Abandonner l\'aventure ?',
        acceptLabel: 'Oui, abandonner',
        rejectLabel: 'Rester',
    }))) return;

    if (action === 'pause' && !(await confirmModal({
        header: 'Pause de la mission',
        message: 'Mettre la mission en pause ? Le chronomètre sera arrêté.',
        acceptLabel: 'Oui, mettre en pause',
        rejectLabel: 'Non',
    }))) return;

    isProcessingAction.value = true;
    try {
        await axios.post(route(`player.game-sessions.${action}`, gameState.value.id));
        if (action === 'abandon') {
            router.visit(route('player.dashboard'));
        } else {
            router.reload({ preserveScroll: true });
        }
    } catch (e) {
        await alertModal({ message: e.response?.data?.message || 'Action impossible' });
    } finally {
        isProcessingAction.value = false;
    }
};

// ── Lifecycle ───────────────────────────────────────────────────────────────
onMounted(() => {
    setTimeout(initMap, 100);
    startTimer();

    // GSAP Intro
    gsap.from(".hud-top", { y: -100, opacity: 0, duration: 1, ease: "power4.out" });
    gsap.from(".hud-bottom", { y: 100, opacity: 0, duration: 1, ease: "power4.out", delay: 0.5 });
});

onUnmounted(() => {
    if (watchId) navigator.geolocation.clearWatch(watchId);
    if (map) map.remove();
    stopTimer();
});

watch(() => gameState.value, drawMarkers, { deep: true });
</script>

<template>
    <Head title="CityPlay - Mission Tactique" />

    <PlayerLayout>
        <div class="relative min-h-[calc(100vh-80px)] overflow-hidden bg-[#070a13]">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_left_top,rgba(255,179,0,0.14),transparent_32%),radial-gradient(circle_at_right_bottom,rgba(255,95,0,0.16),transparent_32%)]"></div>

            <div class="absolute inset-x-0 top-4 z-20 px-4 hud-top">
                <div class="flex flex-col gap-3 rounded-[32px] border border-white/10 bg-slate-950/90 p-4 shadow-2xl backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.28em] text-slate-400">Mission en cours</p>
                        <h1 class="text-xl font-black uppercase tracking-[0.18em] text-white">Carte tactique</h1>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] uppercase tracking-[0.24em] text-slate-500">Ville</p>
                        <p class="truncate text-sm font-black uppercase tracking-[0.1em] text-orange-300">{{ gameState?.city?.name || 'Ville inconnue' }}</p>
                    </div>
                </div>
            </div>

            <div ref="mapContainer" class="absolute inset-0 z-0"></div>

            <div class="absolute inset-x-4 top-24 z-20">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-3xl border border-white/10 bg-slate-950/85 p-4 shadow-xl backdrop-blur-xl">
                        <div class="text-[10px] uppercase tracking-[0.28em] text-slate-400 mb-2">Chrono</div>
                        <div class="text-3xl font-black tracking-tight" :class="timerColorClass">{{ formatTime(remainingTimeSeconds) }}</div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-slate-950/85 p-4 shadow-xl backdrop-blur-xl">
                        <div class="text-[10px] uppercase tracking-[0.28em] text-slate-400 mb-2">Score</div>
                        <div class="text-3xl font-black tracking-tight text-white">{{ gameState?.total_score || 0 }} <span class="text-xs text-orange-300">PTS</span></div>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-slate-950/85 p-4 shadow-xl backdrop-blur-xl">
                        <div class="text-[10px] uppercase tracking-[0.28em] text-slate-400 mb-2">Progression</div>
                        <div class="text-3xl font-black tracking-tight text-white">{{ gameState?.solved_places || 0 }}/{{ gameState?.total_places || 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="hud-bottom absolute inset-x-4 bottom-4 z-30 pointer-events-none">
                <div class="mx-auto max-w-3xl rounded-[36px] border border-white/10 bg-slate-950/95 p-5 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.72)] backdrop-blur-xl pointer-events-auto">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.22em] text-[#e7a06f] mb-2">Objectif actuel</p>
                            <h2 class="text-xl font-black text-white leading-tight truncate">{{ gameState?.current_riddle?.place?.name || 'Lieu mystère' }}</h2>
                            <p class="mt-2 text-sm text-slate-300 line-clamp-2">{{ gameState?.current_riddle?.question || 'Attendez la prochaine énigme.' }}</p>
                        </div>

                        <div class="grid gap-3 sm:w-56">
                            <Link
                                v-if="gameState?.current_riddle"
                                :href="route('player.riddle.show', gameState.current_riddle.id)"
                                class="inline-flex items-center justify-center rounded-3xl bg-gradient-to-r from-[#E0531C] to-[#FFB700] px-5 py-3 text-sm font-black uppercase tracking-[0.18em] text-white shadow-[0_18px_48px_rgba(224,83,28,0.24)] transition-all duration-300 hover:-translate-y-0.5 active:scale-95"
                            >
                                Résoudre
                            </Link>
                            <button
                                v-if="gameState?.is_host && gameState.status === 'active'"
                                @click="handleAction('pause')"
                                :disabled="isProcessingAction"
                                class="h-11 rounded-3xl border border-white/10 bg-white/5 text-sm font-black uppercase tracking-[0.14em] text-white transition hover:border-[#E0531C]/40 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Pause
                            </button>
                            <button
                                v-if="gameState?.is_host"
                                @click="handleAction('abandon')"
                                :disabled="isProcessingAction"
                                class="h-11 rounded-3xl bg-white text-slate-900 text-sm font-black uppercase tracking-[0.14em] transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                Abandonner
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-3xl bg-slate-900/80 p-4 border border-white/10">
                            <p class="text-[10px] uppercase tracking-[0.22em] text-slate-400 mb-2">Mode</p>
                            <p class="text-sm font-black text-white uppercase">{{ gameState?.mode || 'N/A' }}</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900/80 p-4 border border-white/10">
                            <p class="text-[10px] uppercase tracking-[0.22em] text-slate-400 mb-2">Difficulté</p>
                            <p class="text-sm font-black text-white uppercase">{{ gameState?.difficulty || 'N/A' }}</p>
                        </div>
                        <div class="rounded-3xl bg-slate-900/80 p-4 border border-white/10">
                            <p class="text-[10px] uppercase tracking-[0.22em] text-slate-400 mb-2">Étapes restantes</p>
                            <p class="text-sm font-black text-white uppercase">{{ remainingPlaces }}</p>
                        </div>
                    </div>
                </div>
            </div>

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
