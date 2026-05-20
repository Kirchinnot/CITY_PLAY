<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { gsap } from 'gsap';

const props = defineProps({
    riddle: Object,
    unlockedHintIds: Array,
    qcmValidated: { type: Boolean, default: false },
    selectedAnswer: { type: String, default: null },
});

// ── Étape courante : 'qcm' | 'onsite' ─────────────────────────────────────
const step = ref(props.qcmValidated ? 'onsite' : 'qcm');

// ── GPS & State ─────────────────────────────────────────────────────────────
const userLocation = ref(null);
const distanceToTarget = ref(null);
const watchId = ref(null);
const geoError = ref(null);

const isSubmittingAnswer = ref(false);
const isValidatingPresence = ref(false);
const showResult = ref(false);
const showHints = ref(false);
const resultData = ref(null);
const answerInput = ref(props.selectedAnswer || '');
const localUnlockedHintIds = ref([...props.unlockedHintIds]);

const validationRadius = computed(() => props.riddle.place?.validation_radius ?? 30);
const isInValidationZone = computed(() => distanceToTarget.value !== null && distanceToTarget.value <= validationRadius.value);
const isQcm = computed(() => props.riddle.options && props.riddle.options.length > 0);

const coverImageUrl = computed(() => {
    const riddleImg = props.riddle.images?.[0]?.image_url || props.riddle.images?.[0]?.image_path;
    if (riddleImg) return riddleImg;
    const placeImg = props.riddle.place?.images?.[0]?.image_url || props.riddle.place?.images?.[0]?.image_path;
    if (placeImg) return placeImg;
    return '/placeholder-place.svg';
});

const hintsCount = computed(() => props.riddle.hints?.length || 0);
const unlockedHintsCount = computed(() => localUnlockedHintIds.value.length);
const canSubmitAnswer = computed(() => !!answerInput.value && !isSubmittingAnswer.value && step.value === 'qcm');
const canValidatePresence = computed(() => isInValidationZone.value && !isValidatingPresence.value && step.value === 'onsite');
const locationStatus = computed(() => {
    if (geoError.value) return geoError.value;
    if (distanceToTarget.value === null) return 'Localisation en cours...';
    if (isInValidationZone.value) return 'Zone de validation atteinte';
    return `À ${Math.round(distanceToTarget.value)} m de la cible (max ${validationRadius.value} m)`;
});

// ── Lifecycle ───────────────────────────────────────────────────────────────
onMounted(() => {
    startWatchingLocation();

    gsap.from('.riddle-card', { scale: 0.92, opacity: 0, duration: 0.8, ease: 'back.out(1.7)' });
    gsap.from('.riddle-image', { y: 24, opacity: 0, duration: 1, ease: 'power4.out', delay: 0.25 });
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
        () => { geoError.value = 'GPS requis pour valider sur place.'; },
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
    const a = Math.sin(Δφ / 2) ** 2 + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) ** 2;
    distanceToTarget.value = R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
};

// ── Actions ─────────────────────────────────────────────────────────────────
const submitAnswer = async () => {
    if (isSubmittingAnswer.value || !answerInput.value) return;
    isSubmittingAnswer.value = true;

    try {
        const res = await axios.post(route('player.riddle.submit-answer', props.riddle.id), {
            answer: answerInput.value,
        });

        if (res.data.status === 'success') {
            step.value = 'onsite';
            if (res.data.selected_answer) {
                answerInput.value = res.data.selected_answer;
            }
        }
    } catch (e) {
        const data = e.response?.data;
        if (data?.step === 'onsite') {
            step.value = 'onsite';
            if (data.selected_answer) answerInput.value = data.selected_answer;
            return;
        }
        await alertModal({ message: data?.message || 'Erreur de validation' });
    } finally {
        isSubmittingAnswer.value = false;
    }
};

const validatePresence = async () => {
    if (isValidatingPresence.value) return;
    isValidatingPresence.value = true;

    try {
        const res = await axios.post(route('player.riddle.validate-presence', props.riddle.id), {
            lat: userLocation.value?.lat,
            lng: userLocation.value?.lng,
        });

        if (res.data.status === 'success') {
            resultData.value = res.data;
            showResult.value = true;
            gsap.to('.riddle-card', { y: -50, opacity: 0, duration: 0.5 });
        }
    } catch (e) {
        await alertModal({ message: e.response?.data?.message || 'Erreur de validation' });
    } finally {
        isValidatingPresence.value = false;
    }
};

const unlockHint = async (hintId) => {
    if (localUnlockedHintIds.value.includes(hintId)) return;

    try {
        await axios.post(route('player.riddle.unlock-hint', props.riddle.id), { hint_id: hintId });
        localUnlockedHintIds.value.push(hintId);
    } catch (e) {
        console.error(e);
    }
};

const skipRiddle = async () => {
    if (!(await confirmModal({
        header: 'Passer l\'énigme',
        message: 'Passer cette énigme ? Vous ne gagnerez aucun point.',
        acceptLabel: 'Continuer',
        rejectLabel: 'Annuler',
    }))) return;
    router.post(route('player.riddle.skip', props.riddle.id));
};
</script>

