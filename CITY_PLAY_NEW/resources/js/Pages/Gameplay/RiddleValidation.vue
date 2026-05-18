<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
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
const showWrongAnswerModal = ref(false);
const revealSolutionState = ref(false);
const isSkipping        = ref(false);
const resultData        = ref(null);
const answerError       = ref(null);
const answerInput       = ref('');
const localUnlockedHintIds = ref([...props.unlockedHintIds]);
const unlockedHintsCount   = computed(() => localUnlockedHintIds.value.length);

// ── Offline-First ─────────────────────────────────────────────────────────────
const isOnline            = ref(navigator.onLine);
const showOfflineToast    = ref(false);
const offlineToastMessage = ref('');
const isSyncing           = ref(false);

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

    // Mode Offline-First
    if (!isOnline.value) {
        // Enregistre la réponse en local
        const submission = {
            riddle_id: props.riddle.id,
            session_id: props.session.id,
            latitude: currentLat.value,
            longitude: currentLng.value,
            answer: answerInput.value,
            submitted_at: new Date().toISOString()
        };

        try {
            const queue = JSON.parse(localStorage.getItem('cityplay_offline_submissions') || '[]');
            queue.push(submission);
            localStorage.setItem('cityplay_offline_submissions', JSON.stringify(queue));
        } catch (e) {
            console.error("Erreur de sauvegarde hors-ligne:", e);
        }

        isValidating.value = false;
        
        // Simulation locale si possible pour ne pas bloquer le joueur
        if (props.riddle.answer) {
            const cleanInput = answerInput.value.trim().toLowerCase();
            const cleanTarget = props.riddle.answer.trim().toLowerCase();
            if (cleanInput === cleanTarget) {
                resultData.value = {
                    score: props.riddle.points_base || 100,
                    is_finished: false,
                    is_offline_simulated: true,
                    message: "Bonne réponse ! (Enregistrée hors-ligne 💾)"
                };
                showResult.value = true;
            } else {
                answerError.value = "Réponse incorrecte (vérification locale).";
                showWrongAnswerModal.value = true;
            }
        } else {
            offlineToastMessage.value = "Connexion perdue 📶. Réponse enregistrée localement. Elle sera synchronisée dès le retour du réseau.";
            showOfflineToast.value = true;
            setTimeout(() => { showOfflineToast.value = false; }, 4000);
        }
        return;
    }

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
        if (err.response?.status === 422) {
            answerError.value = msg;
            showWrongAnswerModal.value = true;
        } else {
            alert(msg);
        }
    } finally {
        isValidating.value = false;
    }
};

const skipRiddle = () => {
    if (confirm("Voulez-vous vraiment passer cette énigme ? Vous obtiendrez 0 points sur cette étape.")) {
        isSkipping.value = true;
        router.post(route('player.riddle.skip', props.riddle.id), {}, {
            onFinish: () => {
                isSkipping.value = false;
                showWrongAnswerModal.value = false;
            }
        });
    }
};

// ── Offline-First Helpers ──────────────────────────────────────────────────────
const updateOnlineStatus = () => {
    isOnline.value = navigator.onLine;
    if (isOnline.value) {
        syncOfflineSubmissions();
    }
};

const cacheActiveRiddle = () => {
    try {
        localStorage.setItem(`cityplay_cache_riddle_${props.riddle.id}`, JSON.stringify({
            riddle: props.riddle,
            session: props.session,
            unlockedHintIds: props.unlockedHintIds
        }));
    } catch (e) {
        console.warn("Erreur mise en cache locale:", e);
    }
};

const syncOfflineSubmissions = async () => {
    if (isSyncing.value) return;
    const queue = JSON.parse(localStorage.getItem('cityplay_offline_submissions') || '[]');
    if (queue.length === 0) return;

    isSyncing.value = true;
    offlineToastMessage.value = "📶 Connexion rétablie ! Synchronisation des réponses enregistrées localement...";
    showOfflineToast.value = true;

    let successfulSyncs = 0;
    const remainingQueue = [];

    for (const sub of queue) {
        try {
            await axios.post(route('player.riddle.validate', sub.riddle_id), {
                session_id: sub.session_id,
                latitude: sub.latitude,
                longitude: sub.longitude,
                answer: sub.answer
            });
            successfulSyncs++;
        } catch (err) {
            console.error("Échec de synchronisation d'une tentative hors-ligne:", err);
            remainingQueue.push(sub);
        }
    }

    localStorage.setItem('cityplay_offline_submissions', JSON.stringify(remainingQueue));
    isSyncing.value = false;

    if (successfulSyncs > 0) {
        offlineToastMessage.value = `✅ Synchronisation réussie de ${successfulSyncs} réponse(s) ! Rechargement des scores...`;
        setTimeout(() => {
            showOfflineToast.value = false;
            router.reload({ only: ['session'] });
        }, 3000);
    } else {
        showOfflineToast.value = false;
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

    // Cacher l'énigme active pour le mode offline
    cacheActiveRiddle();

    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);

    if (isOnline.value) {
        syncOfflineSubmissions();
    }
});

