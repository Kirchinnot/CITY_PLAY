<script setup>
import { computed, onMounted, ref, nextTick } from 'vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { useTheme } from '@/composables/useTheme';

const page = usePage();
const user       = computed(() => page.props.auth.user);
const session    = computed(() => page.props.session);
const cities     = computed(() => page.props.cities);
const adminStats = computed(() => page.props.adminStats);

const currentLayout = computed(() =>
    user.value.role === 'admin' ? AdminLayout : PlayerLayout
);

// ── Thème ────────────────────────────────────────────────────────────────────
const { isDark, toggleTheme } = useTheme();

// ── État session ──────────────────────────────────────────────────────────────
const hasActiveSession = computed(() => session.value?.status === 'active');
const progressPercent  = computed(() => {
    if (!session.value) return 0;
    const { solved_places, total_places } = session.value;
    return total_places > 0 ? Math.round((solved_places / total_places) * 100) : 0;
});

// ── Refs GSAP ─────────────────────────────────────────────────────────────────
const greetRef        = ref(null);
const subtitleRef     = ref(null);
const sessionCardRef  = ref(null);
const progressBarRef  = ref(null);
const statBadgesRef   = ref(null);
const resumeBtnRef    = ref(null);
const noSessionRef    = ref(null);
const sectionTitleRef = ref(null);
const themeBtnRef     = ref(null);
const cityCardsRef    = ref([]);
const orb1Ref         = ref(null);
const orb2Ref         = ref(null);

const setCityCardRef = (el, i) => { if (el) cityCardsRef.value[i] = el; };

// ── Démarrer une session ──────────────────────────────────────────────────────
const showSetupModal = ref(false);
const startSessionForm = useForm({
    city_id: null,
    team_members: 4,
    duration_minutes: 120,
    locomotion: 'marche',
    difficulty: 'force_1'
});

const openSetupModal = (cityId) => {
    startSessionForm.city_id = cityId;
    showSetupModal.value = true;
};

const startSession = () => {
    startSessionForm.post(route('player.game-sessions.store'), {
        onSuccess: () => {
            showSetupModal.value = false;
        }
    });
};

// ── Palettes villes ───────────────────────────────────────────────────────────
const cityIcons = [
    'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
    'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z',
    'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 7',
];
const cityAccents = ['#d65a31', '#3b82f6', '#10b981', '#8b5cf6'];
const cityGlow    = ['rgba(214,90,49,0.12)', 'rgba(59,130,246,0.12)', 'rgba(16,185,129,0.12)', 'rgba(139,92,246,0.12)'];

// ── GSAP ──────────────────────────────────────────────────────────────────────
onMounted(async () => {
    if (user.value.role === 'admin') return;
    await nextTick();

    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    if (orb1Ref.value) gsap.to(orb1Ref.value, { y: -40, x: 25, duration: 7,  repeat: -1, yoyo: true, ease: 'sine.inOut' });
    if (orb2Ref.value) gsap.to(orb2Ref.value, { y:  30, x: -20, duration: 9, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 1.5 });

    tl.fromTo(subtitleRef.value, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.55 })
      .fromTo(greetRef.value,    { opacity: 0, y: 32, filter: 'blur(10px)' }, { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.8 }, '-=0.35')
      .fromTo(themeBtnRef.value, { opacity: 0, scale: 0.7 }, { opacity: 1, scale: 1, duration: 0.4, ease: 'back.out(2)' }, '-=0.5');

    if (sessionCardRef.value) {
        tl.fromTo(sessionCardRef.value, { opacity: 0, y: 48, scale: 0.96 }, { opacity: 1, y: 0, scale: 1, duration: 0.75 }, '-=0.3');
        if (progressBarRef.value)
            tl.fromTo(progressBarRef.value, { width: '0%' }, { width: progressPercent.value + '%', duration: 1.5, ease: 'power2.out' }, '-=0.3');
        if (statBadgesRef.value?.children?.length)
            tl.fromTo(statBadgesRef.value.children, { opacity: 0, scale: 0.75, y: 8 }, { opacity: 1, scale: 1, y: 0, duration: 0.45, stagger: 0.1 }, '-=1');
        if (resumeBtnRef.value)
            tl.fromTo(resumeBtnRef.value, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.45 }, '-=0.3');
    }
    if (noSessionRef.value)
        tl.fromTo(noSessionRef.value, { opacity: 0, y: 40, scale: 0.97 }, { opacity: 1, y: 0, scale: 1, duration: 0.7 }, '-=0.3');
    if (sectionTitleRef.value)
        tl.fromTo(sectionTitleRef.value, { opacity: 0, x: -24 }, { opacity: 1, x: 0, duration: 0.55 }, '-=0.2');

    const cards = cityCardsRef.value.filter(Boolean);
    if (cards.length)
        tl.fromTo(cards, { opacity: 0, y: 56, scale: 0.88 }, { opacity: 1, y: 0, scale: 1, duration: 0.55, stagger: 0.09, ease: 'back.out(1.5)' }, '-=0.3');
});

