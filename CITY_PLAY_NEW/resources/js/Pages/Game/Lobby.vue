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
    if (!isPendingSession.value) {
        window.location.href = window.route('player.game.map');
        return;
    }
    startForm.post(window.route('game.start', props.session.id));
};

const isHost = props.currentUser.id === props.session.host_user_id;
const isPendingSession = computed(() => props.session.status === 'pending');
const isActiveSession = computed(() => props.session.status === 'active');
const isPausedSession = computed(() => props.session.status === 'paused');

const orb1Ref = ref(null);
const orb2Ref = ref(null);
const playersListRef = ref(null);

onMounted(() => {
    // Animations Orbes aux couleurs Soleil (Orange/Or)
    if (orb1Ref.value) gsap.to(orb1Ref.value, { y: -30, x: 20, duration: 6, repeat: -1, yoyo: true, ease: 'sine.inOut' });
    if (orb2Ref.value) gsap.to(orb2Ref.value, { y: 25, x: -15, duration: 8, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 1 });

    if (window.Echo) {
        window.Echo.join(`session.${props.session.id}`)
            .joining((user) => {
                if (!players.value.find(p => p.id === user.id)) {
                    players.value.push(user);
                    animateNewPlayer();
                }
            })
            .listen('PlayerJoined', (e) => {
                if (!players.value.find(p => p.id === e.user.id)) {
                    players.value.push(e.user);
                    animateNewPlayer();
                }
            });
    }
        
    if (!isHost && (props.session.status === 'active' || props.session.status === 'paused')) {
        window.location.href = window.route('player.game.map');
        return;
    }

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
    if (window.Echo) window.Echo.leave(`session.${props.session.id}`);
});

const animateNewPlayer = () => {
    nextTick(() => {
        if (playersListRef.value && playersListRef.value.lastElementChild) {
            gsap.from(playersListRef.value.lastElementChild, {
                opacity: 0,
                y: 15,
                scale: 0.9,
                duration: 0.4,
                ease: "back.out(1.7)"
            });
            if ('vibrate' in navigator) navigator.vibrate(40);
        }
    });
};

const locomotionOptions = [
    { value: 'marche', label: 'À pied', icon: 'M13 4a1 1 0 10-2 0 1 1 0 002 0zM6 14l2-6 3 2 3-2 2 6h-10z' },
    { value: 'velo', label: 'Vélo', icon: 'M5 16a3 3 0 106 0 3 3 0 00-6 0zm13 0a3 3 0 106 0 3 3 0 00-6 0zM7 16h3l3-8h4' },
    { value: 'moto', label: 'Moto', icon: 'M5 16a3 3 0 106 0 3 3 0 00-6 0zm13 0a3 3 0 106 0 3 3 0 00-6 0zM7 16h3l4-10h3' },
    { value: 'voiture', label: 'Voiture', icon: 'M3 16l1.5-4.5h15L21 16' },
];

const difficultyOptions = [
    { value: 'enfant', label: 'Enfant', icon: 'M12 12c2.21 0 4-1.79 4-4S14.21 4 12 4 8 5.79 8 8s1.79 4 4 4z' },
    { value: 'facile', label: 'Facile', icon: 'M12 3l7 4v5c0 5.25-3.35 9.75-7 11-3.65-1.25-7-5.75-7-11V7l7-4z' },
    { value: 'moyen', label: 'Moyen', icon: 'M4 20l8-14 8 14H4z' },
    { value: 'difficile', label: 'Expert', icon: 'M5 16l2-6 3 4 3-4 2 6h-10z' },
];

const decrementTeamSize = () => startForm.team_size > 1 && startForm.team_size--;
const incrementTeamSize = () => startForm.team_size < 10 && startForm.team_size++;
</script>

