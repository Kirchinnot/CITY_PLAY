<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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

const getDifficultyLabel = (level) => {
    const labels = {
        facile: 'Facile 🏹',
        moyen: 'Moyen 🦁',
        difficile: 'Difficile 👑'
    };
    return labels[level] || level;
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
        <Head title="CityPlay - Salon d'attente" />

        <div class="premium-container" style="max-width: 1000px; margin: 0 auto; padding: 2rem 1rem;">
            
            <div class="premium-card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-color); box-shadow: var(--shadow-lg); background: var(--color-surface-light);">
                <!-- Header themed in Terracotta & Gold -->
                <div style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark)); padding: 2.5rem 2rem; color: white; display: flex; flex-direction: column; md:flex-direction: row; justify-content: space-between; align-items: flex-start; md:align-items: center; gap: 1.5rem; position: relative;">
                    <!-- Benin subtle accent bar -->
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #1E6B43 33%, #D4AF37 33%, #D4AF37 66%, #962D2D 66%);"></div>
                    
                    <div>
                        <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; background: rgba(255,255,255,0.2); padding: 0.25rem 0.6rem; border-radius: 4px; display: inline-block; margin-bottom: 0.5rem;">🎮 Salon de jeu (Lobby)</span>
                        <h1 style="font-family: var(--font-family-display); font-size: 2.25rem; font-weight: 800; text-transform: uppercase; margin: 0; letter-spacing: -0.01em; line-height: 1.1;">
                            {{ session.city.name }}
                        </h1>
                        <p style="font-size: 0.85rem; color: rgba(255,255,255,0.85); margin: 0.25rem 0 0 0; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                            Aventure interactive • Mode {{ session.mode }}
                        </p>
                    </div>

                    <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.3); padding: 0.75rem 1.25rem; border-radius: var(--border-radius-md); text-align: right; min-width: 150px;">
                        <span style="font-size: 0.6rem; font-weight: 800; uppercase: true; opacity: 0.7; display: block; text-transform: uppercase; letter-spacing: 0.05em;">Statut</span>
                        <span style="font-family: var(--font-family-display); font-size: 1.1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-secondary);">
                            ⚡ {{ session.status }}
                        </span>
                    </div>
                </div>

                <div style="padding: 2.5rem; display: grid; grid-template-columns: 1fr; lg:grid-template-columns: 2fr 1fr; gap: 2.5rem; align-items: start;">
                    <!-- Left: Equipe / Joueurs -->
                    <div style="display: flex; flex-direction: column; gap: 1.75rem;">
                        <div>
                            <h2 style="font-family: var(--font-family-display); font-size: 1.35rem; font-weight: 800; color: var(--color-primary-dark); margin: 0 0 1.25rem 0; display: flex; align-items: center; gap: 0.5rem; text-transform: uppercase; letter-spacing: -0.01em;">
                                <span>👥</span> L'Équipe d'Exploration ({{ players.length }})
                            </h2>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem;">
                                <div 
                                    v-for="player in players" 
                                    :key="player.id"
                                    style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border-radius: var(--border-radius-md); background: var(--color-bg-light); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); transition: transform 0.2s;"
                                    class="hover:scale-[1.02]"
                                >
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light)); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800; font-family: var(--font-family-display); box-shadow: var(--shadow-sm);">
                                        {{ player.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p style="margin: 0; font-weight: 800; color: var(--color-text-main); font-size: 0.95rem;">{{ player.name }}</p>
                                        <span v-if="player.id === session.host_user_id" style="font-size: 0.65rem; font-weight: 800; color: var(--color-primary); text-transform: uppercase; display: block; letter-spacing: 0.05em;">👑 Chef de clan</span>
                                        <span v-else style="font-size: 0.65rem; font-weight: 700; color: var(--color-text-muted); text-transform: uppercase; display: block; letter-spacing: 0.05em;">🧭 Explorateur</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Waiting hint banner -->
                        <div style="background: var(--color-warning-bg); border: 1px dashed var(--color-secondary); padding: 1.25rem; border-radius: var(--border-radius-md); display: flex; align-items: center; gap: 1rem; color: #b45309; font-size: 0.9rem; font-weight: 600; line-height: 1.4;">
                            <span style="font-size: 1.75rem;">⏳</span>
                            <p style="margin: 0;">En attente de vos amis... Partagez le lien d'invitation ou faites-leur scanner le QR Code pour démarrer la chasse ensemble !</p>
                        </div>
                    </div>

                    <!-- Right: Info de session & CTA de lancement -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div style="background: var(--color-bg-light); border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
                            <h3 style="font-family: var(--font-family-display); font-size: 1.1rem; font-weight: 800; color: var(--color-primary-dark); margin: 0; text-transform: uppercase; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; letter-spacing: 0.05em;">Configuration</h3>
                            
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700;">
                                    <span style="color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Difficulté</span>
                                    <span style="color: var(--color-primary-dark);">{{ getDifficultyLabel(session.difficulty) }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700;">
                                    <span style="color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Moyen de transport</span>
                                    <span style="color: var(--color-primary-dark);">{{ getLocomotionIcon(session.locomotion) }} {{ session.locomotion }}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700;">
                                    <span style="color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Temps disponible</span>
                                    <span style="color: var(--color-primary-dark);">⏱️ {{ session.available_minutes }} min</span>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Lancer / Attente -->
                        <div v-if="isHost" style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <button
                                @click="showQRModal = true"
                                class="premium-btn premium-btn-outline"
                                style="width: 100%; font-family: var(--font-family-display); font-weight: 800; text-transform: uppercase; padding: 0.85rem;"
                            >
                                📱 Inviter des amis
                            </button>

                            <button
                                @click="startSession"
                                class="premium-btn premium-btn-primary"
                                style="width: 100%; font-family: var(--font-family-display); font-weight: 800; text-transform: uppercase; padding: 1.25rem; font-size: 1.1rem; box-shadow: var(--shadow-glow);"
                                :disabled="startForm.processing"
                            >
                                🚀 Lancer l'aventure
                            </button>
                            <span style="font-size: 0.65rem; color: var(--color-text-muted); font-weight: 800; text-transform: uppercase; text-align: center; display: block; letter-spacing: 0.05em;">
                                Seul le chef de clan peut donner le départ
                            </span>
                        </div>
                        <div v-else style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <div style="background: var(--color-surface-light); border: 1px solid var(--border-color); padding: 1.5rem; border-radius: var(--border-radius-lg); text-align: center; box-shadow: var(--shadow-sm); margin-bottom: 0.25rem;">
                                <div style="font-size: 2.25rem; animation: bounce 2s infinite; margin-bottom: 0.5rem;">👑</div>
                                <h4 style="font-family: var(--font-family-display); font-size: 1rem; font-weight: 800; color: var(--color-primary-dark); margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">Attente du Chef</h4>
                                <p style="font-size: 0.75rem; color: var(--color-text-muted); margin: 0.25rem 0 0 0; font-weight: 500;">Le chef d'aventure va démarrer la partie d'un instant à l'autre...</p>
                            </div>
                            
                            <button
                                @click="showQRModal = true"
                                class="premium-btn premium-btn-outline"
                                style="width: 100%; font-family: var(--font-family-display); font-weight: 800; text-transform: uppercase; padding: 0.85rem;"
                            >
                                📱 Inviter des amis
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- QR Code Modal (Premium themed in Terracotta/Gold) -->
        <Teleport to="body">
            <div v-if="showQRModal" style="position: fixed; inset: 0; z-index: 50; display: flex; align-items: center; justify-content: center; padding: 1rem; background: rgba(28,24,22,0.6); backdrop-filter: blur(8px);" @click.self="showQRModal = false">
                <div style="background: var(--color-surface-light); border-radius: var(--border-radius-xl); box-shadow: var(--shadow-premium); max-width: 380px; width: 100%; overflow: hidden; border: 1px solid var(--border-color);">
                    <div style="background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark)); padding: 1.5rem; color: white; text-align: center; position: relative;">
                        <h3 style="font-family: var(--font-family-display); font-size: 1.35rem; font-weight: 800; text-transform: uppercase; margin: 0; letter-spacing: -0.01em;">Invite ton équipe !</h3>
                        <p style="font-size: 0.8rem; opacity: 0.9; margin: 0.25rem 0 0 0; font-weight: 500;">Faites-leur scanner ce QR code pour rejoindre la session en direct.</p>
                    </div>
                    
                    <div style="padding: 2rem; display: flex; flex-direction: column; align-items: center; gap: 1.5rem;">
                        <div style="background: white; padding: 1rem; border-radius: var(--border-radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-md);">
                            <QRCodeDisplay 
                                :url="invitationUrl || route('game.join', { token: session.invitation?.token })"
                                :size="220"
                            />
                        </div>
                        
                        <button 
                            @click="showQRModal = false"
                            class="premium-btn premium-btn-outline"
                            style="width: 100%; padding: 0.75rem; text-transform: uppercase; font-family: var(--font-family-display); font-weight: 800; font-size: 0.85rem;"
                        >
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}
</style>
