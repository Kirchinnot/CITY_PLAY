<script setup>
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import QRCodeDisplay from '@/Components/QRCodeDisplay.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, nextTick, computed } from 'vue';
import { gsap } from 'gsap';

const props = defineProps({
    session: Object,
    currentUser: Object,
    invitationUrl: String
});

const qrCodeUrl = computed(() => {
    if (props.invitationUrl) return props.invitationUrl;
    if (props.session?.invitation?.token) {
        return window.route('game.join', { token: props.session.invitation.token });
    }
    return '';
});

const players = ref([...(props.session.players || [])]);
const showQRModal = ref(false);
const showSettings = ref(false);

const startForm = useForm({
    mode: props.session.mode,
    difficulty: props.session.difficulty,
    locomotion: props.session.locomotion,
    available_minutes: props.session.available_minutes,
    team_size: props.session.team_size || 1,
});

const startSession = () => {
    startForm.post(window.route('game.start', props.session.id));
};

const isHost = props.currentUser.id === props.session.host_user_id;

// ── Refs pour GSAP ───────────────────────────────────────────────────────────
const orb1Ref = ref(null);
const orb2Ref = ref(null);
const playersListRef = ref(null);

onMounted(() => {
    // 1. Animations de fond (Orbes)
    if (orb1Ref.value) gsap.to(orb1Ref.value, { y: -30, x: 20, duration: 6, repeat: -1, yoyo: true, ease: 'sine.inOut' });
    if (orb2Ref.value) gsap.to(orb2Ref.value, { y: 25, x: -15, duration: 8, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 1 });

    // 2. Écoute Pusher
    if (window.Echo) {
        window.Echo.join(`session.${props.session.id}`)
            .here((users) => {
                console.log('Joueurs présents:', users);
            })
            .joining((user) => {
                if (!players.value.find(p => p.id === user.id)) {
                    players.value.push(user);
                    animateNewPlayer();
                }
            })
            .leaving((user) => {})
            .listen('PlayerJoined', (e) => {
                if (!players.value.find(p => p.id === e.user.id)) {
                    players.value.push(e.user);
                    animateNewPlayer();
                }
            });
    }
        
    // Polling basique si Pusher n'est pas configuré pour rediriger les invités quand le host lance la partie
    if (!isHost) {
        const pollInterval = setInterval(() => {
            router.reload({ 
                only: ['session'], 
                preserveScroll: true, 
                onSuccess: (page) => {
                    if (page.props.session.status === 'active') {
                        clearInterval(pollInterval);
                        window.location.href = window.route('player.game.map');
                    }
                }
            });
        }, 5000);
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leave(`session.${props.session.id}`);
    }
});

const animateNewPlayer = () => {
    nextTick(() => {
        if (playersListRef.value && playersListRef.value.lastElementChild) {
            gsap.from(playersListRef.value.lastElementChild, {
                opacity: 0,
                y: 20,
                scale: 0.8,
                duration: 0.5,
                ease: "back.out(1.5)"
            });
            // Haptic feedback
            if ('vibrate' in navigator) navigator.vibrate([50, 50, 50]);
        }
    });
};

const locomotionOptions = [
    { value: 'marche', label: 'À pied', icon: '🚶' },
    { value: 'velo', label: 'Vélo', icon: '🚲' },
    { value: 'moto', label: 'Zémidjan', icon: '🏍️' },
    { value: 'voiture', label: 'Voiture', icon: '🚗' },
];

const difficultyOptions = [
    { value: 'enfant', label: 'Enfant', icon: '👶' },
    { value: 'facile', label: 'Facile', icon: '🏹' },
    { value: 'moyen', label: 'Moyen', icon: '🦁' },
    { value: 'difficile', label: 'Difficile', icon: '👑' },
];

const decrementTeamSize = () => {
    if (startForm.team_size > 1) startForm.team_size--;
};

const incrementTeamSize = () => {
    if (startForm.team_size < 10) startForm.team_size++;
};
</script>

