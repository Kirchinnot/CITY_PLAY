<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    riddle:          Object,
    session:         Object,
    unlockedHintIds: Array,
});

// ── GPS ───────────────────────────────────────────────────────────────────────
const userLocation      = ref(null);
const distanceToTarget  = ref(null);
const watchId           = ref(null);
const geoError          = ref(null);
const currentLat        = ref(null);
const currentLng        = ref(null);

// ── Jeu ───────────────────────────────────────────────────────────────────────
const isValidating      = ref(false);
const isUnlockingHint   = ref(false);
const showResult        = ref(false);
const showHints         = ref(false);
const resultData        = ref(null);
const answerError       = ref(null);
const answerInput       = ref('');
const localUnlockedHintIds = ref([...props.unlockedHintIds]);
const unlockedHintsCount   = computed(() => localUnlockedHintIds.value.length);

// ── PAUSE ─────────────────────────────────────────────────────────────────────
const isPaused          = ref(props.session?.status === 'paused');
const showPauseMenu     = ref(false);
const isPausingOrResuming = ref(false);
const isAbandoning      = ref(false);
const showAbandonConfirm = ref(false);

// Chrono de pause (affiche combien de temps on est en pause)
const pauseElapsed      = ref(0);
let pauseTimer          = null;

const startPauseTimer = () => {
    pauseTimer = setInterval(() => { pauseElapsed.value++; }, 1000);
};
const stopPauseTimer = () => {
    clearInterval(pauseTimer);
    pauseTimer = null;
    pauseElapsed.value = 0;
};

const formatPauseTime = computed(() => {
    const m = Math.floor(pauseElapsed.value / 60).toString().padStart(2, '0');
    const s = (pauseElapsed.value % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
});

// ── Actions pause/reprise/abandon ─────────────────────────────────────────────
const pauseSession = async () => {
    if (isPausingOrResuming.value) return;
    isPausingOrResuming.value = true;
    try {
        await axios.post(route('player.game-sessions.pause', props.session.id));
        isPaused.value = true;
        showPauseMenu.value = false;
        startPauseTimer();
        // Arrêter le GPS pendant la pause
        if (watchId.value) {
            navigator.geolocation.clearWatch(watchId.value);
            watchId.value = null;
        }
    } catch (e) {
        console.error('Erreur pause:', e);
    } finally {
        isPausingOrResuming.value = false;
    }
};

const resumeSession = async () => {
    if (isPausingOrResuming.value) return;
    isPausingOrResuming.value = true;
    try {
        await axios.post(route('player.game-sessions.resume', props.session.id));
        isPaused.value = false;
        stopPauseTimer();
        // Relancer le GPS
        startWatchingLocation();
    } catch (e) {
        console.error('Erreur reprise:', e);
    } finally {
        isPausingOrResuming.value = false;
    }
};

const abandonSession = async () => {
    if (isAbandoning.value) return;
    isAbandoning.value = true;
    try {
        await axios.post(route('player.game-sessions.abandon', props.session.id));
        router.visit(route('player.dashboard'));
    } catch (e) {
        console.error('Erreur abandon:', e);
        isAbandoning.value = false;
    }
};

// ── GPS ───────────────────────────────────────────────────────────────────────
const startWatchingLocation = () => {
    if (!('geolocation' in navigator)) {
        geoError.value = "Votre navigateur ne supporte pas la géolocalisation.";
        return;
    }
    geoError.value = null;
    watchId.value = navigator.geolocation.watchPosition(
        (pos) => {
            userLocation.value = { lat: pos.coords.latitude, lng: pos.coords.longitude };
            currentLat.value = pos.coords.latitude;
            currentLng.value = pos.coords.longitude;
            calculateDistanceToTarget();
        },
        (err) => {
            geoError.value = err.code === 1
                ? "Autorisez la géolocalisation pour jouer."
                : "Impossible de récupérer votre position.";
        },
        { enableHighAccuracy: true }
    );
};

const calculateDistanceToTarget = () => {
    if (!userLocation.value || !props.riddle.place) return;
    const R = 6371e3;
    const φ1 = userLocation.value.lat * Math.PI / 180;
    const placeLat = props.riddle.place.lat ?? props.riddle.place.latitude;
    const placeLng = props.riddle.place.lng ?? props.riddle.place.longitude;
    const φ2 = placeLat * Math.PI / 180;
    const Δφ = (placeLat - userLocation.value.lat) * Math.PI / 180;
    const Δλ = (placeLng - userLocation.value.lng) * Math.PI / 180;
    const a = Math.sin(Δφ/2)**2 + Math.cos(φ1)*Math.cos(φ2)*Math.sin(Δλ/2)**2;
    distanceToTarget.value = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
};

// ── Validation ────────────────────────────────────────────────────────────────
const selectOption = (option) => { answerInput.value = option; submitValidation(); };

const submitValidation = async () => {
    if (isPaused.value) return;
    isValidating.value = true;
    answerError.value = null;
    try {
        const { data } = await axios.post(route('player.riddle.validate', props.riddle.id), {
            session_id: props.session.id,
            latitude:   currentLat.value,
            longitude:  currentLng.value,
            answer:     answerInput.value,
        });
        resultData.value = data;
        showResult.value = true;
    } catch (err) {
        const msg = err.response?.data?.message || 'Une erreur est survenue.';
        if (err.response?.status === 422) answerError.value = msg;
        else alert(msg);
    } finally {
        isValidating.value = false;
    }
};

// ── Indices ───────────────────────────────────────────────────────────────────
const unlockNextHint = async () => {
    const idx = localUnlockedHintIds.value.length;
    if (idx >= props.riddle.hints.length) return;
    const nextHint = props.riddle.hints[idx];
    isUnlockingHint.value = true;
    try {
        await axios.post(route('player.riddle.unlock-hint', props.riddle.id), {
            session_id: props.session.id,
            hint_id:    nextHint.id,
        });
        localUnlockedHintIds.value.push(nextHint.id);
    } catch (e) {
        console.error("Erreur déblocage indice:", e);
    } finally {
        isUnlockingHint.value = false;
    }
};

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
    if (!isPaused.value) startWatchingLocation();
    else startPauseTimer();
});