<template>
    <Head :title="`Énigme : ${riddle.place?.name}`" />

    <PlayerLayout>
        <div class="min-h-[calc(100vh-160px)] bg-[radial-gradient(circle_120px_at_top,_rgba(245,158,11,0.12),_transparent_30%),radial-gradient(circle_140px_at_bottom_right,_rgba(34,197,94,0.08),_transparent_30%)] px-4 py-6">

            <!-- Result Overlay (Success) -->
            <Transition name="scale">
                <div v-if="showResult" class="fixed inset-0 z-50 bg-slate-950/95 flex flex-col items-center justify-center p-8 text-center">
                    <div class="mb-8 flex h-32 w-32 items-center justify-center rounded-full bg-emerald-500 shadow-[0_0_50px_rgba(16,185,129,0.3)]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="mb-3 text-4xl font-black uppercase tracking-tight text-white">Mission Accomplie</h2>
                    <p class="mb-10 max-w-md text-sm leading-6 text-slate-300">Vous avez percé le mystère de <strong class="text-white">{{ riddle.place?.name }}</strong>. La route vers la suite est ouverte.</p>

                    <Link
                        :href="route('player.game.map')"
                        class="inline-flex w-full max-w-xs items-center justify-center rounded-[32px] bg-white px-6 py-4 text-base font-black uppercase tracking-[0.16em] text-slate-950 shadow-2xl transition hover:-translate-y-0.5"
                    >
                        CONTINUER
                    </Link>
                </div>
            </Transition>

            <!-- Main Riddle Card -->
            <div v-if="!showResult" class="riddle-card mx-auto flex min-h-[calc(100vh-220px)] max-w-4xl flex-col overflow-hidden rounded-[40px] border border-white/10 bg-slate-950/90 shadow-2xl backdrop-blur-xl">

                <!-- Image Header -->
                <div class="riddle-image relative h-64 overflow-hidden">
                    <img :src="coverImageUrl" class="h-full w-full object-cover" alt="Illustration énigme" />
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-transparent to-transparent"></div>

                    <div class="absolute inset-x-6 top-6 flex items-center justify-between">
                        <Link :href="route('player.game.map')" class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950/75 text-white shadow-lg ring-1 ring-white/10 transition hover:bg-slate-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>

                        <div class="grid grid-cols-2 gap-2">
                            <span :class="['rounded-2xl border px-3 py-2 text-[10px] font-black uppercase tracking-[0.24em]', step === 'qcm' ? 'bg-amber-400/10 border-amber-400 text-amber-300' : 'bg-white/10 border-white/10 text-slate-300']">1. Réponse</span>
                            <span :class="['rounded-2xl border px-3 py-2 text-[10px] font-black uppercase tracking-[0.24em]', step === 'onsite' ? 'bg-emerald-400/10 border-emerald-400 text-emerald-300' : 'bg-white/10 border-white/10 text-slate-300']">2. Sur place</span>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex flex-1 flex-col gap-6 p-8 text-white">
                    <div class="space-y-6">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div class="max-w-2xl">
                                <p class="text-sm uppercase tracking-[0.28em] text-amber-300">{{ riddle.place?.name || 'Lieu mystère' }}</p>
                                <h1 class="mt-3 text-3xl font-black leading-tight tracking-tight">{{ riddle.question }}</h1>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-white/5 px-5 py-4 text-xs uppercase tracking-[0.22em] text-slate-300">
                                {{ props.riddle.difficulty || 'Niveau inconnu' }}
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-3xl border border-white/10 bg-slate-950/80 p-4">
                                <p class="text-[10px] uppercase tracking-[0.24em] text-slate-400">Mode</p>
                                <p class="mt-2 text-sm font-black uppercase tracking-[0.12em] text-white">{{ props.riddle.mode || 'Exploration' }}</p>
                            </div>
                            <div class="rounded-3xl border border-white/10 bg-slate-950/80 p-4">
                                <p class="text-[10px] uppercase tracking-[0.24em] text-slate-400">Validation</p>
                                <p class="mt-2 text-sm font-black uppercase tracking-[0.12em] text-white">{{ validationRadius }} m</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1">
                        <div v-if="step === 'qcm'" class="space-y-4">
                            <div v-if="isQcm" class="grid gap-3">
                                <button
                                    v-for="(option, index) in riddle.options"
                                    :key="index"
                                    type="button"
                                    @click="answerInput = option"
                                    :class="[
                                        'w-full rounded-3xl border px-5 py-4 text-left font-bold transition duration-200',
                                        answerInput === option ? 'bg-amber-400/20 border-amber-400 text-white' : 'bg-white/5 border-white/10 text-slate-300 hover:bg-white/10 hover:border-white/20'
                                    ]"
                                >
                                    <div class="flex items-center gap-4">
                                        <div :class="['flex h-10 w-10 items-center justify-center rounded-2xl text-sm font-black', answerInput === option ? 'bg-amber-400 text-slate-950' : 'bg-slate-900 text-slate-400']">{{ String.fromCharCode(65 + index) }}</div>
                                        <span class="leading-snug">{{ option }}</span>
                                    </div>
                                </button>
                            </div>

                            <textarea
                                v-else
                                v-model="answerInput"
                                placeholder="Votre réponse ici..."
                                class="min-h-[150px] w-full rounded-[28px] border border-white/10 bg-white/5 p-5 text-base text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none focus:ring-0"
                            ></textarea>

                            <p class="text-sm text-slate-400">Confirmez votre réponse, puis rendez-vous sur place pour valider votre présence.</p>
                        </div>

                        <div v-else class="space-y-6">
                            <div class="rounded-3xl border border-emerald-500/20 bg-emerald-500/10 p-5">
                                <p class="text-xs uppercase tracking-[0.24em] text-emerald-300">Réponse validée</p>
                                <p class="mt-3 text-lg font-black text-white">{{ answerInput }}</p>
                                <p class="mt-2 text-sm text-slate-400">Rendez-vous sur place pour confirmer votre présence au point <strong>{{ riddle.place?.name }}</strong>.</p>
                            </div>

                            <div class="rounded-3xl border border-white/10 bg-slate-950/80 p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-[0.24em] text-slate-500">Localisation</p>
                                        <p class="mt-2 text-sm font-black text-white">{{ locationStatus }}</p>
                                    </div>
                                    <span :class="['inline-flex rounded-full px-3 py-2 text-xs font-black uppercase tracking-[0.2em]', isInValidationZone ? 'bg-emerald-500/15 border border-emerald-500/20 text-emerald-300' : 'bg-rose-500/15 border border-rose-500/20 text-rose-300']">
                                        {{ isInValidationZone ? 'Prêt' : 'En route' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button
                            v-if="step === 'qcm'"
                            @click="submitAnswer"
                            :disabled="!canSubmitAnswer"
                            class="w-full rounded-[2rem] bg-amber-500 px-6 py-5 text-base font-black uppercase tracking-[0.16em] text-slate-950 shadow-[0_20px_50px_-20px_rgba(245,158,11,0.8)] transition duration-200 hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:bg-slate-800"
                        >
                            {{ isSubmittingAnswer ? 'VÉRIFICATION...' : 'CONFIRMER MA RÉPONSE' }}
                        </button>

                        <button
                            v-else
                            @click="validatePresence"
                            :disabled="!canValidatePresence"
                            class="w-full rounded-[2rem] bg-emerald-500 px-6 py-5 text-base font-black uppercase tracking-[0.16em] text-slate-950 shadow-[0_20px_50px_-20px_rgba(16,185,129,0.8)] transition duration-200 hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:bg-slate-800"
                        >
                            {{ isValidatingPresence ? 'VÉRIFICATION...' : 'VALIDER SUR PLACE' }}
                        </button>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <button
                                @click="showHints = !showHints"
                                class="rounded-3xl border border-white/10 bg-white/5 px-5 py-4 text-sm font-black uppercase tracking-[0.16em] text-white transition hover:bg-white/10"
                            >
                                INDICES ({{ unlockedHintsCount }}/{{ hintsCount }})
                            </button>
                            <button
                                @click="skipRiddle"
                                class="rounded-3xl border border-white/10 bg-slate-900/80 px-5 py-4 text-sm font-black uppercase tracking-[0.16em] text-slate-300 transition hover:bg-slate-900"
                            >
                                PASSER L'ÉNIGME
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hints Drawer -->
            <Transition name="slide-up">
                <div v-if="showHints" class="fixed inset-x-0 bottom-0 z-40 max-h-[70vh] overflow-y-auto rounded-t-[40px] border-t border-white/10 bg-slate-950/95 p-8 shadow-2xl backdrop-blur-2xl">
                    <div class="mx-auto mb-8 h-1.5 w-16 rounded-full bg-white/10"></div>
                    <h3 class="mb-6 text-2xl font-black text-white tracking-tight">Besoin d'aide ?</h3>

                    <div class="space-y-4">
                        <div v-for="(hint, index) in riddle.hints" :key="hint.id" class="rounded-3xl border border-white/10 bg-white/5 p-6">
                            <div v-if="localUnlockedHintIds.includes(hint.id)">
                                <p class="text-[10px] uppercase tracking-[0.24em] text-amber-300">Indice {{ index + 1 }}</p>
                                <p class="mt-3 text-sm text-slate-100">{{ hint.content }}</p>
                            </div>
                            <div v-else class="flex items-center justify-between gap-4">
                                <span class="text-sm italic text-slate-400">Indice caché</span>
                                <button
                                    @click="unlockHint(hint.id)"
                                    class="rounded-2xl bg-white px-4 py-2 text-xs font-black uppercase tracking-[0.18em] text-slate-950 transition hover:bg-slate-100"
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