<template>
    <PlayerLayout>
        <Head title="CityPlay - Salon d'attente" />

        <div class="relative min-h-[calc(100vh-80px)] p-6 overflow-hidden flex flex-col justify-start">
            
            <!-- Orbes décoratifs -->
            <div ref="orb1Ref" class="pointer-events-none absolute top-[-5%] right-[-10%] w-[300px] h-[300px] rounded-full opacity-40 blur-3xl"
                 style="background: radial-gradient(circle, var(--cityplay-primary, #d65a31) 0%, transparent 70%);"></div>
            <div ref="orb2Ref" class="pointer-events-none absolute bottom-[-10%] left-[-10%] w-[250px] h-[250px] rounded-full opacity-30 blur-3xl"
                 style="background: radial-gradient(circle, var(--cityplay-neon-blue, #3b82f6) 0%, transparent 70%);"></div>

            <div class="relative z-10 w-full max-w-lg mx-auto bg-[#1c2128]/90 backdrop-blur-xl border border-white/5 rounded-[32px] p-6 shadow-2xl mb-6">
                
                <!-- En-tête -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 mx-auto bg-[#d65a31]/10 border border-[#d65a31]/30 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[#d65a31] animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-black text-white uppercase tracking-wider mb-2">Salon d'attente</h1>
                    <p class="text-sm cp-text-secondary font-medium">{{ session.city.name }}</p>
                </div>

                <!-- Paramètres de jeu (Host uniquement) -->
                <div v-if="isHost" class="mb-8 p-4 bg-white/5 border border-white/10 rounded-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xs font-black uppercase tracking-widest text-[#d65a31]">Configuration de l'aventure</h2>
                        <button @click="showSettings = !showSettings" class="text-xs text-white/50 hover:text-white underline">
                            {{ showSettings ? 'Réduire' : 'Modifier' }}
                        </button>
                    </div>

                    <div v-if="showSettings" class="space-y-4 animate-in fade-in slide-in-from-top-4 duration-300">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-white/40 mb-2">Moyen de transport</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button v-for="opt in locomotionOptions" :key="opt.value" 
                                    @click="startForm.locomotion = opt.value"
                                    :class="startForm.locomotion === opt.value ? 'bg-[#d65a31] border-[#d65a31]' : 'bg-white/5 border-white/10'"
                                    class="p-2 rounded-xl border flex flex-col items-center gap-1 transition-all">
                                    <span class="text-lg">{{ opt.icon }}</span>
                                    <span class="text-[8px] font-bold text-white uppercase">{{ opt.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-white/40 mb-2">Difficulté</label>
                            <div class="grid grid-cols-4 gap-2">
                                <button v-for="opt in difficultyOptions" :key="opt.value" 
                                    @click="startForm.difficulty = opt.value"
                                    :class="startForm.difficulty === opt.value ? 'bg-[#d65a31] border-[#d65a31]' : 'bg-white/5 border-white/10'"
                                    class="p-2 rounded-xl border flex flex-col items-center gap-1 transition-all">
                                    <span class="text-lg">{{ opt.icon }}</span>
                                    <span class="text-[8px] font-bold text-white uppercase">{{ opt.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-white/40 mb-2">Membres de l'équipe (max 10)</label>
                            <div class="flex items-center gap-4 bg-white/5 border border-white/10 p-2 rounded-xl">
                                <button @click="decrementTeamSize" class="w-8 h-8 flex items-center justify-center bg-white/10 rounded-lg text-white font-black">-</button>
                                <span class="flex-1 text-center text-white font-black text-sm">{{ startForm.team_size }}</span>
                                <button @click="incrementTeamSize" class="w-8 h-8 flex items-center justify-center bg-white/10 rounded-lg text-white font-black">+</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase text-white/40 mb-2">Durée ({{ startForm.available_minutes }} min)</label>
                            <input type="range" min="30" max="240" step="15" v-model="startForm.available_minutes" class="w-full h-1.5 bg-white/10 rounded-lg appearance-none cursor-pointer accent-[#d65a31]">
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-3 gap-2">
                        <div class="p-2 rounded-xl bg-white/5 border border-white/5 text-center">
                            <span class="block text-[8px] text-white/40 uppercase">Transport</span>
                            <span class="text-sm font-bold text-white">{{ locomotionOptions.find(o => o.value === startForm.locomotion)?.icon }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-white/5 border border-white/5 text-center">
                            <span class="block text-[8px] text-white/40 uppercase">Niveau</span>
                            <span class="text-sm font-bold text-white">{{ difficultyOptions.find(o => o.value === startForm.difficulty)?.icon }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-white/5 border border-white/5 text-center">
                            <span class="block text-[8px] text-white/40 uppercase">Durée</span>
                            <span class="text-sm font-bold text-white">⏱️ {{ startForm.available_minutes }}'</span>
                        </div>
                    </div>
                </div>

                <!-- Joueurs -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xs font-black uppercase tracking-widest text-[#d65a31]">L'Équipe ({{ players.length }}/10)</h2>
                    </div>
                    <div ref="playersListRef" class="space-y-3">
                        <div v-for="player in players" :key="player.id"
                             class="flex items-center gap-4 bg-white/5 border border-white/5 rounded-2xl p-3 transition-colors hover:bg-white/10">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#d65a31] to-orange-600 flex items-center justify-center text-white font-black shadow-lg">
                                {{ player.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-white">{{ player.name }}</p>
                                <p v-if="player.id === session.host_user_id" class="text-[10px] text-[#d65a31] font-black uppercase tracking-wider">👑 Chef de clan</p>
                                <p v-else class="text-[10px] cp-text-secondary font-black uppercase tracking-wider">Explorateur</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="space-y-4">
                    <button v-if="isHost" @click="showQRModal = true" class="w-full h-12 flex items-center justify-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl text-xs font-black uppercase tracking-widest text-white transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Inviter des amis
                    </button>
                    
                    <button v-if="isHost" @click="startSession" :disabled="startForm.processing" class="w-full h-14 bg-gradient-to-r from-[#d65a31] to-[#ff7a45] rounded-2xl text-sm font-black uppercase tracking-widest text-white shadow-[0_8px_32px_rgba(214,90,49,0.4)] hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                        <svg v-if="startForm.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span>{{ startForm.processing ? 'Démarrage...' : '🚀 Lancer l\'aventure' }}</span>
                    </button>

                    <div v-else class="text-center p-4 bg-white/5 rounded-2xl border border-white/5">
                        <p class="text-xs font-black text-[#d65a31] uppercase tracking-widest animate-pulse mb-1">Attente du Chef...</p>
                        <p class="text-[10px] cp-text-secondary font-medium">Le jeu commencera quand le chef de clan lancera la partie.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal QR Code -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-300" enter-from-class="opacity-0 scale-95" leave-active-class="transition-all duration-200" leave-to-class="opacity-0 scale-95">
                <div v-if="showQRModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-[#0d1117]/90 backdrop-blur-md" @click.self="showQRModal = false">
                    <div class="w-full max-w-sm bg-[#1c2128] border border-white/10 rounded-[32px] overflow-hidden shadow-2xl relative">
                        <div class="p-6 text-center border-b border-white/5 bg-gradient-to-b from-[#d65a31]/10 to-transparent">
                            <h3 class="text-lg font-black text-white uppercase tracking-wider mb-1">Inviter l'équipe</h3>
                            <p class="text-[10px] cp-text-secondary font-medium">Scannez ce QR Code pour rejoindre</p>
                        </div>
                        <div class="p-8 flex justify-center bg-white">
                            <QRCodeDisplay :url="qrCodeUrl" :size="200" />
                        </div>
                        <div class="p-4 bg-[#1c2128]">
                            <button @click="showQRModal = false" class="w-full h-12 bg-white/5 hover:bg-white/10 rounded-2xl text-xs font-black uppercase tracking-widest text-white transition-all">
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </PlayerLayout>
</template>

<style scoped>
.cp-text-secondary { color: rgba(255, 255, 255, 0.6); }

input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #d65a31;
  cursor: pointer;
  box-shadow: 0 0 10px rgba(214, 90, 49, 0.5);
}
</style>