onUnmounted(() => {
    if (watchId.value) navigator.geolocation.clearWatch(watchId.value);
    stopPauseTimer();

    window.removeEventListener('online', updateOnlineStatus);
    window.removeEventListener('offline', updateOnlineStatus);
});
</script>

<template>
    <Head :title="`Énigme : ${riddle.title}`" />
    <PlayerLayout>
        <div class="max-w-md mx-auto space-y-5 pb-20">

            <!-- ── FLOATING NETWORK STATUS BADGE ── -->
            <div v-if="!isOnline" class="rounded-2xl p-4 flex items-center justify-between text-amber-900 bg-amber-500/10 border border-amber-500/30 transition-all duration-300">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📶</span>
                    <div>
                        <p class="text-xs font-black uppercase tracking-widest text-amber-600">Mode Hors-Connexion</p>
                        <p class="text-[10px] font-bold text-amber-600/70">Vos réponses seront stockées en local.</p>
                    </div>
                </div>
                <span class="animate-pulse bg-amber-500/20 text-amber-600 text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-widest">
                    Hors-ligne
                </span>
            </div>

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
                <!-- Lock warning banner if player is too far -->
                <div v-if="distanceToTarget !== null && distanceToTarget > (riddle.place?.validation_radius || 30)" 
                     class="rounded-2xl p-4 flex flex-col items-center text-center space-y-3 mb-4 transition-all duration-300"
                     style="background: rgba(214, 90, 49, 0.05); border: 1px solid rgba(214, 90, 49, 0.2);">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🔒</span>
                        <span class="text-xs font-black text-[#d65a31] uppercase tracking-widest">Énigme Verrouillée</span>
                    </div>
                    <p class="text-xs font-medium cp-text-secondary leading-relaxed max-w-xs">
                        Rapprochez-vous de <b class="cp-text-primary">{{ riddle.place?.name }}</b> pour débloquer les réponses. Vous êtes à <b>{{ Math.round(distanceToTarget) }}m</b> (cible à <b>{{ riddle.place?.validation_radius || 30 }}m</b>).
                    </p>
                    <Link :href="route('player.game.map')"
                          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#d65a31]/10 hover:bg-[#d65a31]/20 border border-[#d65a31]/30 text-[10px] font-black text-[#d65a31] uppercase tracking-widest transition-all">
                        <span>🗺️ Voir sur la carte</span>
                    </Link>
                </div>

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

        <!-- ══ MODAL RÉSULTAT (BONNE RÉPONSE) ══ -->
        <div v-if="showResult" class="fixed inset-0 z-[60] flex items-center justify-center p-6"
             style="background: rgba(13,17,23,0.95); backdrop-filter: blur(16px);">
            <div class="max-w-sm w-full text-center animate-bounce-in max-h-screen overflow-y-auto pb-6 scrollbar-hide">
                <div class="w-20 h-20 bg-green-500 text-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-[0_0_40px_rgba(34,197,94,0.3)]">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-black text-white italic tracking-tighter uppercase mb-1">Gagné !</h2>
                <p class="text-[#d65a31] font-black uppercase tracking-widest text-xs mb-6">Lieu découvert avec succès</p>

                <!-- Photos du lieu (max 3-4) -->
                <div v-if="riddle.place?.images?.length" class="flex gap-2 overflow-x-auto snap-x scrollbar-hide mb-6" style="scroll-behavior: smooth;">
                    <img v-for="img in riddle.place.images.slice(0, 4)" :key="img.id"
                         :src="'/storage/' + img.path"
                         alt="Lieu découvert"
                         class="w-40 h-32 object-cover rounded-xl shadow-md snap-center shrink-0 border border-white/10" />
                </div>

                <!-- Présentation du lieu (max 500 caractères) -->
                <div v-if="riddle.place?.description" class="bg-white/5 border border-white/10 rounded-2xl p-4 mb-6 text-left">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">À propos de ce lieu</h3>
                    <p class="text-sm text-gray-200 leading-relaxed font-medium">
                        {{ riddle.place.description.length > 500 ? riddle.place.description.substring(0, 500) + '...' : riddle.place.description }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="rounded-xl p-3" style="background: var(--bg-card); border: 1px solid var(--border-card);">
                        <span class="block text-[9px] font-black cp-text-muted uppercase tracking-widest mb-1">Points</span>
                        <span class="text-xl font-black cp-text-primary">+{{ resultData?.score }}</span>
                    </div>
                    <div class="rounded-xl p-3" style="background: var(--bg-card); border: 1px solid var(--border-card);">
                        <span class="block text-[9px] font-black cp-text-muted uppercase tracking-widest mb-1">Temps</span>
                        <span class="text-xl font-black cp-text-primary">{{ Math.floor(resultData?.details?.time_taken / 60) }}m {{ resultData?.details?.time_taken % 60 }}s</span>
                    </div>
                </div>
                <button @click="resultData?.is_finished ? router.visit(route('player.game-sessions.summary', resultData.session_id)) : (resultData?.next_riddle_id ? router.visit(route('player.riddle.show', resultData.next_riddle_id)) : router.visit(route('player.dashboard')))"
                        class="w-full h-14 rounded-2xl font-black uppercase tracking-widest text-white transition"
                        style="background: linear-gradient(135deg, #d65a31, #b84a24); box-shadow: 0 8px 28px rgba(214,90,49,0.4);">
                    {{ resultData?.is_finished ? 'Voir le bilan' : 'Continuer vers l\'étape suivante' }}
                </button>
            </div>
        </div>

        <!-- ══ MODAL MAUVAISE RÉPONSE ══ -->
        <div v-if="showWrongAnswerModal" class="fixed inset-0 z-[65] flex items-center justify-center p-6"
             style="background: rgba(13,17,23,0.95); backdrop-filter: blur(16px);">
            <div class="max-w-sm w-full text-center animate-bounce-in bg-[#1c2128] border border-white/5 rounded-3xl p-6 shadow-2xl">
                
                <div v-if="!revealSolutionState">
                    <div class="w-20 h-20 bg-red-500/10 border border-red-500/20 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-black text-white italic tracking-tighter uppercase mb-2">Mauvaise réponse</h2>
                    <p class="text-xs text-gray-400 font-medium mb-6">Que souhaitez-vous faire ?</p>

                    <div class="space-y-3">
                        <button @click="showWrongAnswerModal = false; showHints = true"
                                class="w-full h-12 rounded-xl text-xs font-black uppercase tracking-widest text-white flex items-center justify-center gap-2 transition bg-blue-500/10 border border-blue-500/20 hover:bg-blue-500/20">
                            <span>🔍</span> Obtenir d'autres indices
                        </button>
                        <button @click="revealSolutionState = true"
                                class="w-full h-12 rounded-xl text-xs font-black uppercase tracking-widest text-white flex items-center justify-center gap-2 transition bg-amber-500/10 border border-amber-500/20 hover:bg-amber-500/20">
                            <span>💡</span> Fournir la solution
                        </button>
                        <button @click="skipRiddle" :disabled="isSkipping"
                                class="w-full h-12 rounded-xl text-xs font-black uppercase tracking-widest text-white flex items-center justify-center gap-2 transition bg-gray-500/10 border border-gray-500/20 hover:bg-gray-500/20">
                            <svg v-if="isSkipping" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span v-else>⏭️ Passer à une autre énigme</span>
                        </button>
                    </div>
                    
                    <button @click="showWrongAnswerModal = false" class="mt-5 text-[10px] text-gray-500 uppercase tracking-widest font-black hover:text-white">
                        Fermer et réessayer
                    </button>
                </div>

                <div v-else>
                    <div class="w-20 h-20 bg-amber-500/10 border border-amber-500/20 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl">💡</span>
                    </div>
                    <h2 class="text-xl font-black text-white uppercase mb-2">Solution</h2>
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-4 mb-6">
                        <p class="text-2xl font-black text-amber-500">{{ riddle.answer || 'Aucune réponse attendue' }}</p>
                    </div>
                    <button @click="revealSolutionState = false; showWrongAnswerModal = false"
                            class="w-full h-12 rounded-xl font-black uppercase tracking-widest text-white bg-amber-600 hover:bg-amber-500 transition">
                        Compris
                    </button>
                </div>

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

            <!-- ── OFFLINE TOAST / NOTIFICATION ── -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-10 scale-95"
                leave-active-class="transition-all duration-200 ease-in"
                leave-to-class="opacity-0 translate-y-10 scale-95"
            >
                <div v-if="showOfflineToast" class="fixed bottom-6 left-4 right-4 z-50 max-w-sm mx-auto rounded-2xl p-4 shadow-xl border flex items-center gap-3 backdrop-blur-md"
                     :style="{
                         background: isSyncing ? 'rgba(214,90,49,0.95)' : 'rgba(28,24,22,0.95)',
                         color: 'white',
                         borderColor: isSyncing ? 'rgba(214,90,49,0.3)' : 'rgba(255,255,255,0.1)'
                     }">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-white/10 shrink-0 text-lg">
                        {{ isSyncing ? '🔄' : '💾' }}
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-bold leading-relaxed">{{ offlineToastMessage }}</p>
                    </div>
                </div>
            </Transition>

    </PlayerLayout>