<template>
    <PlayerLayout>
        <Head title="CityPlay - Salon d'attente" />

        <div class="relative min-h-[calc(100vh-64px)] bg-[#FDFBF7] p-4 overflow-hidden flex flex-col font-sans text-[#2D1B16]">
            
            <div ref="orb1Ref" class="pointer-events-none absolute top-[-5%] right-[-10%] w-[350px] h-[350px] rounded-full opacity-20 blur-3xl bg-gradient-to-br from-[#E0531C] to-[#FFB700]"></div>
            <div ref="orb2Ref" class="pointer-events-none absolute bottom-[-5%] left-[-10%] w-[300px] h-[300px] rounded-full opacity-10 blur-3xl bg-[#E0531C]"></div>

            <div class="relative z-10 w-full max-w-md mx-auto flex flex-col flex-1">
                
                <header class="text-center mb-6 pt-2">
                    <div class="inline-flex p-3 bg-gradient-to-br from-[#E0531C]/10 to-[#FFB700]/10 border border-[#E0531C]/20 rounded-2xl mb-3 shadow-inner">
                        <svg class="w-7 h-7 text-[#E0531C]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-black uppercase tracking-tighter leading-none">Salon d'attente</h1>
                    <p class="text-xs font-bold text-[#E0531C]/70 uppercase tracking-widest mt-1">{{ session.city.name }}</p>
                </header>

                <section v-if="isHost" class="bg-white/80 backdrop-blur-md border border-[#2D1B16]/5 rounded-[2.5rem] p-5 shadow-sm mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-black uppercase tracking-tight">Configuration</h2>
                        <button @click="showSettings = !showSettings" 
                                class="text-[10px] font-black uppercase tracking-wider py-1.5 px-3 bg-[#2D1B16]/5 rounded-full active:scale-95 transition-all">
                            {{ showSettings ? 'Masquer' : 'Modifier' }}
                        </button>
                    </div>

                    <div v-if="!showSettings" class="flex gap-2 overflow-x-auto no-scrollbar">
                        <div class="flex-none bg-[#FDFBF7] border border-[#2D1B16]/5 py-2 px-4 rounded-2xl flex items-center gap-2">
                            <span class="text-[10px] font-bold opacity-50 uppercase">Mode:</span>
                            <span class="text-xs font-black">{{ locomotionOptions.find(o => o.value === startForm.locomotion)?.label }}</span>
                        </div>
                        <div class="flex-none bg-[#FDFBF7] border border-[#2D1B16]/5 py-2 px-4 rounded-2xl flex items-center gap-2">
                            <span class="text-[10px] font-bold opacity-50 uppercase">Défi:</span>
                            <span class="text-xs font-black">{{ difficultyOptions.find(o => o.value === startForm.difficulty)?.label }}</span>
                        </div>
                    </div>

                    <div v-else class="space-y-5 animate-in fade-in slide-in-from-top-2 duration-300">
                        <div>
                            <span class="text-[10px] font-black uppercase opacity-40 ml-1">Moyen de transport</span>
                            <div class="flex overflow-x-auto snap-x snap-mandatory gap-3 py-2 no-scrollbar">
                                <button v-for="opt in locomotionOptions" :key="opt.value"
                                    @click="startForm.locomotion = opt.value"
                                    class="snap-start flex-none w-32 p-4 rounded-3xl border-2 transition-all active:scale-95"
                                    :class="startForm.locomotion === opt.value ? 'bg-gradient-to-br from-[#E0531C] to-[#FFB700] border-transparent text-white shadow-lg shadow-orange-500/20' : 'bg-white border-[#2D1B16]/5 text-[#2D1B16]/60'">
                                    <svg class="w-6 h-6 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="opt.icon" /></svg>
                                    <span class="text-[11px] font-black uppercase block">{{ opt.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <span class="text-[10px] font-black uppercase opacity-40 ml-1">Difficulté de la mission</span>
                            <div class="flex overflow-x-auto snap-x snap-mandatory gap-3 py-2 no-scrollbar">
                                <button v-for="opt in difficultyOptions" :key="opt.value"
                                    @click="startForm.difficulty = opt.value"
                                    class="snap-start flex-none w-32 p-4 rounded-3xl border-2 transition-all active:scale-95"
                                    :class="startForm.difficulty === opt.value ? 'bg-gradient-to-br from-[#E0531C] to-[#FFB700] border-transparent text-white shadow-lg shadow-orange-500/20' : 'bg-white border-[#2D1B16]/5 text-[#2D1B16]/60'">
                                    <svg class="w-6 h-6 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="opt.icon" /></svg>
                                    <span class="text-[11px] font-black uppercase block">{{ opt.label }}</span>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <span class="text-[10px] font-black uppercase opacity-40 ml-1">Équipe ({{ startForm.team_size }})</span>
                                <div class="flex items-center bg-[#FDFBF7] rounded-2xl p-1 border border-[#2D1B16]/5">
                                    <button @click="decrementTeamSize" class="w-10 h-10 flex items-center justify-center font-bold active:scale-75 transition-transform text-lg">-</button>
                                    <span class="flex-1 text-center font-black text-sm">{{ startForm.team_size }}</span>
                                    <button @click="incrementTeamSize" class="w-10 h-10 flex items-center justify-center font-bold active:scale-75 transition-transform text-lg">+</button>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <span class="text-[10px] font-black uppercase opacity-40 ml-1">Temps ({{ startForm.available_minutes }}')</span>
                                <div class="h-12 flex items-center px-2">
                                    <input type="range" min="30" max="240" step="15" v-model="startForm.available_minutes" 
                                           class="w-full h-2 bg-[#2D1B16]/10 rounded-lg appearance-none cursor-pointer accent-[#E0531C]">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="flex-1 min-h-0 flex flex-col mb-4">
                    <div class="flex items-center justify-between px-2 mb-3">
                        <h2 class="text-[11px] font-black uppercase tracking-widest text-[#2D1B16]/40">Équipe de chasse ({{ players.length }}/10)</h2>
                    </div>
                    
                    <div ref="playersListRef" class="space-y-2 overflow-y-auto pr-1 max-h-[250px] no-scrollbar">
                        <div v-for="player in players" :key="player.id"
                             class="flex items-center gap-3 bg-white border border-[#2D1B16]/5 rounded-2xl p-2 shadow-sm">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#E0531C] to-[#FFB700] flex items-center justify-center text-white font-black shadow-md shadow-orange-500/10">
                                {{ player.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-black leading-tight">{{ player.name }}</p>
                                <p class="text-[9px] font-bold uppercase tracking-wide opacity-50">
                                    {{ player.id === session.host_user_id ? 'Chef de clan' : 'Explorateur' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <footer class="mt-auto space-y-3 pb-4">
                    <button v-if="isHost" @click="showQRModal = true" 
                            class="w-full h-14 border-2 border-dashed border-[#E0531C]/30 rounded-2xl text-[11px] font-black uppercase tracking-widest text-[#E0531C] flex items-center justify-center gap-2 active:scale-95 transition-all bg-[#E0531C]/5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Invoquer des amis
                    </button>

                    <button v-if="isHost && isPendingSession" @click="startSession" :disabled="startForm.processing" 
                            class="w-full h-16 bg-gradient-to-r from-[#E0531C] to-[#FFB700] rounded-2xl text-sm font-black uppercase tracking-widest text-white shadow-xl shadow-orange-500/30 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <svg v-if="startForm.processing" class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <template v-else>
                            <span>Démarrer l'aventure</span>
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7" /></svg>
                        </template>
                    </button>

                    <div v-else-if="!isHost" class="p-5 bg-white/50 border border-[#E0531C]/10 rounded-3xl text-center">
                        <div class="flex justify-center gap-1 mb-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-[#E0531C] animate-bounce"></div>
                            <div class="w-1.5 h-1.5 rounded-full bg-[#E0531C] animate-bounce [animation-delay:0.2s]"></div>
                            <div class="w-1.5 h-1.5 rounded-full bg-[#E0531C] animate-bounce [animation-delay:0.4s]"></div>
                        </div>
                        <p class="text-[10px] font-black uppercase text-[#E0531C] tracking-widest">Le Chef prépare la mission...</p>
                    </div>
                </footer>
            </div>
        </div>

        <Teleport to="body">
            <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" leave-active-class="transition duration-200 ease-in" leave-to-class="opacity-0 scale-95">
                <div v-if="showQRModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-[#2D1B16]/80 backdrop-blur-md" @click.self="showQRModal = false">
                    <div class="w-full max-w-sm bg-[#FDFBF7] rounded-[3rem] overflow-hidden shadow-2xl relative border-4 border-white">
                        <div class="p-6 text-center">
                            <h3 class="text-xl font-black text-[#2D1B16] uppercase tracking-tight">Recrute ton clan</h3>
                            <p class="text-[10px] font-bold text-[#E0531C] uppercase tracking-wider">Fais scanner ce code à tes amis</p>
                        </div>
                        <div class="p-8 bg-white flex justify-center shadow-inner">
                            <QRCodeDisplay :url="qrCodeUrl" :size="220" />
                        </div>
                        <div class="p-4">
                            <button @click="showQRModal = false" class="w-full h-12 bg-[#2D1B16] rounded-2xl text-xs font-black uppercase tracking-widest text-white active:scale-95 transition-all">
                                C'est compris !
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </PlayerLayout>
</template>

<style>
/* Masquage scrollbar pur Tailwind ne suffit pas toujours */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 24px;
  width: 24px;
  border-radius: 50%;
  background: #E0531C;
  border: 4px solid white;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(224, 83, 28, 0.3);
}
</style>