onUnmounted(() => {
    if (watchId.value) navigator.geolocation.clearWatch(watchId.value);
    stopPauseTimer();
});
</script>

<template>
    <Head :title="`Énigme : ${riddle.title}`" />
    <PlayerLayout>
        <div class="max-w-md mx-auto space-y-5 pb-20">

            <!-- ── HEADER : titre + difficulté + bouton pause ── -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black cp-text-primary italic tracking-tighter uppercase">Énigme en cours</h1>
                    <p class="text-[10px] cp-text-muted font-bold uppercase tracking-widest mt-0.5">
                        Lieu {{ session.solved_places + 1 }} / {{ session.total_places }}
                        <span class="text-[#d65a31] ml-1">— {{ riddle.place?.name }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="bg-[#d65a31] text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-widest">
                        Force {{ riddle.difficulty === 'force_3' ? '3' : riddle.difficulty === 'force_2' ? '2' : '1' }}
                    </span>
                    <!-- Bouton pause / menu -->
                    <button @click="showPauseMenu = true"
                            class="pause-btn"
                            :class="isPaused ? 'pause-btn-active' : ''"
                            title="Pause">
                        <svg v-if="!isPaused" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ── BANNIÈRE PAUSE ── -->
            <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 -translate-y-2" leave-active-class="transition-all duration-200" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="isPaused" class="pause-banner rounded-2xl p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/15 border border-amber-500/25 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-amber-400 uppercase tracking-widest">Jeu en pause</p>
                            <p class="text-[11px] text-amber-400/60 font-bold">{{ formatPauseTime }} écoulé</p>
                        </div>
                    </div>
                    <button @click="resumeSession" :disabled="isPausingOrResuming" class="resume-btn">
                        <svg v-if="!isPausingOrResuming" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span>Reprendre</span>
                    </button>
                </div>
            </Transition>

            <!-- ── ERREUR GPS ── -->
            <div v-if="geoError && !isPaused" class="rounded-2xl p-4 text-center" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);">
                <p class="text-red-400 text-xs font-black uppercase tracking-widest mb-3">{{ geoError }}</p>
                <button @click="startWatchingLocation" class="text-[10px] font-black text-[#d65a31] underline uppercase tracking-widest">Réessayer</button>
            </div>

            <!-- ── CARTE ÉNIGME ── -->
            <div class="riddle-card rounded-3xl p-6 relative overflow-hidden" :class="{ 'opacity-60 pointer-events-none': isPaused }">
                <div class="absolute left-0 top-6 bottom-6 w-1 bg-[#d65a31] rounded-r-full"></div>
                <div class="pl-4 space-y-4">
                    <p class="cp-text-primary text-xl font-bold leading-relaxed italic">"{{ riddle.question }}"</p>
                    <div class="rounded-xl p-4 flex items-center space-x-3" style="background: rgba(214,90,49,0.06); border: 1px solid rgba(214,90,49,0.15);">
                        <div class="bg-[#d65a31]/15 p-2 rounded-lg shrink-0">
                            <svg class="w-4 h-4 text-[#d65a31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <p class="text-[10px] font-bold cp-text-muted uppercase tracking-widest leading-tight">
                            Rends-toi physiquement sur le lieu pour valider
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── ZONE D'ACTION ── -->
            <div class="space-y-4" :class="{ 'opacity-50 pointer-events-none select-none': isPaused }">
                <!-- QCM -->
                <div v-if="riddle.options && riddle.options.length > 0" class="grid grid-cols-1 gap-3">
                    <button v-for="(option, index) in riddle.options" :key="index"
                            @click="selectOption(option)"
                            :disabled="distanceToTarget === null || distanceToTarget > (riddle.place?.validation_radius || 30) || isValidating"
                            class="qcm-btn group">
                        <span>{{ option }}</span>
                        <div class="w-6 h-6 rounded-full border-2 border-current/20 group-hover:border-[#d65a31] flex items-center justify-center transition">
                            <div class="w-2 h-2 rounded-full bg-[#d65a31] scale-0 group-hover:scale-100 transition"></div>
                        </div>
                    </button>
                </div>

                <!-- Texte libre -->
                <div v-else-if="riddle.answer" class="space-y-2">
                    <input type="text" v-model="answerInput"
                           placeholder="Tapez votre réponse ici..."
                           class="answer-input" />
                    <InputError :message="answerError" />
                </div>

                <!-- Bouton valider + indice (texte libre) -->
                <div v-if="!riddle.options || riddle.options.length === 0" class="flex items-center space-x-3">
                    <button @click="submitValidation"
                            :disabled="distanceToTarget === null || distanceToTarget > (riddle.place?.validation_radius || 30) || isValidating"
                            class="flex-1 h-16 rounded-2xl flex items-center justify-center space-x-3 transition-all font-black uppercase tracking-widest active:scale-95"
                            :class="distanceToTarget !== null && distanceToTarget <= (riddle.place?.validation_radius || 30)
                                ? 'validate-btn-active'
                                : 'validate-btn-disabled'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span>{{ isValidating ? 'Vérification...' : 'Je suis sur place' }}</span>
                    </button>
                    <button @click="showHints = true" class="hint-btn relative">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <span v-if="unlockedHintsCount < riddle.hints?.length"
                              class="absolute -top-1 -right-1 w-5 h-5 bg-[#d65a31] text-white text-[10px] font-black flex items-center justify-center rounded-full border-2"
                              style="border-color: var(--bg-base);">
                            {{ riddle.hints.length - unlockedHintsCount }}
                        </span>
                    </button>
                </div>

                <!-- Indice seul (QCM) -->
                <div v-else class="flex justify-end">
                    <button @click="showHints = true" class="hint-btn relative">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <span v-if="unlockedHintsCount < riddle.hints?.length"
                              class="absolute -top-1 -right-1 w-5 h-5 bg-[#d65a31] text-white text-[10px] font-black flex items-center justify-center rounded-full border-2"
                              style="border-color: var(--bg-base);">
                            {{ riddle.hints.length - unlockedHintsCount }}
                        </span>
                    </button>
                </div>

                <!-- Distance -->
                <div v-if="distanceToTarget !== null" class="text-center">
                    <span v-if="distanceToTarget > (riddle.place?.validation_radius || 30)" class="text-xs font-bold cp-text-muted">
                        📍 {{ Math.round(distanceToTarget) }}m du lieu cible
                    </span>
                    <span v-else class="text-xs font-bold text-green-500">✓ Vous êtes sur place !</span>
                </div>
                <div v-else-if="!geoError" class="text-center">
                    <span class="text-xs font-bold cp-text-muted">Localisation en cours...</span>
                </div>

                <!-- Footer stats -->
                <div class="flex items-center justify-between pt-2">
                    <div class="flex items-center gap-4">
                        <span class="text-green-500 font-black text-xs">{{ session.total_score || 0 }} pts</span>
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3 cp-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="cp-text-muted font-black text-xs">{{ session.available_minutes }}min</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-black cp-text-muted uppercase tracking-widest">{{ session.mode }}</span>
                </div>
            </div>
        </div>

        <!-- ══ MENU PAUSE (overlay) ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" leave-active-class="transition-all duration-150" leave-to-class="opacity-0">
                <div v-if="showPauseMenu" class="fixed inset-0 z-[80] flex items-end justify-center p-4"
                     style="background: rgba(13,17,23,0.7); backdrop-filter: blur(8px);"
                     @click.self="showPauseMenu = false">
                    <div class="pause-menu w-full max-w-sm rounded-[28px] p-6 space-y-3">
                        <div class="text-center mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/25 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-black cp-text-primary">Options de jeu</h3>
                            <p class="text-xs cp-text-muted font-medium mt-0.5">{{ riddle.place?.name }}</p>
                        </div>

                        <!-- Mettre en pause -->
                        <button v-if="!isPaused" @click="pauseSession" :disabled="isPausingOrResuming"
                                class="menu-action-btn menu-pause">
                            <div class="menu-icon bg-amber-500/15 border-amber-500/25">
                                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-black cp-text-primary">Mettre en pause</p>
                                <p class="text-[11px] cp-text-muted">Le chrono s'arrête</p>
                            </div>
                            <svg v-if="isPausingOrResuming" class="w-4 h-4 animate-spin cp-text-muted ml-auto" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </button>

                        <!-- Reprendre (si déjà en pause) -->
                        <button v-if="isPaused" @click="resumeSession; showPauseMenu = false" :disabled="isPausingOrResuming"
                                class="menu-action-btn menu-resume">
                            <div class="menu-icon bg-green-500/15 border-green-500/25">
                                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-black cp-text-primary">Reprendre</p>
                                <p class="text-[11px] cp-text-muted">Continuer l'aventure</p>
                            </div>
                        </button>

                        <!-- Abandonner -->
                        <button @click="showAbandonConfirm = true; showPauseMenu = false"
                                class="menu-action-btn menu-abandon">
                            <div class="menu-icon bg-red-500/10 border-red-500/20">
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-sm font-black text-red-400">Abandonner la partie</p>
                                <p class="text-[11px] cp-text-muted">Progression perdue</p>
                            </div>
                        </button>

                        <!-- Annuler -->
                        <button @click="showPauseMenu = false" class="menu-cancel-btn">
                            Annuler
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- ══ CONFIRMATION ABANDON ══ -->
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-95" leave-active-class="transition-all duration-150" leave-to-class="opacity-0 scale-95">
                <div v-if="showAbandonConfirm" class="fixed inset-0 z-[90] flex items-center justify-center p-6"
                     style="background: rgba(13,17,23,0.85); backdrop-filter: blur(12px);">
                    <div class="pause-menu w-full max-w-xs rounded-[28px] p-6 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-black cp-text-primary mb-1">Abandonner ?</h3>
                        <p class="text-xs cp-text-muted font-medium mb-6 leading-relaxed">
                            Ta progression sera perdue.<br>Cette action est irréversible.
                        </p>
                        <div class="flex gap-3">
                            <button @click="showAbandonConfirm = false" class="flex-1 h-11 rounded-2xl text-xs font-black uppercase tracking-widest cp-text-secondary transition"
                                    style="background: var(--input-bg); border: 1px solid var(--border-subtle);">
                                Annuler
                            </button>
                            <button @click="abandonSession" :disabled="isAbandoning"
                                    class="flex-1 h-11 rounded-2xl text-xs font-black uppercase tracking-widest text-white flex items-center justify-center gap-2 transition"
                                    style="background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 6px 20px rgba(239,68,68,0.3);">
                                <svg v-if="isAbandoning" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span>{{ isAbandoning ? '...' : 'Abandonner' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- ══ MODAL RÉSULTAT ══ -->
        <div v-if="showResult" class="fixed inset-0 z-[60] flex items-center justify-center p-6"
             style="background: rgba(13,17,23,0.95); backdrop-filter: blur(16px);">
            <div class="max-w-xs w-full text-center animate-bounce-in">
                <div class="w-24 h-24 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-8 shadow-[0_0_50px_rgba(34,197,94,0.4)]">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-4xl font-black text-white italic tracking-tighter uppercase mb-2">Gagné !</h2>
                <p class="text-[#d65a31] font-black uppercase tracking-widest text-xs mb-10">Lieu découvert avec succès</p>
                <div class="grid grid-cols-2 gap-4 mb-10">
                    <div class="rounded-2xl p-4" style="background: var(--bg-card); border: 1px solid var(--border-card);">
                        <span class="block text-[10px] font-black cp-text-muted uppercase tracking-widest mb-1">Points</span>
                        <span class="text-2xl font-black cp-text-primary">+{{ resultData?.score }}</span>
                    </div>
                    <div class="rounded-2xl p-4" style="background: var(--bg-card); border: 1px solid var(--border-card);">
                        <span class="block text-[10px] font-black cp-text-muted uppercase tracking-widest mb-1">Temps</span>
                        <span class="text-2xl font-black cp-text-primary">{{ Math.floor(resultData?.details?.time_taken / 60) }}m {{ resultData?.details?.time_taken % 60 }}s</span>
                    </div>
                </div>
                <button @click="resultData?.is_finished ? router.visit(route('player.game-sessions.summary', resultData.session_id)) : router.visit(route('player.dashboard'))"
                        class="w-full h-14 rounded-2xl font-black uppercase tracking-widest text-white transition"
                        style="background: linear-gradient(135deg, #d65a31, #b84a24); box-shadow: 0 8px 28px rgba(214,90,49,0.4);">
                    {{ resultData?.is_finished ? 'Voir le bilan' : 'Continuer' }}
                </button>
            </div>
        </div>

        <!-- ══ MODAL INDICES ══ -->
        <div v-if="showHints" class="fixed inset-0 z-[70] flex items-center justify-center p-6"
             style="background: rgba(13,17,23,0.95); backdrop-filter: blur(16px);">
            <div class="w-full max-w-md rounded-3xl p-8 shadow-2xl" style="background: var(--bg-card); border: 1px solid var(--border-card);">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-black cp-text-primary italic tracking-tighter uppercase">Indices</h2>
                    <button @click="showHints = false" class="cp-text-muted hover:cp-text-primary transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div v-for="(hint, index) in riddle.hints" :key="hint.id"
                         class="p-4 rounded-2xl transition-all"
                         :style="index < unlockedHintsCount
                             ? 'background: var(--bg-surface); border: 1px solid var(--border-card);'
                             : 'background: var(--input-bg); border: 1px solid var(--border-subtle); opacity: 0.5;'">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-black text-[#d65a31] uppercase tracking-widest">Indice {{ index + 1 }}</span>
                            <svg v-if="index >= unlockedHintsCount" class="w-4 h-4 cp-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <p v-if="index < unlockedHintsCount" class="cp-text-primary font-bold leading-relaxed text-sm">{{ hint.content }}</p>
                        <p v-else class="cp-text-muted text-sm font-bold italic">Débloquez cet indice pour obtenir de l'aide</p>
                    </div>
                    <button v-if="unlockedHintsCount < riddle.hints?.length"
                            @click="unlockNextHint" :disabled="isUnlockingHint"
                            class="w-full h-14 rounded-2xl font-black uppercase tracking-widest text-white flex items-center justify-center gap-2 disabled:opacity-50 transition"
                            style="background: linear-gradient(135deg, #d65a31, #b84a24); box-shadow: 0 6px 20px rgba(214,90,49,0.3);">
                        <svg v-if="!isUnlockingHint" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <svg v-else class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span>{{ isUnlockingHint ? 'Déblocage...' : `Débloquer l'indice (${(unlockedHintsCount + 1) * 30} pts)` }}</span>
                    </button>
                </div>
            </div>
        </div>

    </PlayerLayout>
</template>
