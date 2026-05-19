<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';

// Fix Leaflet's default icon path issues with Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

const props = defineProps({
    session: Object,
});

const mapContainer = ref(null);
let map = null;
let userMarker = null;
let watchId = null;
const userLocation = ref(null);
const geoError = ref(null);

const selectedMapPlace = ref(null);
const isSelectingPlace = ref(false);
const isProcessingAction = ref(false);

// ── Gestion du Temps ─────────────────────────────────────────────────────────
const remainingTimeSeconds = ref(0);
let timerInterval = null;

const calculateRemainingTime = () => {
    if (!props.session?.started_at || props.session.status === 'completed' || props.session.status === 'abandoned') {
        remainingTimeSeconds.value = 0;
        return;
    }

    const startedAt = new Date(props.session.started_at).getTime();
    const now = props.session.status === 'paused' 
        ? new Date(props.session.paused_at).getTime() 
        : new Date().getTime();
    
    const totalPauseMs = (props.session.total_pause_seconds || 0) * 1000;
    const elapsedMs = now - startedAt - totalPauseMs;
    const availableMs = (props.session.available_minutes || 0) * 60 * 1000;
    
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
    if (remainingTimeSeconds.value < 300) return 'text-red-500 animate-pulse'; // < 5min
    if (remainingTimeSeconds.value < 900) return 'text-amber-500'; // < 15min
    return 'text-[#d65a31]';
});

// ── Actions de Session ───────────────────────────────────────────────────────
const togglePause = async () => {
    if (isProcessingAction.value) return;
    isProcessingAction.value = true;
    
    const action = props.session.status === 'paused' ? 'resume' : 'pause';
    try {
        await axios.post(route(`player.game-sessions.${action}`, props.session.id));
        router.reload({ only: ['session'] });
    } catch (e) {
        console.error(`Erreur lors de ${action}:`, e);
    } finally {
        isProcessingAction.value = false;
    }
};

const abandonGame = async () => {
    if (!confirm("Êtes-vous sûr de vouloir abandonner l'aventure ? Votre progression sera enregistrée mais la partie sera terminée.")) return;
    
    if (isProcessingAction.value) return;
    isProcessingAction.value = true;

    try {
        await axios.post(route('player.game-sessions.abandon', props.session.id));
        router.visit(route('player.dashboard'));
    } catch (e) {
        console.error("Erreur lors de l'abandon:", e);
    } finally {
        isProcessingAction.value = false;
    }
};

const places = computed(() => {
    if (!props.session?.session_places) return [];
    
    // 1. On ne montre que les lieux déjà complétés ou le lieu actuel
    return props.session.session_places
        .filter(sp => sp.place && (sp.is_completed || sp.order_index === props.session.current_place_index))
        .map(sp => ({
            ...sp.place,
            is_completed: sp.is_completed,
            order_index: sp.order_index
        }));
});

const currentPlace = computed(() => {
    return places.value.find(p => p.order_index === props.session.current_place_index);
});

let placeMarkers = [];

