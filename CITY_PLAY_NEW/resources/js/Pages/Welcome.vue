<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Bienvenue sur CITYPLAY" />

    <div class="min-h-screen bg-slate-950 text-slate-200 font-sans selection:bg-cyan-500 selection:text-white overflow-x-hidden">
        <!-- Hero Section -->
        <div class="relative min-h-screen flex flex-col items-center justify-center p-6 text-center overflow-hidden">
            <!-- Urban Background Effect -->
            <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(79,70,229,0.15),transparent_70%)]"></div>
                <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?q=80&w=2000&auto=format&fit=crop')] bg-cover bg-center opacity-10 grayscale"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-slate-950/50 via-slate-950 to-slate-950"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 max-w-4xl w-full space-y-8">
                <!-- Logo -->
                <div class="flex justify-center mb-8 animate-bounce-slow">
                    <div class="h-24 w-24 bg-gradient-to-tr from-indigo-600 to-cyan-500 rounded-3xl flex items-center justify-center shadow-2xl shadow-cyan-500/40 border-4 border-slate-900">
                        <span class="text-white font-black text-4xl tracking-tighter">CP</span>
                    </div>
                </div>

                <h1 class="text-5xl md:text-7xl font-black text-white leading-tight tracking-tighter uppercase italic">
                    La ville est <br/> 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">ton terrain de jeu</span>
                </h1>

                <p class="text-lg md:text-xl text-slate-400 max-w-2xl mx-auto font-medium">
                    CITYPLAY transforme ta ville en un Escape Game géant. Résous des énigmes, explore des lieux secrets et vis une aventure urbaine inédite.
                </p>

                <!-- CTAs -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-8">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-indigo-600 to-cyan-600 rounded-2xl text-white font-bold text-lg shadow-xl shadow-cyan-500/20 hover:scale-105 transition-transform active:scale-95"
                        >
                            Accéder au Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-indigo-600 to-cyan-600 rounded-2xl text-white font-bold text-lg shadow-xl shadow-cyan-500/20 hover:scale-105 transition-transform active:scale-95"
                        >
                            Commencer l'aventure
                        </Link>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="w-full sm:w-auto px-8 py-4 bg-slate-900 border border-slate-700 rounded-2xl text-white font-bold text-lg hover:bg-slate-800 transition-colors active:scale-95"
                        >
                            Se connecter
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div class="absolute bottom-10 left-1/2 -translate-x-1/2 animate-bounce opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                </svg>
            </div>
        </div>

        <!-- Features Section -->
        <section class="max-w-7xl mx-auto px-6 py-24 space-y-24">
            <div class="grid md:grid-cols-3 gap-12">
                <div class="space-y-4 text-center p-8 bg-slate-900/50 rounded-3xl border border-slate-800 hover:border-indigo-500/50 transition-colors">
                    <div class="text-4xl mb-4">🧩</div>
                    <h3 class="text-xl font-bold text-white uppercase tracking-tight">Énigmes Immersives</h3>
                    <p class="text-slate-400">Des défis conçus pour tester ton esprit logique et ton sens de l'observation dans l'environnement urbain.</p>
                </div>
                <div class="space-y-4 text-center p-8 bg-slate-900/50 rounded-3xl border border-slate-800 hover:border-cyan-500/50 transition-colors">
                    <div class="text-4xl mb-4">📍</div>
                    <h3 class="text-xl font-bold text-white uppercase tracking-tight">Exploration Secrète</h3>
                    <p class="text-slate-400">Découvre des recoins cachés et l'histoire méconnue de ta ville comme tu ne l'as jamais vue.</p>
                </div>
                <div class="space-y-4 text-center p-8 bg-slate-900/50 rounded-3xl border border-slate-800 hover:border-orange-500/50 transition-colors">
                    <div class="text-4xl mb-4">🏆</div>
                    <h3 class="text-xl font-bold text-white uppercase tracking-tight">Compétition Live</h3>
                    <p class="text-slate-400">Grimpe dans le classement, gagne des badges et deviens une légende urbaine de CITYPLAY.</p>
                </div>
            </div>

            <!-- Call to action bottom -->
            <div class="bg-gradient-to-r from-indigo-900/20 to-cyan-900/20 rounded-[3rem] p-12 text-center border border-slate-800 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_50%_50%,rgba(34,211,238,0.1),transparent_70%)]"></div>
                <div class="relative z-10 space-y-6">
                    <h2 class="text-3xl md:text-5xl font-black text-white uppercase tracking-tighter italic">Prêt à relever le défi ?</h2>
                    <p class="text-slate-400 max-w-xl mx-auto">Rejoins des milliers de joueurs et commence ta première session de jeu dès maintenant.</p>
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('register')"
                        class="inline-block px-10 py-5 bg-white text-slate-950 font-black uppercase tracking-widest rounded-2xl hover:scale-105 transition-transform active:scale-95 shadow-2xl"
                    >
                        Créer mon compte
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-900 py-12 px-6 text-center text-slate-600 text-sm">
            <p>&copy; 2026 CITYPLAY. Développé pour les explorateurs urbains.</p>
        </footer>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap');

.font-sans {
    font-family: 'Space Grotesk', sans-serif;
}

@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-bounce-slow {
    animation: bounce-slow 3s infinite ease-in-out;
}
</style>