</template>

<style scoped>
/* ── Textes thémés ── */
.cp-text-primary   { color: var(--text-primary); }
.cp-text-secondary { color: var(--text-secondary); }
.cp-text-muted     { color: var(--text-muted); }

/* ── Bouton pause (header) ── */
.pause-btn {
    width: 36px; height: 36px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    background: var(--input-bg);
    border: 1px solid var(--border-subtle);
    color: var(--text-secondary);
    transition: background 0.2s, border-color 0.2s, color 0.2s;
}
.pause-btn:hover { background: var(--bg-surface); color: var(--text-primary); }
.pause-btn-active {
    background: rgba(245,158,11,0.15);
    border-color: rgba(245,158,11,0.3);
    color: #f59e0b;
}

/* ── Bannière pause ── */
.pause-banner {
    background: rgba(245,158,11,0.08);
    border: 1px solid rgba(245,158,11,0.2);
}
.resume-btn {
    display: inline-flex; align-items: center; gap: 6px;
    height: 36px; padding: 0 16px; border-radius: 12px;
    font-size: 12px; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: #fff;
    background: linear-gradient(135deg, #d65a31, #b84a24);
    box-shadow: 0 4px 14px rgba(214,90,49,0.35);
    transition: transform 0.15s, box-shadow 0.15s;
}
.resume-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(214,90,49,0.45); }
.resume-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Carte énigme ── */
.riddle-card {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    transition: background 0.35s, opacity 0.3s;
}

