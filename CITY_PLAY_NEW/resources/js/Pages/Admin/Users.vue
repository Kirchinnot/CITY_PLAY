<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    players: Array,
    teams: Array,
});

const activeTab = ref('players'); // 'players' or 'teams'

const deletePlayer = (playerId) => {
    if (confirm('Voulez-vous vraiment supprimer ce joueur ? Cette action est irréversible.')) {
        router.delete(route('admin.users.destroy', playerId));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Jamais';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="CityPlay Admin - Joueurs & Équipes" />

    <AdminLayout>
        <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; width: 100%;">
                <div>
                    <h2 style="font-family: var(--font-family-display); font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark); margin: 0;">
                        Joueurs & Équipes
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 0.25rem;">
                        Gestion de la communauté et des sessions de jeu
                    </p>
                </div>
            </div>
        </template>

        <div class="premium-container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
            
            <!-- Onglets -->
            <div style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                <button 
                    @click="activeTab = 'players'"
                    class="tab-btn"
                    :class="{ 'active': activeTab === 'players' }"
                >
                    👤 Joueurs ({{ players.length }})
                </button>
                <button 
                    @click="activeTab = 'teams'"
                    class="tab-btn"
                    :class="{ 'active': activeTab === 'teams' }"
                >
                    👥 Équipes & Sessions ({{ teams.length }})
                </button>
            </div>

            <!-- Liste des JOUEURS -->
            <div v-if="activeTab === 'players'" class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div class="premium-card" style="padding: 0; overflow: hidden;">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>Joueur</th>
                                <th>Email / Téléphone</th>
                                <th>Stats</th>
                                <th>Dernière activité</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="player in players" :key="player.id">
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div class="avatar-circle">
                                            {{ player.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--color-text-main);">{{ player.name }}</div>
                                            <div style="font-size: 0.75rem; color: var(--color-text-muted);">Inscrit le {{ new Date(player.created_at).toLocaleDateString() }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">{{ player.email }}</div>
                                    <div style="font-size: 0.75rem; color: var(--color-text-muted);">{{ player.phone || 'Pas de numéro' }}</div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <span class="premium-badge badge-info" title="Parties jouées">🎮 {{ player.game_sessions_count }}</span>
                                        <span class="premium-badge badge-success" title="Badges débloqués">🏆 {{ player.achievements_count }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem;">{{ formatDate(player.updated_at) }}</div>
                                </td>
                                <td style="text-align: right;">
                                    <button @click="deletePlayer(player.id)" class="premium-btn premium-btn-danger" style="padding: 0.4rem 0.75rem; font-size: 0.75rem;">
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="players.length === 0">
                                <td colspan="5" style="text-align: center; padding: 3rem; color: var(--color-text-muted);">
                                    Aucun joueur trouvé.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Liste des ÉQUIPES / SESSIONS -->
            <div v-else class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                    <div v-for="team in teams" :key="team.id" class="premium-card" style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span class="premium-badge" :class="team.status === 'active' ? 'badge-success' : 'badge-warning'" style="margin-bottom: 0.5rem; display: inline-block;">
                                    {{ team.status === 'active' ? 'En cours' : team.status === 'completed' ? 'Terminé' : team.status }}
                                </span>
                                <h3 style="font-weight: 800; font-size: 1.1rem; color: var(--color-text-main); margin: 0;">
                                    Équipe de {{ team.host?.name }}
                                </h3>
                                <p style="font-size: 0.75rem; color: var(--color-text-muted);">
                                    📍 {{ team.city?.name }} • {{ formatDate(team.started_at) }}
                                </p>
                            </div>
                        </div>

                        <div style="background: var(--color-bg-light); padding: 1rem; border-radius: var(--border-radius-md); border: 1px solid var(--border-color);">
                            <div style="font-size: 0.75rem; font-weight: 700; color: var(--color-primary); text-transform: uppercase; margin-bottom: 0.5rem;">
                                Membres ({{ team.players_count }})
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                <div v-for="player in team.players" :key="player.id" class="member-chip">
                                    {{ player.name }}
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.8rem;">
                            <div class="stat-box">
                                <span class="stat-label">Difficulté</span>
                                <span class="stat-value">{{ team.difficulty }}</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">Transport</span>
                                <span class="stat-value">{{ team.locomotion }}</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">Énigmes</span>
                                <span class="stat-value">{{ team.solved_places }} / {{ team.total_places }}</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-label">Score</span>
                                <span class="stat-value">{{ team.total_score || 0 }} pts</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="teams.length === 0" style="grid-column: 1 / -1; text-align: center; padding: 5rem; background: white; border-radius: var(--border-radius-lg); border: 2px dashed var(--border-color);">
                        <p style="color: var(--color-text-muted); font-weight: 700;">Aucune équipe ou session active pour le moment.</p>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
.tab-btn {
    padding: 0.75rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--color-text-muted);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    border-radius: var(--border-radius-md) var(--border-radius-md) 0 0;
    position: relative;
}

.tab-btn.active {
    color: var(--color-primary);
}

.tab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -0.5rem;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--color-primary);
    border-radius: 3px 3px 0 0;
}

.avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--color-primary-light);
    color: var(--color-primary-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.9rem;
}

.member-chip {
    padding: 0.25rem 0.6rem;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 100px;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--color-text-main);
}

.stat-box {
    display: flex;
    flex-direction: column;
    padding: 0.5rem;
    background: var(--color-bg-light);
    border-radius: var(--border-radius-sm);
}

.stat-label {
    font-size: 0.65rem;
    color: var(--color-text-muted);
    text-transform: uppercase;
    font-weight: 700;
}

.stat-value {
    font-weight: 800;
    color: var(--color-text-main);
}

.premium-table {
    width: 100%;
    border-collapse: collapse;
}

.premium-table th {
    text-align: left;
    padding: 1rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: var(--color-text-muted);
    border-bottom: 1px solid var(--border-color);
    background: var(--color-bg-light);
}

.premium-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.premium-table tr:last-child td {
    border-bottom: none;
}
</style>
