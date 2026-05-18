<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import NotificationDescartes from '@/Components/NotificationDescartes.vue';
import { useTheme } from '@/composables/useTheme';

const page = usePage();
const user = computed(() => page.props.auth.user);
const session = computed(() => page.props.session || null);
const hasActiveSession = computed(() => session.value?.status === 'active');

// Initialise le thème dès le montage du layout (appliqué sur toutes les pages)
const { isDark } = useTheme();
</script>

<template>
    <div class="cp-layout min-h-screen font-sans" style="transition: background 0.35s ease, color 0.35s ease;">

        <!-- ── Fond ambiant (orbs + grain) ── -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute -top-40 -left-40 w-[700px] h-[700px] rounded-full"
                 style="background: radial-gradient(circle, var(--orb-orange) 0%, transparent 65%); transition: background 0.35s;"></div>
            <div class="absolute top-1/3 -right-32 w-[500px] h-[500px] rounded-full"
                 style="background: radial-gradient(circle, var(--orb-indigo) 0%, transparent 65%); transition: background 0.35s;"></div>
            <div class="absolute -bottom-32 left-1/4 w-[500px] h-[500px] rounded-full"
                 style="background: radial-gradient(circle, var(--orb-violet) 0%, transparent 65%); transition: background 0.35s;"></div>
            <div class="absolute inset-0 opacity-[0.03]"
                 style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E'); background-size: 200px 200px;"></div>
        </div>

        <NotificationDescartes />

        <!-- ── TOP BAR ── -->
        <header class="cp-header sticky top-0 z-40 backdrop-blur-xl" style="border-bottom: 1px solid var(--border-subtle);">
            <div class="max-w-2xl mx-auto px-4 h-14 flex items-center justify-between">
                <!-- Logo -->
                <Link :href="route('player.dashboard')" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#d65a31] flex items-center justify-center shadow-lg shadow-[#d65a31]/30">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                    </div>
                    <span class="font-black text-base uppercase tracking-tighter cp-text-primary">
                        City<span class="text-[#d65a31]">Play</span>
                    </span>
                </Link>

                <!-- Droite -->
                <div class="flex items-center gap-3">
                    <div v-if="hasActiveSession"
                         class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full"
                         style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2);">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                        </span>
                        <span class="text-[10px] font-black text-green-500 uppercase tracking-widest">En jeu</span>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" aria-label="Déconnexion"
                          class="cp-icon-btn relative w-8 h-8 rounded-xl flex items-center justify-center transition text-[#d65a31] border border-[#d65a31]/20 hover:bg-[#d65a31]/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"/>
                        </svg>
                    </Link>
                    <button class="cp-icon-btn relative w-8 h-8 rounded-xl flex items-center justify-center transition">
                        <svg class="w-4 h-4 cp-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-[#d65a31] rounded-full"></span>
                    </button>
                </div>
            </div>
        </header>

        <!-- ── CONTENU ── -->
        <main class="relative z-10 max-w-2xl mx-auto px-4 py-5 pb-28">
            <slot />
        </main>

        <!-- ── BOTTOM NAV ── -->
        <nav class="cp-nav fixed bottom-0 inset-x-0 z-40">
            <div class="max-w-2xl mx-auto px-6 h-[68px] flex items-center justify-around">

                <Link :href="route('player.dashboard')"
                      :class="route().current('player.dashboard') ? 'text-[#d65a31]' : 'cp-nav-item'"
                      class="flex flex-col items-center gap-1 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Accueil</span>
                </Link>

                <div class="relative -mt-5">
                    <Link v-if="hasActiveSession && session?.current_riddle"
                          :href="route('player.riddle.show', session.current_riddle.id)"
                          class="flex flex-col items-center gap-1 group">
                        <div class="w-14 h-14 rounded-2xl bg-[#d65a31] flex items-center justify-center shadow-xl shadow-[#d65a31]/40 group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-widest text-[#d65a31]">Jouer</span>
                    </Link>
                    <div v-else class="flex flex-col items-center gap-1 opacity-25 cursor-not-allowed">
                        <div class="w-14 h-14 rounded-2xl cp-icon-btn flex items-center justify-center">
                            <svg class="w-6 h-6 cp-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-widest cp-text-muted">Jouer</span>
                    </div>
                </div>

                <Link :href="route('player.game.map')"
                      :class="route().current('player.game.map') ? 'text-[#d65a31]' : 'cp-nav-item'"
                      class="flex flex-col items-center gap-1 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7m0 0L9 7"/>
                    </svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Carte</span>
                </Link>

                <Link :href="route('player.profile.edit')"
                      :class="route().current('player.profile.edit') ? 'text-[#d65a31]' : 'cp-nav-item'"
                      class="flex flex-col items-center gap-1 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Profil</span>
                </Link>

            </div>
        </nav>
    </div>
</template>

<style>
/* ── Layout root ── */
.cp-layout {
    background-color: var(--bg-base);
    color: var(--text-primary);
}

/* ── Header ── */
.cp-header {
    background: var(--bg-header);
}

/* ── Bottom nav ── */
.cp-nav {
    background: var(--bg-nav);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border-top: 1px solid var(--border-subtle);
    padding-bottom: env(safe-area-inset-bottom);
}

/* ── Textes ── */
.cp-text-primary   { color: var(--text-primary); }
.cp-text-secondary { color: var(--text-secondary); }
.cp-text-muted     { color: var(--text-muted); }

/* ── Nav items ── */
.cp-nav-item { color: var(--text-muted); }
.cp-nav-item:hover { color: var(--text-secondary); }

/* ── Icon buttons ── */
.cp-icon-btn {
    background: var(--input-bg);
    border: 1px solid var(--border-subtle);
}
.cp-icon-btn:hover { background: var(--bg-surface); }

/* ── Ping ── */
@keyframes ping {
    75%, 100% { transform: scale(2); opacity: 0; }
}
.animate-ping { animation: ping 1.2s cubic-bezier(0, 0, 0.2, 1) infinite; }
</style>