/* ── QCM ── */
.qcm-btn {
    width: 100%; height: 56px;
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    border-radius: 16px;
    color: var(--text-primary);
    padding: 0 20px;
    font-weight: 700;
    text-align: left;
    display: flex; align-items: center; justify-content: space-between;
    transition: border-color 0.2s, background 0.2s;
}
.qcm-btn:hover:not(:disabled) { border-color: rgba(214,90,49,0.4); background: var(--bg-surface); }
.qcm-btn:disabled { opacity: 0.45; cursor: not-allowed; }

/* ── Input réponse ── */
.answer-input {
    width: 100%; height: 52px;
    padding: 0 16px; border-radius: 16px;
    background: var(--input-bg);
    border: 1px solid var(--input-border);
    color: var(--input-text);
    font-size: 14px; font-weight: 600;
    outline: none;
    transition: border-color 0.2s, background 0.2s;
}
.answer-input::placeholder { color: var(--text-muted); }
.answer-input:focus { border-color: rgba(214,90,49,0.5); background: rgba(214,90,49,0.03); }

/* ── Bouton valider ── */
.validate-btn-active {
    background: linear-gradient(135deg, #d65a31, #b84a24);
    color: #fff;
    box-shadow: 0 8px 24px rgba(214,90,49,0.35);
}
.validate-btn-active:hover { box-shadow: 0 10px 28px rgba(214,90,49,0.45); }
.validate-btn-disabled {
    background: var(--input-bg);
    color: var(--text-muted);
    border: 1px solid var(--border-subtle);
    cursor: not-allowed;
}

/* ── Bouton indice ── */
.hint-btn {
    width: 64px; height: 64px;
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    color: #d65a31;
    transition: background 0.2s;
}
.hint-btn:hover { background: var(--bg-surface); }

/* ── Menu pause (bottom sheet) ── */
.pause-menu {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    box-shadow: 0 -8px 48px rgba(0,0,0,0.3);
}
.menu-action-btn {
    width: 100%;
    display: flex; align-items: center; gap: 14px;
    padding: 14px 16px;
    border-radius: 18px;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    transition: background 0.2s, border-color 0.2s;
    text-align: left;
}
.menu-action-btn:hover { background: var(--bg-card); }
.menu-action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.menu-icon {
    width: 40px; height: 40px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid;
    flex-shrink: 0;
}
.menu-pause:hover  { border-color: rgba(245,158,11,0.25); }
.menu-resume:hover { border-color: rgba(34,197,94,0.25); }
.menu-abandon:hover { border-color: rgba(239,68,68,0.25); }
.menu-cancel-btn {
    width: 100%; height: 48px; border-radius: 16px;
    font-size: 13px; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--text-muted);
    background: var(--input-bg);
    border: 1px solid var(--border-subtle);
    transition: background 0.15s, color 0.15s;
}
.menu-cancel-btn:hover { background: var(--bg-surface); color: var(--text-secondary); }

/* ── Animations ── */
@keyframes bounceIn {
    0%   { transform: scale(0.3); opacity: 0; }
    50%  { transform: scale(1.05); }
    70%  { transform: scale(0.9); }
    100% { transform: scale(1); opacity: 1; }
}
.animate-bounce-in { animation: bounceIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); }

@keyframes spin { to { transform: rotate(360deg); } }
.animate-spin { animation: spin 0.8s linear infinite; }
</style>
