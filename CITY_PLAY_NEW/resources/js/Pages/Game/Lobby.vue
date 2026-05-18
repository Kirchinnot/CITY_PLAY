<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import QRCodeDisplay from '@/Components/QRCodeDisplay.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    session: Object,
    currentUser: Object,
    invitationUrl: String
});

const players = ref([...props.session.players]);
const showQRModal = ref(false);

const startForm = useForm({});

const startSession = () => {
    startForm.post(route('game.start', props.session.id));
};

const isHost = props.currentUser.id === props.session.host_user_id;

const getLocomotionIcon = (type) => {
    const icons = {
        marche: '🚶',
        velo: '🚲',
        moto: '🛵',
        voiture: '🚗'
    };
    return icons[type] || '🚶';
};

onMounted(() => {
    window.Echo.join(`session.${props.session.id}`)
        .here((users) => {
            console.log('Joueurs présents:', users);
        })
        .joining((user) => {
            if (!players.value.find(p => p.id === user.id)) {
                players.value.push(user);
            }
        })
        .leaving((user) => {
        })
        .listen('PlayerJoined', (e) => {
            if (!players.value.find(p => p.id === e.user.id)) {
                players.value.push(e.user);
            }
        });
});

onUnmounted(() => {
    window.Echo.leave(`session.${props.session.id}`);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Lobby" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl rounded-3xl border-2 border-cityplay-lime/20">
                    <!-- Header -->
                    <div class="bg-cityplay-lime p-8 text-cityplay-brown">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                            <div>
                                <h1 class="text-4xl font-black uppercase tracking-tighter mb-2">Lobby de l'aventure</h1>
                                <p class="font-bold opacity-80 uppercase tracking-widest text-sm">
                                    📍 {{ session.city.name }} • Mode {{ session.mode }}
                                </p>
                            </div>
                            <div class="bg-white/30 backdrop-blur-md px-6 py-3 rounded-2xl border border-white/40">
                                <span class="text-xs font-bold uppercase block opacity-60">Status</span>
                                <span class="text-lg font-black uppercase tracking-widest">{{ session.status }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 grid md:grid-cols-3 gap-8">
                        <!-- Players List -->
                        <div class="md:col-span-2 space-y-6">
                            <div>
                                <h2 class="text-2xl font-black text-cityplay-brown uppercase mb-4 flex items-center gap-2">
                                    👥 Équipe ({{ players.length }})
                                </h2>
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div 
                                        v-for="player in players" 
                                        :key="player.id"
                                        class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 border-2 border-transparent hover:border-cityplay-lime transition-all animate-in fade-in zoom-in duration-300"
                                    >
                                        <div class="w-12 h-12 rounded-full bg-cityplay-orange flex items-center justify-center text-white text-xl font-black">
                                            {{ player.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-cityplay-brown">{{ player.name }}</p>
                                            <p v-if="player.id === session.host_user_id" class="text-xs font-bold text-cityplay-orange uppercase">Chef d'aventure</p>
                                            <p v-else class="text-xs font-bold text-gray-400 uppercase">Explorateur</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-cityplay-yellow/10 p-6 rounded-3xl border-2 border-dashed border-cityplay-yellow">
                                <p class="text-cityplay-brown font-medium text-center">
                                    En attente des autres joueurs... Partage ton lien pour qu'ils te rejoignent !
                                </p>
                            </div>
                        </div>

                        <!-- Session Info & Actions -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 p-6 rounded-3xl space-y-4">
                                <h3 class="text-lg font-black text-cityplay-brown uppercase">Configuration</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-400 font-bold uppercase">Difficulté</span>
                                        <span class="font-black text-cityplay-orange uppercase">{{ session.difficulty }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-400 font-bold uppercase">Transport</span>
                                        <span class="font-black text-cityplay-brown uppercase">
                                            {{ getLocomotionIcon(session.locomotion) }} {{ session.locomotion }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-400 font-bold uppercase">Temps</span>
                                        <span class="font-black text-cityplay-brown uppercase">{{ session.available_minutes }} min</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="isHost" class="space-y-4">
                                <PrimaryButton
                                    @click="showQRModal = true"
                                    class="w-full justify-center py-4 bg-cityplay-brown hover:bg-cityplay-yellow text-white font-black text-base rounded-2xl shadow-lg transform transition active:scale-95 border-b-4 border-black/20 uppercase"
                                >
                                    📱 Inviter des amis
                                </PrimaryButton>

                                <PrimaryButton
                                    @click="startSession"
                                    class="w-full justify-center py-6 bg-cityplay-orange hover:bg-cityplay-yellow text-white font-black text-xl rounded-2xl shadow-xl transform transition active:scale-95 border-b-8 border-cityplay-brown/20 uppercase"
                                    :class="{ 'opacity-25': startForm.processing }"
                                    :disabled="startForm.processing"
                                >
                                    🚀 Lancer l'aventure
                                </PrimaryButton>
                                <p class="text-[10px] text-center text-gray-400 font-bold uppercase tracking-widest">
                                    Seul le chef d'aventure peut démarrer
                                </p>
                            </div>
                            <div v-else class="bg-cityplay-brown text-white p-6 rounded-3xl text-center">
                                <div class="animate-bounce text-3xl mb-2">⌛</div>
                                <p class="font-black uppercase text-sm">Attente du chef...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Modal -->
        <Teleport to="body">
            <div v-if="showQRModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showQRModal = false">
                <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full overflow-hidden transform transition-all">
                    <div class="bg-cityplay-orange p-6 text-white text-center">
                        <h3 class="text-2xl font-black uppercase tracking-tight">Invite tes amis !</h3>
                        <p class="text-sm opacity-80 mt-1">Qu'ils scannent ce QR Code pour te rejoindre</p>
                    </div>
                    
                    <div class="p-6 flex flex-col items-center">
                        <QRCodeDisplay 
                            :url="invitationUrl || route('game.join', { token: session.invitation?.token })"
                            :size="220"
                        />
                        
                        <button 
                            @click="showQRModal = false"
                            class="mt-6 w-full py-3 bg-gray-100 hover:bg-gray-200 text-cityplay-brown font-bold rounded-xl transition-colors uppercase text-sm"
                        >
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
