<script setup>
import { computed } from 'vue';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const session = computed(() => page.props.session);
const cities = computed(() => page.props.cities);
const adminStats = computed(() => page.props.adminStats);

// Détermine quel layout utiliser selon le rôle
const currentLayout = computed(() => {
    return user.value.role === 'admin' ? AdminLayout : PlayerLayout;
});

const startSessionForm = useForm({
    city_id: null,
});

const startSession = (cityId) => {
    if (confirm('Voulez-vous démarrer une nouvelle aventure dans cette ville ?')) {
        startSessionForm.city_id = cityId;
        startSessionForm.post(route('game-sessions.store'));
    }
};
</script>

<template>
    <Head title="Tableau de bord" />

    <component :is="currentLayout">
        <template #header>
            <span v-if="user.role === 'admin'">Administration</span>
            <span v-else>Accueil</span>
        </template>

        <!-- Vue ADMIN -->
        <div v-if="user.role === 'admin'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-bold uppercase mb-2">Sessions Actives</h3>
                    <p class="text-3xl font-black text-[#d65a31]">{{ adminStats.active_sessions }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-bold uppercase mb-2">Joueurs Connectés</h3>
                    <p class="text-3xl font-black text-blue-600">{{ adminStats.total_players }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-bold uppercase mb-2">Alertes Triche</h3>
                    <p class="text-3xl font-black text-red-500">{{ adminStats.suspicious_logs }}</p>
                </div>
            </div>
        </div>

        <!-- Vue JOUEUR -->
        <div v-else class="max-w-md mx-auto space-y-8 pb-20">
            <!-- Greeting -->
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h1 class="text-2xl font-black text-white">Bonjour, {{ user.name }} 👋</h1>
                    <p class="text-gray-500 text-sm font-bold">{{ session ? session.city?.name + ' t\'attend' : 'Quelle ville vas-tu explorer ?' }}</p>
                </div>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.error" class="bg-red-500/20 border border-red-500/50 p-4 rounded-2xl text-red-500 text-xs font-bold uppercase text-center">
                {{ $page.props.flash.error }}
            </div>

            <!-- Active Session Card -->
            <div v-if="session" class="bg-[#1c2128] rounded-3xl p-6 border border-white/5 shadow-2xl">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Partie en cours</span>
                </div>
                
                <h2 class="text-xl font-black text-white mb-1">{{ session.city?.name }} Mystère</h2>
                <p class="text-xs font-bold text-gray-500 mb-4">{{ session.solved_places }} / {{ session.total_places }} lieux découverts</p>
                
                <!-- Progress Bar -->
                <div class="h-1.5 w-full bg-white/5 rounded-full overflow-hidden mb-6">
                    <div class="h-full bg-[#d65a31] transition-all duration-1000" :style="{ width: (session.solved_places / session.total_places * 100) + '%' }"></div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center bg-[#d65a31]/10 text-[#d65a31] px-3 py-1.5 rounded-lg border border-[#d65a31]/20">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-xs font-black">{{ session.available_minutes }} min</span>
                    </div>
                    <div class="flex items-center bg-green-500/10 text-green-500 px-3 py-1.5 rounded-lg border border-green-500/20">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                        <span class="text-xs font-black">{{ session.total_score || 0 }} pts</span>
                    </div>
                </div>

                <Link 
                    v-if="session.current_riddle"
                    :href="route('riddle.show', session.current_riddle.id)"
                    class="mt-6 w-full flex items-center justify-center bg-white text-black h-12 rounded-xl font-black uppercase tracking-widest hover:bg-gray-200 transition"
                >
                    Reprendre
                </Link>
                <div v-else-if="session.status === 'active'" class="mt-6 text-center">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Aucune énigme disponible pour ce lieu</span>
                </div>
            </div>

            <!-- Cities Grid -->
            <div>
                <h3 class="text-lg font-black text-white mb-4 uppercase tracking-tighter">Villes du Bénin</h3>
                <div class="grid grid-cols-2 gap-4">
                    <button v-for="city in cities" :key="city.id" @click="startSession(city.id)" class="bg-[#1c2128] rounded-3xl p-4 border border-white/5 flex flex-col h-48 overflow-hidden relative text-left transition hover:border-[#d65a31]/50 group">
                        <!-- Background Pattern/Color -->
                        <div :class="[city.color, 'absolute inset-0 opacity-20 group-hover:opacity-30 transition']"></div>
                        
                        <div class="relative z-10 flex-1">
                            <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center mb-4 group-hover:bg-[#d65a31]/20 transition">
                                <svg class="w-6 h-6 text-gray-400 group-hover:text-[#d65a31] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="city.icon" /></svg>
                            </div>
                            <h4 class="text-lg font-black text-white">{{ city.name }}</h4>
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ city.riddles }} lieux · ~{{ city.duration }}</p>
                        </div>

                        <div class="relative z-10">
                            <span class="inline-block px-2 py-0.5 rounded-md bg-[#d65a31] text-[9px] font-black text-white uppercase tracking-widest">
                                {{ city.tag }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </component>
</template>