const drawPlaces = () => {
    // Supprimer les anciens marqueurs s'ils existent
    placeMarkers.forEach(marker => {
        if (map) {
            map.removeLayer(marker);
        }
    });
    placeMarkers = [];

    // Ajouter les lieux de la session
    places.value.forEach(place => {
        const isCurrent = place.order_index === props.session.current_place_index;
        // Place utilise lat/lng
        const placeLat = place.lat ?? place.latitude;
        const placeLng = place.lng ?? place.longitude;

        if (!placeLat || !placeLng) return; // Ignorer les lieux sans coordonnées
        
        // Custom Icon based on status
        const icon = L.divIcon({
            className: 'custom-div-icon',
            html: `
                <div class="relative cursor-pointer">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center shadow-2xl transition-all duration-300 border-2 font-black text-xs ${
                        place.is_completed 
                            ? 'bg-green-500 border-green-200 text-white' 
                            : (isCurrent 
                                ? 'bg-[#d65a31] border-orange-200 text-white scale-110' 
                                : 'bg-gray-800 border-gray-600 text-gray-400 opacity-60')
                    }">
                        ${place.is_completed ? '✓' : (isCurrent ? '?' : place.order_index + 1)}
                    </div>
                    ${isCurrent ? '<div class="absolute -top-1 -right-1 w-3 h-3 bg-white rounded-full animate-ping"></div>' : ''}
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        const marker = L.marker([placeLat, placeLng], { icon }).addTo(map);
        
        // Au clic sur le marqueur, on définit le lieu sélectionné
        marker.on('click', () => {
            selectedMapPlace.value = place;
        });

        placeMarkers.push(marker);
    });
};

const selectThisPlace = async (place) => {
    if (isSelectingPlace.value) return;
    isSelectingPlace.value = true;
    try {
        const response = await axios.post(route('player.game-sessions.select-place', props.session.id), {
            place_id: place.id
        });
        
        const data = response.data;
        if (data.next_riddle_id) {
            // Lance immédiatement la succession d'énigmes du lieu sélectionné !
            router.visit(route('player.riddle.show', data.next_riddle_id));
        } else {
            // Recharger les props de la session de manière transparente
            router.reload({
                only: ['session'],
                onSuccess: () => {
                    const updated = places.value.find(p => p.id === place.id);
                    if (updated) {
                        selectedMapPlace.value = updated;
                    }
                }
            });
        }
    } catch (e) {
        console.error("Erreur sélection lieu:", e);
        alert(e.response?.data?.message || "Impossible de sélectionner ce lieu.");
    } finally {
        isSelectingPlace.value = false;
    }
};

// Réagir dynamiquement aux modifications des lieux ou de l'index actuel
watch(places, () => {
    if (map) {
        drawPlaces();
    }
}, { deep: true });

const initMap = () => {
    if (!mapContainer.value) return;

    // City utilise lat/lng (pas latitude/longitude)
    const centerLat = props.session.city?.lat ?? props.session.city?.latitude ?? 6.3654;
    const centerLng = props.session.city?.lng ?? props.session.city?.longitude ?? 2.4183;

    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false
    }).setView([centerLat, centerLng], 14);

    // Style de carte sombre natif (plus stable et lisible)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
    }).addTo(map);

    // Force la recalculation de la taille de la carte pour éviter le bug de d'affichage gris en SPA
    setTimeout(() => {
        if (map) {
            map.invalidateSize();
        }
    }, 250);

    // Retirer le filtre CSS qui rendait les labels illisibles
    const mapEl = mapContainer.value;
    mapEl.style.filter = 'none';

    // Dessiner les lieux de la session
    drawPlaces();

    startWatchingLocation();
};

const recenterMap = () => {
    if (userLocation.value && map) {
        map.setView([userLocation.value.lat, userLocation.value.lng], 16, { animate: true });
    } else if (map && props.session.city) {
        const cityLat = props.session.city.lat ?? props.session.city.latitude;
        const cityLng = props.session.city.lng ?? props.session.city.longitude;
        if (cityLat && cityLng) {
            map.setView([cityLat, cityLng], 14, { animate: true });
        }
    }
};

const zoomIn = () => map?.zoomIn();
const zoomOut = () => map?.zoomOut();

const startWatchingLocation = () => {
    if ("geolocation" in navigator) {
        watchId = navigator.geolocation.watchPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                userLocation.value = { lat: latitude, lng: longitude };

                if (!userMarker) {
                    const userIcon = L.divIcon({
                        className: 'user-icon',
                        html: `<div class="relative">
                            <div class="absolute -inset-2 bg-blue-500/30 rounded-full animate-ping"></div>
                            <div class="relative bg-blue-500 w-4 h-4 rounded-full border-2 border-white shadow-lg"></div>
                        </div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    });
                    userMarker = L.marker([latitude, longitude], { icon: userIcon }).addTo(map);
                    
                    // Centrer sur le joueur au premier fix
                    map.setView([latitude, longitude], 16);
                } else {
                    userMarker.setLatLng([latitude, longitude]);
                }
            },
            (error) => {
                console.error("Erreur GPS:", error);
                geoError.value = "Activez le GPS pour voir votre position.";
            },
            { enableHighAccuracy: true }
        );
    }
};

onMounted(() => {
    // Petit délai pour s'assurer que Leaflet est bien chargé et que le DOM est prêt
    setTimeout(initMap, 100);

    // Initialiser et démarrer le timer
    calculateRemainingTime();
    timerInterval = setInterval(calculateRemainingTime, 1000);
});

