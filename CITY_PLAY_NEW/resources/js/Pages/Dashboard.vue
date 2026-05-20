<script setup>
import { computed, onMounted, ref, nextTick } from 'vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import { useTheme } from '@/composables/useTheme';

const page = usePage();
const user       = computed(() => page.props.auth.user);
const session    = computed(() => page.props.gameState || page.props.session);
const cities     = computed(() => page.props.cities);
const adminStats = computed(() => page.props.adminStats);

const currentLayout = computed(() =>
    user.value.role === 'admin' ? AdminLayout : PlayerLayout
);

const { isDark, toggleTheme } = useTheme();

const hasActiveSession = computed(() => ['active', 'paused', 'pending'].includes(session.value?.status));
const isPending        = computed(() => session.value?.status === 'pending');
const progressPercent  = computed(() => {
    if (!session.value) return 0;
    const { solved_places, total_places } = session.value;
    return total_places > 0 ? Math.round((solved_places / total_places) * 100) : 0;
});

const lastUpdated = ref(new Date().toLocaleTimeString());
const refreshAdminDashboard = () => {
    lastUpdated.value = new Date().toLocaleTimeString();
    router.reload({ preserveState: true });
};

const statusBadgeClass = (status) => {
    if (status === 'active') return 'bg-emerald-50 text-emerald-700 border-emerald-100';
    if (status === 'paused') return 'bg-amber-50 text-amber-700 border-amber-100';
    return 'bg-slate-50 text-slate-700 border-slate-100';
};

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

const startSessionForm = useForm({
    city_id: null,
    difficulty: null,
    mode: null,
    start_place_id: null
});
const showConfirmModal = ref(false);
const selectedCityId   = ref(null);
const selectedCityName = ref('');

const selectedCity = computed(() => {
    return cities.value?.find(c => c.id === selectedCityId.value) || null;
});

const confirmStartSession = (cityId) => {
    if (!cityId) return;
    const parsedId = parseInt(cityId);
    const city = cities.value.find(c => c.id === parsedId);
    selectedCityId.value = parsedId;
    selectedCityName.value = city ? city.name : 'cette ville';
    startSessionForm.difficulty = null;
    startSessionForm.mode = null;
    startSessionForm.start_place_id = null;
    showConfirmModal.value = true;
};

const executeStartSession = () => {
    startSessionForm.city_id = selectedCityId.value;
    startSessionForm.start_place_id = null;
    startSessionForm.post(route('player.game-sessions.store'));
};

const startSessionAtPlace = (place) => {
    if (startSessionForm.processing) return;
    startSessionForm.city_id = selectedCityId.value;
    startSessionForm.start_place_id = place.id;
    startSessionForm.post(route('player.game-sessions.store'));
};

const cityIcons = [
    'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
    'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z',
    'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 7',
];
const cityAccentText = ['text-[#E0531C]', 'text-[#F59E0B]', 'text-[#F97316]', 'text-[#EA580C]'];
const cityGlowClasses = [
    'bg-[radial-gradient(circle,_rgba(224,83,28,0.18),_transparent_70%)]',
    'bg-[radial-gradient(circle,_rgba(255,183,0,0.16),_transparent_70%)]',
    'bg-[radial-gradient(circle,_rgba(251,146,60,0.16),_transparent_70%)]',
    'bg-[radial-gradient(circle,_rgba(249,115,22,0.16),_transparent_70%)]',
];
const cityBorderClasses = ['border-[#E0531C]/20', 'border-[#F59E0B]/20', 'border-[#F97316]/20', 'border-[#EA580C]/20'];
const cityTagClasses = [
    'border-[#E0531C]/20 bg-[#FFF1E1] text-[#E0531C]',
    'border-[#F59E0B]/20 bg-[#FFFBEB] text-[#F59E0B]',
    'border-[#F97316]/20 bg-[#FFF4E6] text-[#F97316]',
    'border-[#EA580C]/20 bg-[#FFF2DE] text-[#EA580C]',
];

