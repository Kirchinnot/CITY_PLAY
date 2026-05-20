<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

// Icônes SVG Professionnelles
const icons = {
    history: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`,
    puzzle: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.7 2c.4 0 .8.3.8.7v1.8c2.4.4 4.3 2.3 4.7 4.7h1.8c.4 0 .7.4.7.8s-.3.8-.7.8h-1.8c-.4 2.4-2.3 4.3-4.7 4.7v1.8c0 .4-.3.7-.7.7s-.8-.3-.8-.7v-1.8c-2.4-.4-4.3-2.3-4.7-4.7H3.8c-.4 0-.7-.4-.7-.8s.3-.8.7-.8h1.8c.4-2.4 2.3-4.3 4.7-4.7V2.7c0-.4.3-.7.7-.7z"/></svg>`,
    community: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`
};
</script>

<template>
    <Head title="CITYPLAY - L'aventure vous attend" />

    <div class="min-h-screen bg-[#FDFBF7] text-[#2D1B16] font-sans overflow-x-hidden flex flex-col selection:bg-[#E0531C] selection:text-white">
        
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute top-[-10%] right-[-15%] w-[85vw] h-[85vw] rounded-full bg-[#FFB700]/20 blur-[80px] animate-float-slow"></div>
            <div class="absolute bottom-[-10%] left-[-15%] w-[90vw] h-[90vw] rounded-full bg-[#00A859]/15 blur-[100px] animate-float-slow-delayed"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, #E0531C 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
        </div>

        <div class="relative z-20 flex-1 flex flex-col">
            
            <header class="w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 bg-gradient-to-br from-[#E0531C] to-[#FFB700] rounded-xl flex items-center justify-center shadow-xl shadow-orange-500/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                    </div>
                    <span class="text-xl font-black italic tracking-tighter uppercase">CityPlay<span class="text-[#E0531C]">.</span></span>
                </div>

                <nav v-if="canLogin">
                    <Link v-if="$page.props.auth.user" :href="route('player.dashboard')" class="text-xs font-bold text-[#E0531C] bg-orange-50 px-4 py-2 rounded-full border border-orange-100 uppercase tracking-widest">
                        Tableau de bord
                    </Link>
                    <Link v-else :href="route('login')" class="text-xs font-black text-[#5C4033] bg-white border-2 border-[#5C4033]/10 px-5 py-2.5 rounded-full hover:border-[#E0531C] transition-all uppercase tracking-widest">
                        Se connecter
                    </Link>
                </nav>
            </header>

            <main class="flex-1 flex flex-col items-center justify-center text-center px-6 pt-4 pb-10">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#FFB700]/15 border border-[#FFB700]/30 text-[#B37D00] text-[10px] font-black uppercase tracking-[0.15em] mb-6 animate-fade-in">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#FFB700] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#FFB700]"></span>
                    </span>
                    CityPlay : Découverte du Bénin
                </div>
                
                <h1 class="text-[2.6rem] leading-[1] font-black tracking-tight mb-4 text-[#2D1B16] animate-slide-up">
                    L'histoire et la culture <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#E0531C] via-[#FFB700] to-[#00A859] italic font-black">
                        Au cœur du jeu
                    </span>
                </h1>
                
                <p class="max-w-md mx-auto text-[#5C4033] text-base font-medium leading-relaxed mb-10 px-4 opacity-90 animate-slide-up-delayed">
                    Explorez les secrets du Bénin, résolvez des énigmes et vivez une aventure culturelle unique sur smartphone.
                </p>

                <div class="w-full max-w-sm px-2 animate-slide-up-delayed-2">
                    <Link v-if="$page.props.auth.user" :href="route('player.dashboard')" class="btn-primary-pulse w-full h-16 flex items-center justify-center gap-3 bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white rounded-2xl text-sm font-black uppercase tracking-widest shadow-2xl">
                        Reprendre l'aventure
                    </Link>
                    
                    <Link v-else :href="route('register')" class="btn-primary-pulse w-full h-16 flex items-center justify-center gap-3 bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white rounded-2xl text-sm font-black uppercase tracking-widest shadow-2xl overflow-hidden group">
                        <div class="absolute inset-0 w-full h-full bg-white/20 -translate-x-full group-hover:animate-shine"></div>
                        <span class="relative z-10">Commencer l'exploration</span>
                        <svg class="relative z-10 w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </Link>
                </div>
            </main>

            <div class="w-full pb-10 overflow-hidden animate-fade-in">
                <div class="carousel-track flex gap-4 px-6">
                    <div class="carousel-card shrink-0 w-[280px] bg-white p-5 rounded-3xl border border-orange-50 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 bg-orange-50 text-[#E0531C] rounded-xl flex items-center justify-center" v-html="icons.history"></div>
                        <div>
                            <h3 class="text-sm font-black uppercase">Patrimoine</h3>
                            <p class="text-[11px] text-[#5C4033] font-medium leading-tight mt-0.5">Explorez les palais royaux et les lieux sacrés.</p>
                        </div>
                    </div>
                    <div class="carousel-card shrink-0 w-[280px] bg-white p-5 rounded-3xl border border-orange-50 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 bg-yellow-50 text-[#B37D00] rounded-xl flex items-center justify-center" v-html="icons.puzzle"></div>
                        <div>
                            <h3 class="text-sm font-black uppercase">Mystères</h3>
                            <p class="text-[11px] text-[#5C4033] font-medium leading-tight mt-0.5">Résolvez des énigmes inspirées des traditions.</p>
                        </div>
                    </div>
                    <div class="carousel-card shrink-0 w-[280px] bg-white p-5 rounded-3xl border border-orange-50 shadow-sm flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-50 text-[#00A859] rounded-xl flex items-center justify-center" v-html="icons.community"></div>
                        <div>
                            <h3 class="text-sm font-black uppercase">Clan</h3>
                            <p class="text-[11px] text-[#5C4033] font-medium leading-tight mt-0.5">Partagez l'aventure avec vos amis en équipe.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <footer class="text-center py-6 text-[9px] font-black text-[#5C4033]/30 uppercase tracking-[0.2em]">
                © {{ new Date().getFullYear() }} CityPlay. Identité Culturelle du Bénin.
            </footer>
        </div>
    </div>
</template>

<style scoped>
/* Animations Fond */
@keyframes float {
    0%, 100% { transform: translate(0,0) scale(1); opacity: 0.6; }
    50% { transform: translate(20px, -20px) scale(1.1); opacity: 0.8; }
}
.animate-float-slow { animation: float 10s ease-in-out infinite; }
.animate-float-slow-delayed { animation: float 12s ease-in-out infinite -2s; }

/* Entrée progressive */
.animate-fade-in { animation: fadeIn 0.8s ease-out; }
.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.animate-slide-up-delayed { opacity: 0; animation: slideUp 0.6s 0.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.animate-slide-up-delayed-2 { opacity: 0; animation: slideUp 0.6s 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

/* Pulsation Bouton Principal */
.btn-primary-pulse {
    animation: pulseGlow 3s infinite;
    position: relative;
    z-index: 10;
}
@keyframes pulseGlow {
    0%, 100% { box-shadow: 0 10px 30px rgba(224, 83, 28, 0.3); transform: scale(1); }
    50% { box-shadow: 0 15px 45px rgba(224, 83, 28, 0.5); transform: scale(1.02); }
}

/* Effet Brillance au survol */
@keyframes shine {
    100% { transform: translateX(100%); }
}
.animate-shine { animation: shine 0.8s ease-out forwards; }

/* Carrousel Auto-Scroll */
.carousel-track {
    display: flex;
    animation: scrollCarousel 20s linear infinite;
    width: max-content;
}

@keyframes scrollCarousel {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

/* Pause au toucher mobile */
.carousel-track:active {
    animation-play-state: paused;
}
</style>