onUnmounted(() => {
    if (watchId) navigator.geolocation.clearWatch(watchId);
    if (map) map.remove();
    if (timerInterval) clearInterval(timerInterval);
});
</script>

<template>
    <Head title="Carte du Jeu" />

    <PlayerLayout>
        <div class="h-[calc(100vh-160px)] -mt-6 -mx-4 relative overflow-hidden">
            <!-- Leaflet Container -->
            <div ref="mapContainer" class="w-full h-full z-0"></div>

            <!-- UI Overlay: Header -->
            <div class="absolute top-6 left-4 right-4 z-10">
                <div class="bg-[#1c2128]/80 backdrop-blur-xl border border-white/10 rounded-2xl p-4 shadow-2xl flex items-center justify-between">
                    <div class="flex-1 min-w-0 mr-2">
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                {{ session.status === 'completed' ? 'Mission Terminée' : 'Mission Actuelle' }}
                            </h1>
                            <div v-if="session.status === 'paused'" class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-500 text-[8px] font-black uppercase tracking-widest border border-amber-500/30">
                                En Pause
                            </div>
                        </div>
                        <p class="text-white font-bold italic truncate text-sm">
                            {{ session.status === 'completed' ? 'Parcours terminé avec succès !' : (currentPlace?.name || 'En route...') }}
                        </p>
                    </div>
                    <div class="flex items-center shrink-0 gap-3">
                        <!-- Timer -->
                        <div v-if="session.status !== 'completed'" class="flex flex-col items-end">
                            <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest">Temps</span>
                            <span :class="timerColorClass" class="text-sm font-black tabular-nums">
                                {{ formatTime(remainingTimeSeconds) }}
                            </span>
                        </div>

                        <!-- Bouton Résoudre l'énigme active -->
                        <Link v-if="session.status === 'active' && session.current_riddle"
                              :href="route('player.riddle.show', session.current_riddle.id)"
                              class="bg-[#d65a31] hover:bg-[#b84a26] text-white text-[10px] font-black uppercase tracking-widest px-3.5 py-2.5 rounded-xl shadow-lg flex items-center gap-1.5 transition-all">
                            <span>Jouer</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- UI Overlay: Controls -->
            <div class="absolute right-4 top-32 z-10 flex flex-col space-y-2">
                <button @click="zoomIn" class="w-12 h-12 bg-[#1c2128]/90 backdrop-blur border border-white/10 rounded-xl flex items-center justify-center text-white hover:bg-[#252b35] transition shadow-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <button @click="zoomOut" class="w-12 h-12 bg-[#1c2128]/90 backdrop-blur border border-white/10 rounded-xl flex items-center justify-center text-white hover:bg-[#252b35] transition shadow-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </button>
                <button @click="recenterMap" class="w-12 h-12 bg-[#d65a31] border border-[#d65a31]/20 rounded-xl flex items-center justify-center text-white hover:bg-[#b84a26] transition shadow-xl mt-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </button>

                <!-- Actions de Session -->
                <button @click="togglePause" :disabled="isProcessingAction" class="w-12 h-12 bg-white/5 backdrop-blur border border-white/10 rounded-xl flex items-center justify-center text-white hover:bg-white/10 transition shadow-xl mt-4">
                    <svg v-if="session.status === 'paused'" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                </button>
                <button @click="abandonGame" :disabled="isProcessingAction" class="w-12 h-12 bg-red-500/10 backdrop-blur border border-red-500/20 rounded-xl flex items-center justify-center text-red-500 hover:bg-red-500/20 transition shadow-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- UI Overlay: Footer Legend -->
            <div class="absolute bottom-6 left-4 right-4 z-10 flex space-x-2 overflow-x-auto pb-2 no-scrollbar">
                <div class="flex-none bg-[#1c2128]/90 backdrop-blur px-4 py-2 rounded-full border border-white/5 flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                    <span class="text-[10px] font-black text-white uppercase tracking-widest">Vous</span>
                </div>
                <div class="flex-none bg-[#1c2128]/90 backdrop-blur px-4 py-2 rounded-full border border-white/5 flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-[#d65a31]"></div>
                    <span class="text-[10px] font-black text-white uppercase tracking-widest">Objectif</span>
                </div>
                <div class="flex-none bg-[#1c2128]/90 backdrop-blur px-4 py-2 rounded-full border border-white/5 flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full bg-[#10b981]"></div>
                    <span class="text-[10px] font-black text-white uppercase tracking-widest">Validé</span>
                </div>
            </div>

            <!-- Floating Place Details Card -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-10 scale-95"
                leave-active-class="transition-all duration-200 ease-in"
                leave-to-class="opacity-0 translate-y-10 scale-95"
            >
                <div v-if="selectedMapPlace" class="absolute bottom-20 left-4 right-4 z-10 max-w-sm mx-auto">
                    <div class="bg-[#1c2128]/95 backdrop-blur-xl border border-white/10 rounded-2xl p-4 shadow-2xl relative overflow-hidden">
                        
                        <!-- Close button -->
                        <button @click="selectedMapPlace = null" class="absolute top-3 right-3 text-gray-400 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <div class="mb-3.5">
                            <span class="text-[9px] font-black uppercase tracking-widest text-[#d65a31] block mb-1">
                                {{ selectedMapPlace.is_completed ? 'Lieu Découvert' : 'Lieu À Explorer' }}
                            </span>
                            <h3 class="text-base font-black text-white leading-tight pr-6">{{ selectedMapPlace.is_completed ? selectedMapPlace.name : 'Lieu Mystère' }}</h3>
                        </div>

                        <p v-if="selectedMapPlace.is_completed && selectedMapPlace.description" class="text-[11px] text-gray-300 font-medium leading-relaxed mb-4 line-clamp-3">
                            {{ selectedMapPlace.description }}
                        </p>
                        <p v-else-if="!selectedMapPlace.is_completed" class="text-[11px] text-[#d65a31] italic font-bold mb-4">
                            Lieu mystère. Résolvez l'énigme et rendez-vous sur place pour découvrir son histoire.
                        </p>

                        <!-- Place Stats Badges -->
                        <div class="flex items-center gap-2 flex-wrap mb-4">
                            <span v-if="selectedMapPlace.estimated_time_min" class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black bg-[#d65a31]/10 text-[#d65a31] border border-[#d65a31]/15">
                                ⏱ {{ selectedMapPlace.estimated_time_min }} min
                            </span>
                            <span v-if="selectedMapPlace.validation_radius" class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black bg-blue-500/10 text-blue-500 border border-blue-500/15">
                                📍 Rayon : {{ selectedMapPlace.validation_radius }}m
                            </span>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2.5">
                            <div v-if="selectedMapPlace.is_completed" class="w-full h-11 rounded-xl bg-green-500/10 border border-green-500/20 text-green-500 text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-2">
                                <span>✓ Découvert avec succès</span>
                            </div>
                            
                            <template v-else>
                                <!-- Si c'est l'objectif actuel, on affiche Jouer -->
                                <Link v-if="selectedMapPlace.order_index === session.current_place_index && session.current_riddle"
                                      :href="route('player.riddle.show', session.current_riddle.id)"
                                      class="w-full h-11 bg-[#d65a31] hover:bg-[#b84a26] text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg flex items-center justify-center gap-1.5 transition-all">
                                    <span>Résoudre les énigmes</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </Link>
                                
                                <!-- Si ce n'est pas l'objectif actuel, on affiche Sélectionner -->
                                <button v-else
                                        @click="selectThisPlace(selectedMapPlace)"
                                        :disabled="isSelectingPlace"
                                        class="w-full h-11 bg-white hover:bg-gray-100 text-gray-900 text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg flex items-center justify-center gap-1.5 transition-all disabled:opacity-50">
                                    <span v-if="!isSelectingPlace">Sélectionner comme objectif</span>
                                    <svg v-else class="animate-spin w-4 h-4 text-gray-900" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- Geolocation Error -->
            <div v-if="geoError" class="absolute top-24 left-4 right-4 z-10 bg-red-500/90 backdrop-blur text-white p-3 rounded-xl text-center text-[10px] font-black uppercase tracking-widest">
                {{ geoError }}
            </div>
        </div>
    </PlayerLayout>
</template>

<style>
.leaflet-container {
    background: #0f111a !important;
}
.custom-div-icon {
    background: transparent !important;
    border: none !important;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
