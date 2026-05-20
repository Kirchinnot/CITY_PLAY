<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
            <div class="max-w-7xl mx-auto w-full px-4 py-4">
                <div>
                    <h2 class="text-2xl font-black text-[#2D1B16] m-0">Joueurs & Équipes</h2>
                    <p class="text-xs text-[#5C4033]/70 mt-1">Gestion de la communauté et des sessions de jeu</p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 py-8">
            
            <!-- Onglets -->
            <div class="flex gap-2 mb-8 border-b border-[#E0531C]/10 pb-0">
                <button 
                    @click="activeTab = 'players'"
                    class="px-4 py-3 border-b-4 text-xs font-black uppercase tracking-wider transition-all whitespace-nowrap focus:outline-none"
                    :class="activeTab === 'players' 
                        ? 'border-[#E0531C] text-[#2D1B16]' 
                        : 'border-transparent text-[#5C4033]/60 hover:text-[#5C4033]/80'"
                >
                    <svg class="w-4 h-4 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Joueurs ({{ players.length }})</span>
                </button>
                <button 
                    @click="activeTab = 'teams'"
                    class="px-4 py-3 border-b-4 text-xs font-black uppercase tracking-wider transition-all whitespace-nowrap focus:outline-none"
                    :class="activeTab === 'teams' 
                        ? 'border-[#E0531C] text-[#2D1B16]' 
                        : 'border-transparent text-[#5C4033]/60 hover:text-[#5C4033]/80'"
                >
                    <svg class="w-4 h-4 inline-block mr-2 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10h.01M11 10h.01M9 10h.01M19 10a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Équipes ({{ teams.length }})</span>
                </button>
            </div>

            <!-- Liste des JOUEURS -->
            <div v-if="activeTab === 'players'">
                <div class="bg-white border border-[#E0531C]/10 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-[#FFF3DF] border-b border-[#E0531C]/10">
                                    <th class="text-left px-4 py-3 text-[10px] font-black uppercase text-[#2D1B16] tracking-wider">Joueur</th>
                                    <th class="text-left px-4 py-3 text-[10px] font-black uppercase text-[#2D1B16] tracking-wider hidden sm:table-cell">Email / Téléphone</th>
                                    <th class="text-left px-4 py-3 text-[10px] font-black uppercase text-[#2D1B16] tracking-wider hidden md:table-cell">Stats</th>
                                    <th class="text-left px-4 py-3 text-[10px] font-black uppercase text-[#2D1B16] tracking-wider hidden lg:table-cell">Dernière activité</th>
                                    <th class="text-right px-4 py-3 text-[10px] font-black uppercase text-[#2D1B16] tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="player in players" :key="player.id" class="border-b border-[#E0531C]/10 hover:bg-[#FFF7EB]/50 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-[#FFEBCC] text-[#E0531C] flex items-center justify-center font-black text-sm">
                                                {{ player.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-[#2D1B16]">{{ player.name }}</div>
                                                <div class="text-xs text-[#5C4033]/60">Inscrit le {{ new Date(player.created_at).toLocaleDateString() }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 hidden sm:table-cell">
                                        <div class="text-sm text-[#2D1B16]">{{ player.email }}</div>
                                        <div class="text-xs text-[#5C4033]/60">{{ player.phone || 'Pas de numéro' }}</div>
                                    </td>
                                    <td class="px-4 py-4 hidden md:table-cell">
                                        <div class="flex gap-2">
                                            <span class="text-xs font-bold bg-blue-100 text-blue-700 px-2 py-1 rounded-lg">🎮 {{ player.game_sessions_count }}</span>
                                            <span class="text-xs font-bold bg-yellow-100 text-yellow-700 px-2 py-1 rounded-lg">🏆 {{ player.achievements_count }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 hidden lg:table-cell">
                                        <div class="text-sm text-[#2D1B16]">{{ formatDate(player.updated_at) }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <button @click="deletePlayer(player.id)" class="text-xs font-bold text-white bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded-lg transition-colors">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="players.length === 0">
                                    <td colspan="5" class="text-center py-12 text-[#5C4033]/70">
                                        Aucun joueur trouvé.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Liste des ÉQUIPES / SESSIONS -->
            <div v-else>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="team in teams" :key="team.id" class="bg-white border border-[#E0531C]/10 rounded-2xl p-5 flex flex-col gap-4 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-xs font-black px-2 py-1 rounded-lg mb-2 inline-block" :class="team.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                                    {{ team.status === 'active' ? 'En cours' : team.status === 'completed' ? 'Terminé' : team.status }}
                                </span>
                                <h3 class="font-black text-base text-[#2D1B16] m-0">
                                    Équipe de {{ team.host?.name }}
                                </h3>
                                <p class="text-xs text-[#5C4033]/60 flex items-center gap-2 mt-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>
                                    {{ team.city?.name }} • {{ formatDate(team.started_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="bg-[#FFF7EB] border border-[#E0531C]/10 rounded-xl p-3">
                            <div class="text-xs font-black uppercase text-[#E0531C] tracking-wider mb-2">
                                Membres ({{ team.players_count }})
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <div v-for="player in team.players" :key="player.id" class="text-xs font-bold bg-white border border-[#E0531C]/20 text-[#2D1B16] px-2 py-1 rounded-full">
                                    {{ player.name }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-[#FFF3DF] rounded-xl p-3">
                                <div class="text-[9px] font-black uppercase text-[#5C4033] tracking-wider">Difficulté</div>
                                <div class="font-black text-[#E0531C]">{{ team.difficulty }}</div>
                            </div>
                            <div class="bg-[#FFF3DF] rounded-xl p-3">
                                <div class="text-[9px] font-black uppercase text-[#5C4033] tracking-wider">Transport</div>
                                <div class="font-black text-[#E0531C]">{{ team.locomotion }}</div>
                            </div>
                            <div class="bg-[#FFF3DF] rounded-xl p-3">
                                <div class="text-[9px] font-black uppercase text-[#5C4033] tracking-wider">Énigmes</div>
                                <div class="font-black text-[#E0531C]">{{ team.solved_places }} / {{ team.total_places }}</div>
                            </div>
                            <div class="bg-[#FFF3DF] rounded-xl p-3">
                                <div class="text-[9px] font-black uppercase text-[#5C4033] tracking-wider">Score</div>
                                <div class="font-black text-[#E0531C]">{{ team.total_score || 0 }} pts</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="teams.length === 0" class="col-span-full text-center py-12 bg-[#FFF3DF] border-2 border-dashed border-[#E0531C]/20 rounded-2xl">
                        <p class="text-[#5C4033]/70 font-bold">Aucune équipe ou session active pour le moment.</p>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