// Animation du bouton toggle
const animateToggle = () => {
    gsap.fromTo(themeBtnRef.value,
        { rotate: 0, scale: 1 },
        { rotate: 360, scale: 1.15, duration: 0.45, ease: 'back.out(2)',
          onComplete: () => gsap.to(themeBtnRef.value, { scale: 1, duration: 0.15 }) }
    );
    toggleTheme();
};
</script>

<template>
    <Head title="Accueil" />
    <component :is="currentLayout">
        <template #header>
            <span v-if="user.role === 'admin'">Administration</span>
            <span v-else>Accueil</span>
        </template>

        <!-- ══════════════════════ ADMIN ══════════════════════ -->
        <div v-if="user.role === 'admin'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-bold uppercase mb-2">Sessions Actives</h3>
                    <p class="text-3xl font-black text-[#d65a31]">{{ adminStats?.active_sessions ?? 0 }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-bold uppercase mb-2">Joueurs Connectés</h3>
                    <p class="text-3xl font-black text-blue-600">{{ adminStats?.total_players ?? 0 }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-bold uppercase mb-2">Alertes Triche</h3>
                    <p class="text-3xl font-black text-red-500">{{ adminStats?.suspicious_logs ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- ══════════════════════ JOUEUR ══════════════════════ -->
        <div v-else class="relative">

            <!-- Orbs GSAP -->
            <div ref="orb1Ref" class="pointer-events-none fixed top-10 right-0 w-80 h-80 rounded-full opacity-60"
                 style="background: radial-gradient(circle, var(--orb-orange) 0%, transparent 70%); z-index:0;"></div>
            <div ref="orb2Ref" class="pointer-events-none fixed bottom-32 left-0 w-64 h-64 rounded-full opacity-60"
                 style="background: radial-gradient(circle, var(--orb-indigo) 0%, transparent 70%); z-index:0;"></div>

            <!-- Flash error -->
            <div v-if="$page.props.flash?.error"
                 class="mb-5 flex items-center gap-3 px-4 py-3 rounded-2xl text-red-500 text-xs font-bold uppercase tracking-wide"
                 style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ $page.props.flash.error }}
            </div>

            <!-- ── GREETING + BOUTON THÈME ── -->
            <div class="mb-7">
                <p ref="subtitleRef" class="text-[11px] font-black text-[#d65a31] uppercase tracking-[0.22em] mb-1.5">
                    {{ new Date().getHours() < 12 ? '☀️ Bonjour' : new Date().getHours() < 18 ? '🌤 Bon après-midi' : '🌙 Bonsoir' }}
                </p>
                <div class="flex items-center justify-between gap-3">
                    <h1 ref="greetRef" class="text-[2rem] font-black leading-none tracking-tight cp-text-primary">
                        {{ user.name }}<span class="text-[#d65a31]">.</span>
                    </h1>

                    <div class="flex items-center gap-2 shrink-0">
                        <!-- ── BOUTON TOGGLE THÈME ── -->
                        <button
                            ref="themeBtnRef"
                            @click="animateToggle"
                            class="theme-toggle-btn"
                            :title="isDark ? 'Passer en mode clair' : 'Passer en mode sombre'"
                            :aria-label="isDark ? 'Mode clair' : 'Mode sombre'"
                        >
                            <!-- Track -->
                            <span class="theme-track" :class="isDark ? 'track-dark' : 'track-light'">
                                <!-- Thumb -->
                                <span class="theme-thumb" :class="isDark ? 'thumb-dark' : 'thumb-light'">
                                    <!-- Icône lune (dark) -->
                                    <svg v-if="isDark" class="w-3 h-3 text-indigo-200" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                                    </svg>
                                    <!-- Icône soleil (light) -->
                                    <svg v-else class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2a1 1 0 011 1v1a1 1 0 01-2 0V3a1 1 0 011-1zm0 16a1 1 0 011 1v1a1 1 0 01-2 0v-1a1 1 0 011-1zm8-6a1 1 0 010 2h-1a1 1 0 010-2h1zM4 12a1 1 0 010 2H3a1 1 0 010-2h1zm13.66-5.66a1 1 0 010 1.41l-.71.71a1 1 0 01-1.41-1.41l.71-.71a1 1 0 011.41 0zM7.05 16.95a1 1 0 010 1.41l-.71.71a1 1 0 01-1.41-1.41l.71-.71a1 1 0 011.41 0zm9.9 0a1 1 0 011.41 0l.71.71a1 1 0 01-1.41 1.41l-.71-.71a1 1 0 010-1.41zM5.64 7.05a1 1 0 011.41 0l.71.71A1 1 0 016.35 9.17l-.71-.71a1 1 0 010-1.41zM12 7a5 5 0 110 10A5 5 0 0112 7z"/>
                                    </svg>
                                </span>
                            </span>
                            <span class="theme-label cp-text-muted">{{ isDark ? 'Nuit' : 'Jour' }}</span>
                        </button>

                        <!-- Avatar -->
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-black text-white text-base shadow-lg"
                             style="background: linear-gradient(135deg, #d65a31, #b84a24); box-shadow: 0 8px 24px rgba(214,90,49,0.35);">
                            {{ user.name?.charAt(0).toUpperCase() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── PARTIE ACTIVE ── -->
            <div v-if="hasActiveSession" ref="sessionCardRef" class="cp-session-card relative mb-7 rounded-[28px] overflow-hidden">
                <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(214,90,49,0.08) 0%, transparent 55%);"></div>
                <div class="absolute inset-0 opacity-[0.04]"
                     style="background-image: linear-gradient(rgba(128,128,128,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(128,128,128,0.5) 1px, transparent 1px); background-size: 28px 28px;"></div>
                <div class="absolute bottom-0 right-0 w-40 h-40 rounded-full opacity-15"
                     style="background: radial-gradient(circle, #d65a31 0%, transparent 70%); transform: translate(30%, 30%);"></div>

                <div class="relative z-10 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span class="text-[10px] font-black text-green-500 uppercase tracking-[0.2em]">Partie en cours</span>
                        </div>
                        <span class="text-[10px] font-black cp-text-muted uppercase tracking-widest">
                            {{ session.difficulty?.replace('force_', 'Niv. ') }}
                        </span>
                    </div>

                    <h2 class="text-2xl font-black cp-text-primary mb-0.5 leading-tight">{{ session.city?.name }}</h2>
                    <p class="text-xs font-bold cp-text-muted mb-5">
                        {{ session.solved_places ?? 0 }} / {{ session.total_places ?? 0 }} lieux découverts
                    </p>

                    <div class="mb-5">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[10px] font-black cp-text-muted uppercase tracking-widest">Progression</span>
                            <span class="text-[10px] font-black text-[#d65a31]">{{ progressPercent }}%</span>
                        </div>
                        <div class="h-1.5 w-full rounded-full overflow-hidden cp-progress-track">
                            <div ref="progressBarRef" class="h-full rounded-full"
                                 style="width: 0%; background: linear-gradient(90deg, #d65a31, #f07040); box-shadow: 0 0 8px rgba(214,90,49,0.5);"></div>
                        </div>
                    </div>

                    <div ref="statBadgesRef" class="flex items-center gap-2.5 mb-6 flex-wrap">
                        <div class="stat-badge" style="--c: rgba(214,90,49,0.12); --b: rgba(214,90,49,0.25); --t: #d65a31;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ session.available_minutes ?? '—' }} min</span>
                        </div>
                        <div class="stat-badge" style="--c: rgba(234,179,8,0.1); --b: rgba(234,179,8,0.22); --t: #ca8a04;">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <span>{{ session.total_score ?? 0 }} pts</span>
                        </div>
                        <div v-if="session.achievements_count" class="stat-badge" style="--c: rgba(139,92,246,0.1); --b: rgba(139,92,246,0.22); --t: #7c3aed;">
                            <span>🏆</span>
                            <span>{{ session.achievements_count }} badge{{ session.achievements_count > 1 ? 's' : '' }}</span>
                        </div>
                    </div>

                    <div ref="resumeBtnRef">
                        <Link v-if="session.current_riddle"
                              :href="route('player.riddle.show', session.current_riddle.id)"
                              class="cta-btn group relative w-full flex items-center justify-center gap-2.5 h-[52px] rounded-2xl font-black uppercase tracking-widest text-sm text-white overflow-hidden">
                            <span class="relative z-10">Reprendre l'aventure</span>
                            <svg class="relative z-10 w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            <div class="shimmer absolute inset-0"></div>
                        </Link>
                        <div v-else class="w-full flex items-center justify-center gap-2 h-[52px] rounded-2xl cp-icon-btn">
                            <svg class="w-4 h-4 cp-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/>
                            </svg>
                            <span class="text-xs font-bold cp-text-muted uppercase tracking-widest">Aucune énigme disponible</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── PAS DE PARTIE ── -->
            <div v-else ref="noSessionRef" class="cp-no-session relative mb-7 rounded-[28px] overflow-hidden">
                <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(214,90,49,0.05) 0%, transparent 60%);"></div>
                <div class="absolute inset-0 opacity-[0.025]"
                     style="background-image: radial-gradient(circle, rgba(128,128,128,0.8) 1px, transparent 1px); background-size: 20px 20px;"></div>
                <div class="relative z-10 p-6 text-center">
                    <div class="mx-auto mb-5 w-16 h-16 rounded-2xl flex items-center justify-center"
                         style="background: rgba(214,90,49,0.1); border: 1px solid rgba(214,90,49,0.2);">
                        <svg class="w-8 h-8 text-[#d65a31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 7"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-black cp-text-primary mb-1.5">Prêt à explorer ?</h2>
                    <p class="text-sm cp-text-secondary font-medium mb-6 leading-relaxed">
                        Choisis une ville ci-dessous et<br>lance ton aventure urbaine.
                    </p>
                    <div class="flex items-center justify-center gap-2 text-[#d65a31]">
                        <span class="text-[11px] font-black uppercase tracking-[0.2em]">Sélectionne une ville</span>
                        <svg class="w-4 h-4 animate-bounce-down" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- ── SECTION VILLES ── -->
            <div>
                <div ref="sectionTitleRef" class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-black cp-text-primary uppercase tracking-[0.15em]">
                            {{ hasActiveSession ? 'Autres villes' : 'Destinations' }}
                        </h3>
                        <p class="text-[10px] cp-text-muted font-bold mt-0.5">
                            {{ cities.length }} ville{{ cities.length > 1 ? 's' : '' }} disponible{{ cities.length > 1 ? 's' : '' }}
                        </p>
                    </div>
                    <div class="w-7 h-7 rounded-xl flex items-center justify-center"
                         style="background: rgba(214,90,49,0.1); border: 1px solid rgba(214,90,49,0.2);">
                        <svg class="w-3.5 h-3.5 text-[#d65a31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3.5">
                    <button
                        v-for="(city, i) in cities"
                        :key="city.id"
                        :ref="el => setCityCardRef(el, i)"
                        @click="openSetupModal(city.id)"
                        class="city-card group relative rounded-[22px] overflow-hidden text-left focus:outline-none"
                        :style="{ '--accent': cityAccents[i % cityAccents.length] }"
                        style="min-height: 172px;"
                    >
                        <div class="absolute inset-0 cp-city-bg"></div>
                        <div class="absolute inset-0 transition-opacity duration-300"
                             :style="{ background: 'linear-gradient(145deg, ' + cityGlow[i % cityGlow.length] + ' 0%, transparent 60%)' }"></div>
                        <div class="absolute inset-0 rounded-[22px] cp-city-border group-hover:border-opacity-20 transition-colors duration-300"></div>
                        <div class="absolute bottom-0 right-0 w-20 h-20 opacity-[0.06]"
                             style="background-image: radial-gradient(circle, rgba(128,128,128,1) 1px, transparent 1px); background-size: 7px 7px;"></div>

                        <div class="relative z-10 p-4 flex flex-col" style="min-height: 172px;">
                            <div class="w-9 h-9 rounded-xl mb-3 flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                                 :style="{ background: cityGlow[i % cityGlow.length], border: '1px solid ' + cityAccents[i % cityAccents.length] + '30' }">
                                <svg class="w-[18px] h-[18px] transition-colors duration-300"
                                     :style="{ color: cityAccents[i % cityAccents.length] }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="cityIcons[i % cityIcons.length]"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-[15px] font-black cp-text-primary leading-tight mb-1">{{ city.name }}</h4>
                                <p class="text-[10px] font-bold cp-text-muted uppercase tracking-widest leading-relaxed">
                                    {{ city.riddles }} lieu{{ city.riddles > 1 ? 'x' : '' }}<br>~{{ city.duration }}
                                </p>
                            </div>
                            <div class="flex items-center justify-between mt-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest"
                                      :style="{ background: cityAccents[i % cityAccents.length] + '18', border: '1px solid ' + cityAccents[i % cityAccents.length] + '35', color: cityAccents[i % cityAccents.length] }">
                                    {{ city.tag }}
                                </span>
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200 -translate-x-1 group-hover:translate-x-0"
                                     :style="{ background: cityAccents[i % cityAccents.length] + '18' }">
                                    <svg class="w-3 h-3" :style="{ color: cityAccents[i % cityAccents.length] }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute inset-0 rounded-[22px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                             :style="{ boxShadow: 'inset 0 0 0 1px ' + cityAccents[i % cityAccents.length] + '40' }"></div>
                    </button>
                </div>

                <div v-if="!cities.length" class="text-center py-14">
                    <div class="w-14 h-14 rounded-2xl cp-icon-btn flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 cp-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 7"/>
                        </svg>
                    </div>
                    <p class="cp-text-muted font-bold text-sm">Aucune ville disponible</p>
                </div>
            </div>
        </div>

        <!-- Modal Configuration de l'Aventure -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" leave-active-class="transition-all duration-150" leave-to-class="opacity-0">
                <div v-if="showSetupModal" class="fixed inset-0 z-[80] flex items-center justify-center p-4" style="background: rgba(28,24,22,0.75); backdrop-filter: blur(8px);" @click.self="showSetupModal = false">
                    <div class="bg-[#FFFDFB] dark:bg-[#1C1816] w-full max-w-sm rounded-[28px] overflow-hidden border border-gray-200 dark:border-white/5 shadow-2xl">
                        
                        <!-- Header Terracotta & Gold accent -->
                        <div style="background: linear-gradient(135deg, #C85C32, #A04422); padding: 1.5rem; color: white; position: relative;">
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #1E6B43 33%, #D4AF37 33%, #D4AF37 66%, #962D2D 66%);"></div>
                            <h3 class="font-black uppercase tracking-tight text-lg">Configurer l'Équipe</h3>
                            <p class="text-[10px] opacity-90 font-medium">Configure ton parcours sur-mesure avant de partir !</p>
                        </div>

                        <!-- Form Parameters -->
                        <form @submit.prevent="startSession" class="p-5 space-y-4">
                            
                            <!-- Team members -->
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">Membres de l'équipe (inférieur à 10)</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="startSessionForm.team_members = Math.max(1, startSessionForm.team_members - 1)" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 font-bold flex items-center justify-center text-gray-700 dark:text-white">-</button>
                                    <input type="number" v-model="startSessionForm.team_members" min="1" max="9" required class="flex-1 text-center font-black rounded-lg border border-gray-200 dark:border-white/10 dark:bg-[#1C1816] dark:text-white h-8 text-xs" />
                                    <button type="button" @click="startSessionForm.team_members = Math.min(9, startSessionForm.team_members + 1)" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 font-bold flex items-center justify-center text-gray-700 dark:text-white">+</button>
                                </div>
                            </div>

                            <!-- Duration -->
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">Durée prévue pour le jeu</label>
                                <select v-model="startSessionForm.duration_minutes" class="w-full rounded-lg border border-gray-200 dark:border-white/10 dark:bg-[#1C1816] dark:text-white h-9 text-xs font-bold px-2">
                                    <option :value="30">30 Minutes (Rapide ⚡)</option>
                                    <option :value="60">1 Heure (Standard 🧭)</option>
                                    <option :value="120">2 Heures (Aventure 🗺️)</option>
                                    <option :value="180">3 Heures (Exploration 👑)</option>
                                    <option :value="240">4 Heures (Grand Défi 🦁)</option>
                                </select>
                            </div>

                            <!-- Locomotion -->
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">Moyen de Locomotion</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" @click="startSessionForm.locomotion = 'marche'" :class="startSessionForm.locomotion === 'marche' ? 'bg-[#C85C32] text-white border-[#C85C32]' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/5'" class="h-12 rounded-lg border flex flex-col items-center justify-center transition-all">
                                        <span class="text-lg">🚶</span>
                                        <span class="text-[8px] font-black uppercase">Pieds</span>
                                    </button>
                                    <button type="button" @click="startSessionForm.locomotion = 'velo'" :class="startSessionForm.locomotion === 'velo' ? 'bg-[#C85C32] text-white border-[#C85C32]' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/5'" class="h-12 rounded-lg border flex flex-col items-center justify-center transition-all">
                                        <span class="text-lg">🚲</span>
                                        <span class="text-[8px] font-black uppercase">Vélos</span>
                                    </button>
                                    <button type="button" @click="startSessionForm.locomotion = 'voiture'" :class="startSessionForm.locomotion === 'voiture' ? 'bg-[#C85C32] text-white border-[#C85C32]' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/5'" class="h-12 rounded-lg border flex flex-col items-center justify-center transition-all">
                                        <span class="text-lg">🚗</span>
                                        <span class="text-[8px] font-black uppercase">Voiture</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Difficulty -->
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-1">Difficulté des énigmes</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" @click="startSessionForm.difficulty = 'force_1'" :class="startSessionForm.difficulty === 'force_1' ? 'bg-[#D4AF37] text-white border-[#D4AF37]' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/5'" class="h-9 rounded-lg border flex items-center justify-center transition-all text-[9px] font-black uppercase">
                                        F1 🏹
                                    </button>
                                    <button type="button" @click="startSessionForm.difficulty = 'force_2'" :class="startSessionForm.difficulty === 'force_2' ? 'bg-[#D4AF37] text-white border-[#D4AF37]' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/5'" class="h-9 rounded-lg border flex items-center justify-center transition-all text-[9px] font-black uppercase">
                                        F2 🦁
                                    </button>
                                    <button type="button" @click="startSessionForm.difficulty = 'force_3'" :class="startSessionForm.difficulty === 'force_3' ? 'bg-[#D4AF37] text-white border-[#D4AF37]' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-white/5'" class="h-9 rounded-lg border flex items-center justify-center transition-all text-[9px] font-black uppercase">
                                        F3 👑
                                    </button>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="pt-2 flex gap-2">
                                <button type="button" @click="showSetupModal = false" class="flex-1 h-9 rounded-lg border border-gray-200 dark:border-white/5 text-[10px] font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Annuler</button>
                                <button type="submit" :disabled="startSessionForm.processing" class="flex-1 h-9 rounded-lg text-[10px] font-black uppercase tracking-wider text-white bg-[#C85C32] hover:bg-[#A04422] transition-all shadow-md">Démarrer !</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </component>
</template>

<style scoped>
/* ── Textes thémés ── */
.cp-text-primary   { color: var(--text-primary); }
.cp-text-secondary { color: var(--text-secondary); }
.cp-text-muted     { color: var(--text-muted); }

/* ── Icon btn ── */
.cp-icon-btn {
    background: var(--input-bg);
    border: 1px solid var(--border-subtle);
}

/* ── Session card ── */
.cp-session-card {
    background: linear-gradient(145deg, var(--session-card-bg-from), var(--session-card-bg-to));
    border: 1px solid rgba(214,90,49,0.18);
    box-shadow: 0 24px 64px rgba(0,0,0,0.15);
    transition: background 0.35s;
}

/* ── No session card ── */
.cp-no-session {
    background: var(--bg-card);
    border: 1px solid var(--border-card);
    box-shadow: 0 16px 48px rgba(0,0,0,0.1);
    transition: background 0.35s;
}

/* ── Progress track ── */
.cp-progress-track { background: var(--border-subtle); }

/* ── City cards ── */
.cp-city-bg { background: var(--city-card-bg); transition: background 0.35s; }
.cp-city-border {
    border-radius: 22px;
    border: 1px solid var(--border-card);
}
.city-card {
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
}
.city-card:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 16px 40px rgba(0,0,0,0.12), 0 0 0 1px var(--accent, rgba(128,128,128,0.15));
}
.city-card:active { transform: scale(0.97); }

/* ── Stat badges ── */
.stat-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 10px; border-radius: 10px;
    background: var(--c); border: 1px solid var(--b); color: var(--t);
    font-size: 11px; font-weight: 900;
}

