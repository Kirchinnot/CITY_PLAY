<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="CITYPLAY - Découvrez votre ville autrement" />

    <div class="min-h-screen bg-cityplay-dark text-white selection:bg-cityplay-primary selection:text-white font-sans overflow-hidden">
        
        <!-- Background Elements -->
        <div class="fixed inset-0 z-0">
            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-cityplay-dark/80 to-cityplay-dark z-10"></div>
            <!-- Pattern Map -->
            <div class="absolute inset-0 opacity-[0.03] z-0" style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 32px 32px;"></div>
            <!-- Glow Orbs -->
            <div class="absolute top-[-10%] right-[-5%] w-[40vw] h-[40vw] rounded-full bg-cityplay-primary/20 blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-[-10%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-cityplay-purple/20 blur-[150px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Content -->
        <div class="relative z-20 min-h-screen flex flex-col">
            <!-- Header Navbar -->
            <header class="w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-cityplay-primary rounded-xl flex items-center justify-center shadow-[0_0_20px_rgba(214,90,49,0.4)]">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-black italic tracking-tighter">CITYPLAY<span class="text-cityplay-primary">.</span></span>
                </div>

                <nav v-if="canLogin" class="flex gap-4 items-center">
                    <Link v-if="$page.props.auth.user" :href="route('player.dashboard')" class="text-xs sm:text-sm font-bold uppercase tracking-widest hover:text-cityplay-primary transition">
                        Tableau de bord
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="hidden sm:block text-xs sm:text-sm font-bold uppercase tracking-widest text-gray-400 hover:text-white transition px-4 py-2">
                            Connexion
                        </Link>
                        <Link v-if="canRegister" :href="route('register')" class="text-xs sm:text-sm font-bold uppercase tracking-widest bg-white/10 border border-white/20 hover:bg-white/20 transition px-6 py-2 rounded-full">
                            Jouer
                        </Link>
                    </template>
                </nav>
            </header>

            <!-- Hero Section -->
            <main class="flex-1 flex flex-col items-center justify-center text-center px-6 mt-[-5vh]">
                <div class="inline-block px-4 py-1.5 rounded-full bg-cityplay-primary/10 border border-cityplay-primary/30 text-cityplay-primary text-[10px] font-black uppercase tracking-[0.2em] mb-6 shadow-[0_0_15px_rgba(214,90,49,0.2)]">
                    🌍 L'Aventure Urbaine
                </div>
                
                <h1 class="text-5xl md:text-7xl font-black tracking-tighter mb-6 leading-[1.1]">
                    Redécouvrez la ville<br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-cityplay-primary to-cityplay-yellow italic">Énigme par Énigme</span>
                </h1>
                
                <p class="max-w-2xl mx-auto text-gray-400 text-lg md:text-xl font-medium leading-relaxed mb-10">
                    Cityplay transforme votre environnement urbain en un immense terrain de jeu interactif. Résolvez des mystères, trouvez des lieux cachés et plongez dans l'histoire et la culture locales.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center w-full max-w-md mx-auto">
                    <Link v-if="$page.props.auth.user" :href="route('player.dashboard')" class="group relative w-full flex items-center justify-center gap-3 h-14 bg-cityplay-primary rounded-2xl font-black uppercase tracking-widest text-sm hover:bg-cityplay-primary-hover transition shadow-[0_10px_30px_rgba(214,90,49,0.3)] overflow-hidden">
                        <span class="relative z-10">Reprendre l'Aventure</span>
                        <svg class="relative z-10 w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </Link>
                    <template v-else>
                        <Link v-if="canRegister" :href="route('register')" class="group relative w-full flex items-center justify-center gap-3 h-14 bg-cityplay-primary rounded-2xl font-black uppercase tracking-widest text-sm transition shadow-[0_10px_30px_rgba(214,90,49,0.3)] overflow-hidden">
                            <span class="relative z-10">Commencer</span>
                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                        </Link>
                        <Link :href="route('login')" class="w-full flex items-center justify-center h-14 bg-white/5 border border-white/10 rounded-2xl font-black uppercase tracking-widest text-sm hover:bg-white/10 transition">
                            Se Connecter
                        </Link>
                    </template>
                </div>
            </main>

            <!-- Features -->
            <div class="w-full max-w-6xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-cityplay-dark-card border border-white/5 p-6 rounded-3xl shadow-xl hover:border-cityplay-blue/30 transition duration-300">
                    <div class="w-10 h-10 bg-cityplay-blue/10 rounded-xl flex items-center justify-center mb-4 border border-cityplay-blue/20">
                        <span class="text-xl">📍</span>
                    </div>
                    <h3 class="font-black text-lg mb-2 text-white">Géolocalisation</h3>
                    <p class="text-sm text-gray-400 font-medium leading-relaxed">Déplacez-vous physiquement pour valider vos objectifs et découvrir les secrets de la carte.</p>
                </div>
                <div class="bg-cityplay-dark-card border border-white/5 p-6 rounded-3xl shadow-xl hover:border-cityplay-purple/30 transition duration-300">
                    <div class="w-10 h-10 bg-cityplay-purple/10 rounded-xl flex items-center justify-center mb-4 border border-cityplay-purple/20">
                        <span class="text-xl">🧩</span>
                    </div>
                    <h3 class="font-black text-lg mb-2 text-white">Énigmes Dynamiques</h3>
                    <p class="text-sm text-gray-400 font-medium leading-relaxed">Des défis adaptés à tous les niveaux de complexité. Demandez des indices si vous êtes coincé.</p>
                </div>
                <div class="bg-cityplay-dark-card border border-white/5 p-6 rounded-3xl shadow-xl hover:border-cityplay-green/30 transition duration-300">
                    <div class="w-10 h-10 bg-cityplay-green/10 rounded-xl flex items-center justify-center mb-4 border border-cityplay-green/20">
                        <span class="text-xl">🤝</span>
                    </div>
                    <h3 class="font-black text-lg mb-2 text-white">Jeu en Équipe</h3>
                    <p class="text-sm text-gray-400 font-medium leading-relaxed">Collaborez jusqu'à 10 joueurs pour percer tous les mystères de votre environnement.</p>
                </div>
            </div>
            
            <footer class="text-center py-6 text-[10px] font-black text-gray-600 uppercase tracking-[0.2em]">
                © {{ new Date().getFullYear() }} CITYPLAY. Tous droits réservés.
            </footer>
        </div>
    </div>
</template>