onMounted(async () => {
    if (user.value.role === 'admin') return;
    await nextTick();

    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
    if (orb1Ref.value) gsap.to(orb1Ref.value, { y: -40, x: 25, duration: 7, repeat: -1, yoyo: true, ease: 'sine.inOut' });
    if (orb2Ref.value) gsap.to(orb2Ref.value, { y: 30, x: -20, duration: 9, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 1.5 });

    tl.fromTo(subtitleRef.value, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.55 })
      .fromTo(greetRef.value, { opacity: 0, y: 32, filter: 'blur(10px)' }, { opacity: 1, y: 0, filter: 'blur(0px)', duration: 0.8 }, '-=0.35')
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

        <div v-if="user.role === 'admin'" class="space-y-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-[#7c4a35]">Tableau de bord</p>
                    <h2 class="text-3xl font-black tracking-tight text-[#2D1B16]">État de la plateforme en direct</h2>
                    <p class="max-w-2xl text-sm font-medium text-[#6f563f]">Surveille les sessions en cours, les joueurs actifs et les alertes de triche en un coup d'œil.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button @click="refreshAdminDashboard" class="inline-flex h-11 items-center justify-center gap-2 rounded-3xl bg-[#E0531C] px-4 text-xs font-black uppercase tracking-[0.16em] text-white shadow-[0_12px_30px_rgba(224,83,28,0.18)] transition hover:-translate-y-0.5 active:scale-95">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5.64 18.36A9 9 0 0112 3.5a9 9 0 018.49 12.71M18.36 18.36A9 9 0 015.64 5.64"/></svg>
                        <span>Rafraîchir</span>
                    </button>
                    <div class="rounded-3xl border border-[#E0531C]/15 bg-[#FFF4E6] px-4 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-[#7c4a35]">Dernière mise à jour : {{ lastUpdated }}</div>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-4">
                <div class="rounded-[28px] border border-[#E0531C]/10 bg-white/95 p-6 shadow-[0_18px_44px_rgba(224,83,28,0.08)]">
                    <h3 class="text-xs font-black uppercase tracking-[0.24em] text-[#7c4a35] mb-3">Sessions en direct</h3>
                    <p class="text-3xl font-black text-[#E0531C]">{{ adminStats?.active_sessions ?? 0 }}</p>
                    <p class="mt-3 text-xs font-bold uppercase tracking-[0.18em] text-[#7c4a35]">Actives, en pause, en attente</p>
                </div>
                <div class="rounded-[28px] border border-[#E0531C]/10 bg-white/95 p-6 shadow-[0_18px_44px_rgba(224,83,28,0.08)]">
                    <h3 class="text-xs font-black uppercase tracking-[0.24em] text-[#7c4a35] mb-3">Joueurs actifs</h3>
                    <p class="text-3xl font-black text-[#0F4C75]">{{ adminStats?.active_players ?? 0 }}</p>
                    <p class="mt-3 text-xs font-bold uppercase tracking-[0.18em] text-[#7c4a35]">Présence en jeu</p>
                </div>
                <div class="rounded-[28px] border border-[#E0531C]/10 bg-white/95 p-6 shadow-[0_18px_44px_rgba(224,83,28,0.08)]">
                    <h3 class="text-xs font-black uppercase tracking-[0.24em] text-[#7c4a35] mb-3">Alertes de triche</h3>
                    <p class="text-3xl font-black text-[#D14343]">{{ adminStats?.suspicious_events ?? 0 }}</p>
                    <p class="mt-3 text-xs font-bold uppercase tracking-[0.18em] text-[#7c4a35]">GPS ou vitesses suspectes</p>
                </div>
                <div class="rounded-[28px] border border-[#E0531C]/10 bg-white/95 p-6 shadow-[0_18px_44px_rgba(224,83,28,0.08)]">
                    <h3 class="text-xs font-black uppercase tracking-[0.24em] text-[#7c4a35] mb-3">Villes actives</h3>
                    <p class="text-3xl font-black text-[#F59E0B]">{{ adminStats?.active_cities ?? 0 }}</p>
                    <p class="mt-3 text-xs font-bold uppercase tracking-[0.18em] text-[#7c4a35]">Parcours en cours</p>
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
                <section class="rounded-[28px] border border-[#E0531C]/10 bg-white/95 p-6 shadow-[0_18px_44px_rgba(224,83,28,0.08)]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-base font-black text-[#2D1B16]">Synthèse des sessions</h3>
                            <p class="mt-2 text-sm text-[#7c4a35]">Répartition par statut avec les chiffres actuels.</p>
                        </div>
                        <span class="rounded-full border border-[#E0531C]/15 bg-[#FFE9D1] px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-[#E0531C]">En temps réel</span>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-3xl border border-[#F0D7BD] bg-[#FFF4E6]/80 p-4">
                            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.18em] text-[#7c4a35]">
                                <span>En attente</span>
                                <span>{{ adminStats?.pending_sessions ?? 0 }}</span>
                            </div>
                            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-[#F4D8C2]">
                                <div class="h-full rounded-full bg-[#FFB700]" :style="{ width: adminStats?.active_sessions ? `${Math.round((adminStats.pending_sessions / adminStats.active_sessions) * 100)}%` : '0%' }"></div>
                            </div>
                        </div>
                        <div class="rounded-3xl border border-[#D6A360] bg-[#FFFAE3]/80 p-4">
                            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.18em] text-[#7c4a35]">
                                <span>Actives</span>
                                <span>{{ adminStats?.active_sessions ?? 0 }}</span>
                            </div>
                            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-[#F4D8C2]">
                                <div class="h-full rounded-full bg-[#E0531C]" :style="{ width: adminStats?.active_sessions ? '100%' : '0%' }"></div>
                            </div>
                        </div>
                        <div class="rounded-3xl border border-[#E29A3D] bg-[#FFF7D4]/80 p-4">
                            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.18em] text-[#7c4a35]">
                                <span>En pause</span>
                                <span>{{ adminStats?.paused_sessions ?? 0 }}</span>
                            </div>
                            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-[#F4D8C2]">
                                <div class="h-full rounded-full bg-[#F97316]" :style="{ width: adminStats?.active_sessions ? `${Math.round((adminStats.paused_sessions / adminStats.active_sessions) * 100)}%` : '0%' }"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-[28px] border border-[#E0531C]/10 bg-white/95 p-6 shadow-[0_18px_44px_rgba(224,83,28,0.08)]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-black text-[#2D1B16]">Sessions prioritaires</h3>
                            <p class="mt-2 text-sm text-[#7c4a35]">Dernières sessions actives et leurs indicateurs clés.</p>
                        </div>
                        <span class="rounded-full border border-[#E0531C]/15 bg-[#FFE9D1] px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-[#E0531C]">Top 4</span>
                    </div>

                    <ul class="mt-6 space-y-3">
                        <li v-for="session in adminStats?.recent_sessions || []" :key="session.id" class="rounded-[24px] border border-[#E0531C]/10 bg-[#FFF7EA] p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-black text-[#2D1B16]">{{ session.city }}</h4>
                                    <p class="mt-1 text-xs text-[#7c4a35]">Host: {{ session.host }} · {{ session.players_count }} joueur{{ session.players_count > 1 ? 's' : '' }}</p>
                                </div>
                                <span :class="statusBadgeClass(session.status) + ' rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em]'">
                                    {{ session.status === 'active' ? 'Actif' : session.status === 'paused' ? 'En pause' : 'En attente' }}
                                </span>
                            </div>
                            <div class="mt-4 grid gap-2 sm:grid-cols-3">
                                <span class="inline-flex items-center gap-2 rounded-2xl border border-[#D9C9B6] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-[#7c4a35]">{{ session.progress }}% achevé</span>
                                <span class="inline-flex items-center gap-2 rounded-2xl border border-[#D9C9B6] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-[#7c4a35]">{{ session.remaining_minutes }} min restantes</span>
                                <span class="inline-flex items-center gap-2 rounded-2xl border border-[#D9C9B6] bg-white px-3 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-[#7c4a35]">Mis à jour {{ session.updated_at }}</span>
                            </div>
                        </li>
                    </ul>

                    <div v-if="!(adminStats?.recent_sessions?.length)" class="rounded-[24px] border border-dashed border-[#E0531C]/20 bg-[#FFF4E6] px-4 py-5 text-center text-sm font-black uppercase tracking-[0.16em] text-[#7c4a35]">
                        Aucune session active à afficher pour le moment.
                    </div>
                </section>
            </div>
        </div>

        <div v-else class="relative overflow-hidden bg-[#FDFBF7] px-4 pb-8 pt-5 sm:px-6">
            <div ref="orb1Ref" class="pointer-events-none fixed top-10 right-0 z-0 h-72 w-72 rounded-full opacity-70 bg-[radial-gradient(circle,_rgba(224,83,28,0.18),_transparent_70%)]"></div>
            <div ref="orb2Ref" class="pointer-events-none fixed bottom-24 left-0 z-0 h-64 w-64 rounded-full opacity-70 bg-[radial-gradient(circle,_rgba(255,183,0,0.16),_transparent_70%)]"></div>

            <div class="relative z-10 space-y-7">
                <div v-if="$page.props.flash?.error" class="mb-5 flex items-center gap-3 rounded-[24px] border border-red-200 bg-red-50/90 px-4 py-3 text-xs font-black uppercase tracking-[0.2em] text-red-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    {{ $page.props.flash.error }}
                </div>

                <div class="space-y-3">
                    <p ref="subtitleRef" class="text-[11px] font-black uppercase tracking-[0.25em] text-[#E0531C]">{{ new Date().getHours() < 12 ? '☀️ Bonjour' : new Date().getHours() < 18 ? '🌤 Bon après-midi' : '🌙 Bonsoir' }}</p>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="max-w-2xl">
                            <h1 ref="greetRef" class="text-[2rem] font-black tracking-tight text-[#2D1B16]">{{ user.name }}<span class="text-[#E0531C]">.</span></h1>
                            <p class="mt-2 text-sm font-medium text-[#7c4a35]">Bienvenue sur ta cité. Tout est prêt pour lancer une nouvelle aventure.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                ref="themeBtnRef"
                                @click="animateToggle"
                                class="inline-flex items-center gap-2 rounded-full border border-[#E0531C]/20 bg-white/95 px-3 py-2 text-[#2D1B16] shadow-[0_12px_30px_rgba(224,83,28,0.14)] transition-all duration-300 hover:-translate-y-0.5 active:scale-95"
                                :title="isDark ? 'Passer en mode clair' : 'Passer en mode sombre'"
                                :aria-label="isDark ? 'Mode clair' : 'Mode sombre'"
                            >
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#FFB700]/20 text-[#E0531C] transition-all duration-300">
                                    <svg v-if="isDark" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                                    <svg v-else class="w-3 h-3 text-[#C47D1C]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a1 1 0 011 1v1a1 1 0 01-2 0V3a1 1 0 011-1zm0 16a1 1 0 011 1v1a1 1 0 01-2 0v-1a1 1 0 011-1zm8-6a1 1 0 010 2h-1a1 1 0 010-2h1zM4 12a1 1 0 010 2H3a1 1 0 010-2h1zm13.66-5.66a1 1 0 010 1.41l-.71.71a1 1 0 01-1.41-1.41l.71-.71a1 1 0 011.41 0zM7.05 16.95a1 1 0 010 1.41l-.71.71a1 1 0 01-1.41-1.41l.71-.71a1 1 0 011.41 0zm9.9 0a1 1 0 011.41 0l.71.71a1 1 0 01-1.41 1.41l-.71-.71a1 1 0 010-1.41zM5.64 7.05a1 1 0 011.41 0l.71.71A1 1 0 016.35 9.17l-.71-.71a1 1 0 010-1.41zM12 7a5 5 0 110 10A5 5 0 0112 7z"/></svg>
                                </span>
                                <span class="text-[10px] uppercase tracking-[0.2em]">{{ isDark ? 'Nuit' : 'Jour' }}</span>
                            </button>

                            <div class="flex h-12 w-12 items-center justify-center rounded-3xl bg-gradient-to-br from-[#E0531C] to-[#FFB700] text-lg font-black text-white shadow-[0_18px_46px_rgba(224,83,28,0.28)]">
                                {{ user.name?.charAt(0).toUpperCase() }}
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="hasActiveSession" ref="sessionCardRef" class="relative overflow-hidden rounded-[32px] border border-[#E0531C]/15 bg-gradient-to-br from-[#FFF4E6] via-[#FFF7EA] to-[#FFFAE3] p-6 shadow-[0_22px_60px_rgba(224,83,28,0.18)]">
                    <div class="absolute inset-0 bg-[radial-gradient(circle,_rgba(224,83,28,0.1),_transparent_60%)]"></div>
                    <div class="relative z-10 space-y-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-2 w-2">
                                    <span :class="isPending ? 'bg-amber-400' : 'bg-green-400'" class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                                    <span :class="isPending ? 'bg-amber-500' : 'bg-green-500'" class="relative inline-flex rounded-full h-2 w-2"></span>
                                </span>
                                <span :class="isPending ? 'text-amber-500' : 'text-green-500'" class="text-[10px] font-black uppercase tracking-[0.2em]">
                                    {{ isPending ? 'En attente de joueurs' : 'Partie en cours' }}
                                </span>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#5b4633]">{{ session.difficulty?.replace('force_', 'Niv. ') }}</span>
                        </div>

                        <div class="space-y-2">
                            <h2 class="text-2xl font-black text-[#2D1B16]">{{ session.city?.name }}</h2>
                            <p v-if="!isPending" class="text-xs font-bold text-[#70462d]">{{ session.solved_places ?? 0 }} / {{ session.total_places ?? 0 }} lieux découverts</p>
                            <p v-else class="text-xs font-bold text-[#70462d]">En attente du lancement par le chef de clan</p>
                        </div>

                        <div v-if="!isPending" class="space-y-4">
                            <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.2em] text-[#7c4a35]">
                                <span>Progression</span>
                                <span class="text-[#E0531C]">{{ progressPercent }}%</span>
                            </div>
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-[#F4D8C2]">
                                <div ref="progressBarRef" class="h-full w-0 rounded-full bg-gradient-to-r from-[#E0531C] via-[#F8872D] to-[#FFB700]"></div>
                            </div>
                        </div>

                        <div ref="statBadgesRef" class="flex flex-wrap gap-2.5">
                            <div class="inline-flex items-center gap-2 rounded-[18px] border border-[#E0531C]/15 bg-[#FFF1E1] px-3 py-2 text-[11px] font-black text-[#C2410C]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ session.available_minutes ?? '—' }} min</span>
                            </div>
                            <div v-if="!isPending" class="inline-flex items-center gap-2 rounded-[18px] border border-[#FBBF24]/20 bg-[#FFF7D4] px-3 py-2 text-[11px] font-black text-[#B45309]">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                <span>{{ session.total_score ?? 0 }} pts</span>
                            </div>
                            <div v-if="session.achievements_count" class="inline-flex items-center gap-2 rounded-[18px] border border-[#8b5cf6]/20 bg-[#F3E8FF] px-3 py-2 text-[11px] font-black text-[#7c3aed]">
                                <span>🏆</span>
                                <span>{{ session.achievements_count }} badge{{ session.achievements_count > 1 ? 's' : '' }}</span>
                            </div>
                        </div>

                        <div ref="resumeBtnRef" class="grid gap-3 sm:grid-cols-2">
                            <Link v-if="isPending" :href="route('game.lobby', session.id)" class="inline-flex h-[52px] w-full items-center justify-center gap-2.5 rounded-3xl bg-gradient-to-r from-[#E0531C] to-[#FFB700] px-4 py-3 text-sm font-black uppercase tracking-[0.22em] text-white shadow-[0_14px_44px_rgba(224,83,28,0.32)] transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                                <span>Rejoindre le salon</span>
                            </Link>
                            <Link v-else-if="session.current_riddle" :href="route('player.riddle.show', session.current_riddle.id)" class="inline-flex h-[52px] w-full items-center justify-center gap-2.5 rounded-3xl bg-gradient-to-r from-[#E0531C] to-[#FFB700] px-4 py-3 text-sm font-black uppercase tracking-[0.22em] text-white shadow-[0_14px_44px_rgba(224,83,28,0.32)] transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                                <span>Reprendre l'aventure</span>
                            </Link>
                            <div v-else class="inline-flex h-[52px] w-full items-center justify-center gap-2 rounded-3xl border border-[#D9C9B6] bg-white text-[#7A5A3C] text-xs font-black uppercase tracking-[0.22em]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                                <span>Aucune énigme disponible</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else ref="noSessionRef" class="relative overflow-hidden rounded-[32px] border border-[#E0531C]/15 bg-[#FFF7EA] p-6 shadow-[0_18px_46px_rgba(224,83,28,0.12)]">
                    <div class="absolute inset-0 bg-[linear-gradient(135deg,_rgba(224,83,28,0.05),_transparent_60%)]"></div>
                    <div class="absolute inset-0 opacity-5 bg-[radial-gradient(circle,_rgba(214,90,49,0.12),_transparent_70%)]"></div>
                    <div class="relative z-10 space-y-5 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[#FFE8D5] border border-[#FBC47A]/30">
                            <svg class="h-8 w-8 text-[#D14343]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 7"/></svg>
                        </div>
                        <h2 class="text-xl font-black text-[#2D1B16]">Prêt à explorer ?</h2>
                        <p class="text-sm font-medium text-[#6f563f] leading-relaxed">Choisis une ville ci-dessous et lance ton aventure urbaine.</p>
                        <div class="relative w-full max-w-xs mx-auto mt-2">
                            <select @change="e => confirmStartSession(e.target.value)" class="w-full cursor-pointer rounded-3xl border border-[#E0531C]/25 bg-white/95 px-4 py-3 text-center text-[11px] font-black uppercase tracking-[0.18em] text-[#2D1B16] outline-none appearance-none transition duration-300 hover:bg-[#fff4d2]">
                                <option value="" disabled selected>Sélectionne une ville</option>
                                <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                            </select>
                            <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[#E0531C]">
                                <svg class="h-4 w-4 animate-bounce-down" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div ref="sectionTitleRef" class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-[0.15em] text-[#2D1B16]">{{ hasActiveSession ? 'Autres villes' : 'Destinations' }}</h3>
                            <p class="text-[10px] font-bold text-[#7c4a35] mt-0.5">{{ cities.length }} ville{{ cities.length > 1 ? 's' : '' }} disponible{{ cities.length > 1 ? 's' : '' }}</p>
                        </div>
                        <div class="flex h-7 w-7 items-center justify-center rounded-xl bg-[#FFE9D1] border border-[#E0531C]/20">
                            <svg class="h-3.5 w-3.5 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>

                    <div class="flex gap-4 overflow-x-auto pb-3 snap-x snap-mandatory scrollbar-none px-1">
                        <button v-for="(city, i) in cities" :key="city.id" :ref="el => setCityCardRef(el, i)" @click="confirmStartSession(city.id)" class="relative snap-start min-w-[220px] shrink-0 overflow-hidden rounded-[28px] border border-[#E0531C]/10 bg-white/90 p-4 text-left shadow-[0_18px_40px_rgba(224,83,28,0.1)] transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#FFF4E6] to-[#FFF9EB]"></div>
                            <div class="absolute inset-0 opacity-80" :class="cityGlowClasses[i % cityGlowClasses.length]"></div>
                            <div class="absolute inset-0 rounded-[28px] border border-transparent transition-colors duration-300 hover:border-[#E0531C]/30"></div>
                            <div class="absolute bottom-0 right-0 h-20 w-20 rounded-full bg-[radial-gradient(circle,_rgba(255,183,0,0.18),_transparent_70%)] opacity-60"></div>
                            <div class="relative z-10 flex min-h-[172px] flex-col justify-between">
                                <div>
                                    <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl transition-transform duration-300 hover:scale-110" :class="[cityGlowClasses[i % cityGlowClasses.length], cityBorderClasses[i % cityBorderClasses.length]]">
                                        <svg class="h-5 w-5" :class="cityAccentText[i % cityAccentText.length]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="cityIcons[i % cityIcons.length]"/></svg>
                                    </div>
                                    <h4 class="text-base font-black leading-tight mb-1 line-clamp-2 text-[#2D1B16]">{{ city.name }}</h4>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] leading-5 text-[#7c4a35] line-clamp-2">{{ city.riddles }} lieu{{ city.riddles > 1 ? 'x' : '' }} · ~{{ city.duration }}</p>
                                </div>
                                <div class="mt-4 flex items-center justify-between gap-3">
                                    <span class="inline-flex items-center rounded-2xl border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.18em]" :class="cityTagClasses[i % cityTagClasses.length]">{{ city.tag }}</span>
                                    <div class="flex h-7 w-7 items-center justify-center rounded-2xl bg-white/90 opacity-0 transition-all duration-200 group-hover:opacity-100 -translate-x-1 group-hover:translate-x-0" :class="cityTagClasses[i % cityTagClasses.length]"><svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></div>
                                </div>
                            </div>
                        </button>
                    </div>

                    <div v-if="!cities.length" class="text-center py-14">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl border border-[#D9C9B6] bg-white/90">
                            <svg class="h-7 w-7 text-[#7c4a35]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 7"/></svg>
                        </div>
                        <p class="text-sm font-bold text-[#7c4a35]">Aucune ville disponible</p>
                    </div>
                </div>
            </div>

            <Teleport to="body">
                <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0 scale-95" leave-active-class="transition-all duration-150" leave-to-class="opacity-0 scale-95">
                    <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-slate-950/70 backdrop-blur-sm">
                        <div class="w-full max-w-md rounded-[32px] border border-white/10 bg-white/95 p-6 shadow-[0_32px_80px_rgba(0,0,0,0.35)]">
                            <div class="mb-4 flex items-center justify-center h-14 w-14 rounded-2xl bg-amber-100 text-[#d65a31] mx-auto">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <h3 class="text-xl font-black text-[#2D1B16] mb-2">Lancer l'aventure à {{ selectedCityName }}</h3>
                            <p class="text-sm text-[#6f563f] mb-5">Choisis un mode, une difficulté, puis sélectionne le lieu de départ.</p>

                            <div v-if="startSessionForm.hasErrors" class="mb-4 rounded-2xl border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                                <p class="font-black uppercase tracking-[0.18em] mb-2">Erreur de lancement</p>
                                <ul class="list-disc pl-4 space-y-1">
                                    <li v-for="(error, field) in startSessionForm.errors" :key="field">{{ error }}</li>
                                </ul>
                            </div>

                            <div class="space-y-4 mb-5">
                                <div class="space-y-2 text-left">
                                    <div class="text-[10px] font-black uppercase tracking-[0.18em] text-[#d65a31]">Mode de jeu</div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button v-for="m in [{ id: 'solo', label: 'Solo' }, { id: 'collectif', label: 'Équipe' }, { id: 'mercenaire', label: 'Rival' }]" :key="m.id" type="button" @click="startSessionForm.mode = m.id" class="rounded-2xl border px-2.5 py-2 text-[10px] font-black uppercase tracking-[0.12em] transition" :class="startSessionForm.mode === m.id ? 'border-[#E0531C] bg-[#E0531C]/10 text-[#2D1B16]' : 'border-[#D9C9B6] bg-white text-[#7c4a35] hover:border-[#E0531C]/30'">{{ m.label }}</button>
                                    </div>
                                </div>

                                <div class="space-y-2 text-left">
                                    <div class="text-[10px] font-black uppercase tracking-[0.18em] text-[#d65a31]">Difficulté</div>
                                    <div class="grid grid-cols-4 gap-2">
                                        <button v-for="d in [{ id: 'enfant', label: 'Enfant' }, { id: 'facile', label: 'Facile' }, { id: 'moyen', label: 'Moyen' }, { id: 'difficile', label: 'Difficile' }]" :key="d.id" type="button" @click="startSessionForm.difficulty = d.id" class="rounded-2xl border px-2.5 py-2 text-[10px] font-black uppercase tracking-[0.12em] transition" :class="startSessionForm.difficulty === d.id ? 'border-[#E0531C] bg-[#E0531C]/10 text-[#2D1B16]' : 'border-[#D9C9B6] bg-white text-[#7c4a35] hover:border-[#E0531C]/30'">{{ d.label }}</button>
                                    </div>
                                </div>
                            </div>

                            <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition-all duration-200 ease-in" leave-to-class="opacity-0 translate-y-4">
                                <div v-if="startSessionForm.mode && startSessionForm.difficulty" class="space-y-4 text-left">
                                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.18em] text-[#d65a31]">
                                        <span>Lieux à explorer</span>
                                        <span>{{ selectedCity?.places?.filter(p => p.riddles.some(r => r.difficulty === startSessionForm.difficulty)).length || 0 }} étapes</span>
                                    </div>
                                    <div v-if="selectedCity?.places?.filter(p => p.riddles.some(r => r.difficulty === startSessionForm.difficulty)).length" class="max-h-[320px] space-y-3 overflow-y-auto pr-1.5">
                                        <button v-for="(place, index) in selectedCity.places.filter(p => p.riddles.some(r => r.difficulty === startSessionForm.difficulty))" :key="place.id" @click="startSessionAtPlace(place)" class="w-full rounded-3xl border border-[#D9C9B6] bg-white px-4 py-3 text-left transition hover:border-[#E0531C]/30 hover:bg-[#FFF4E6]">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-xs font-black uppercase tracking-[0.18em] text-[#2D1B16]">{{ place.name }}</div>
                                                    <p v-if="place.description" class="mt-2 text-[11px] font-medium text-[#6f563f]">{{ place.description }}</p>
                                                </div>
                                                <span class="text-[10px] font-black uppercase tracking-[0.18em] text-[#E0531C]">Étape {{ index + 1 }}</span>
                                            </div>
                                        </button>
                                    </div>
                                    <div v-else class="rounded-3xl border border-[#D9C9B6] bg-white/80 p-4 text-center text-[11px] font-black uppercase tracking-[0.18em] text-[#7c4a35]">Aucun lieu disponible pour ce mode et cette difficulté.</div>
                                </div>
                            </Transition>

                            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                <button @click="showConfirmModal = false" class="h-11 rounded-3xl border border-[#D9C9B6] bg-[#f7f2e7] text-xs font-black uppercase tracking-[0.14em] text-[#7c4a35]">Retour</button>
                                <button @click="executeStartSession" :disabled="startSessionForm.processing || !selectedCity?.places?.length || !startSessionForm.mode || !startSessionForm.difficulty" class="h-11 rounded-3xl bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-xs font-black uppercase tracking-[0.14em] text-white disabled:cursor-not-allowed disabled:opacity-50">{{ startSessionForm.processing ? 'Lancement...' : 'C\'est parti' }}</button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </component>
</template>