/* ── CTA button ── */
.cta-btn {
    background: linear-gradient(135deg, #d65a31 0%, #e8703a 50%, #c94e27 100%);
    box-shadow: 0 8px 28px rgba(214,90,49,0.4);
    transition: transform 0.2s, box-shadow 0.2s;
}
.cta-btn:hover { transform: scale(1.015); box-shadow: 0 12px 36px rgba(214,90,49,0.5); }
.cta-btn:active { transform: scale(0.985); }
.shimmer {
    background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.18) 50%, transparent 60%);
    background-size: 200% 100%; background-position: -100% 0;
}
.cta-btn:hover .shimmer { background-position: 200% 0; transition: background-position 0.6s ease; }

/* ══════════════════════════════════════════════
   BOUTON TOGGLE THÈME
══════════════════════════════════════════════ */
.theme-toggle-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px 4px 4px;
    border-radius: 999px;
    background: var(--bg-surface);
    border: 1px solid var(--border-card);
    cursor: pointer;
    transition: background 0.25s, border-color 0.25s, box-shadow 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.theme-toggle-btn:hover {
    box-shadow: 0 4px 16px rgba(214,90,49,0.2);
    border-color: rgba(214,90,49,0.3);
}
.theme-track {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    transition: background 0.3s;
}
.track-dark  { background: rgba(99,102,241,0.2); }
.track-light { background: rgba(251,191,36,0.2); }
.theme-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    transition: background 0.3s, transform 0.3s;
}
.thumb-dark  { background: rgba(99,102,241,0.35); }
.thumb-light { background: rgba(251,191,36,0.35); }
.theme-label {
    font-size: 10px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    transition: color 0.3s;
    min-width: 22px;
}

/* ── Animations ── */
@keyframes bounce-down {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(4px); }
}
.animate-bounce-down { animation: bounce-down 1.4s ease-in-out infinite; }

@keyframes ping {
    75%, 100% { transform: scale(2.2); opacity: 0; }
}
.animate-ping { animation: ping 1.3s cubic-bezier(0, 0, 0.2, 1) infinite; }
</style>
