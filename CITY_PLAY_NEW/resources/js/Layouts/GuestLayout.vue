<script setup>
import { Link } from '@inertiajs/vue3';
import PrimeModalManager from '@/Components/PrimeModalManager.vue';
</script>

<template>
    <div class="min-h-screen bg-[#FDFBF7] text-[#2D1B16] font-sans overflow-x-hidden flex flex-col justify-between selection:bg-[#E0531C] selection:text-white relative">
        
        <div class="fixed inset-0 z-0 pointer-events-none">
            <div class="absolute top-[-20%] right-[-20%] w-[100vw] h-[100vw] rounded-full bg-[#FFB700]/15 blur-[80px] animate-float"></div>
            <div class="absolute bottom-[-20%] left-[-20%] w-[100vw] h-[100vw] rounded-full bg-[#00A859]/10 blur-[100px] animate-float" style="animation-delay: -3s;"></div>
            <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle, #E0531C 1.5px, transparent 1.5px); background-size: 32px 32px;"></div>
        </div>

        <div class="relative z-10 flex-1 flex flex-col justify-between px-6 py-6 w-full max-w-md mx-auto">
            
            <header class="w-full flex justify-between items-center mb-8 animate-fade-in">
                <Link href="/" class="flex items-center gap-2 group">
                    <div class="w-9 h-9 bg-gradient-to-br from-[#E0531C] to-[#FFB700] rounded-xl flex items-center justify-center shadow-md shadow-orange-500/10 group-hover:scale-105 transition-transform">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </div>
                    <span class="text-xs font-black uppercase tracking-widest text-[#5C4033]/80 group-hover:text-[#E0531C] transition-colors">Accueil</span>
                </Link>
                
                <span class="text-lg font-black italic tracking-tighter uppercase">CityPlay<span class="text-[#E0531C]">.</span></span>
            </header>

            <main class="w-full bg-white/70 backdrop-blur-md p-6 rounded-3xl border border-orange-100/60 shadow-[0_20px_40px_rgba(62,39,32,0.04)] animate-slide-up">
                <slot />
            </main>

            <footer class="text-center pt-8 text-[9px] font-black text-[#5C4033]/30 uppercase tracking-[0.15em]">
                Identité Culturelle & Exploration du Bénin
            </footer>
        </div>

        <PrimeModalManager />
    </div>
</template>

<style scoped>
/* Flottaison douce des orbes de lumière */
@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(15px, -15px) scale(1.03); }
}
.animate-float { animation: float 12s ease-in-out infinite; }

/* Transitions d'entrée */
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
.animate-slide-up { animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1); }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>