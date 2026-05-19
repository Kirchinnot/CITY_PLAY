<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    city: Object
});

const form = useForm({
    mode: 'collectif',
    difficulty: 'facile',
    locomotion: 'marche',
    max_players: 5,
    duration_minutes: 60,
    expires_in_hours: 24
});

const submit = () => {
    form.post(route('admin.invitations.store', props.city.id));
};
</script>

<template>
    <Head title="Créer une Invitation - CityPlay" />

    <AdminLayout>
        <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 800px; margin: 0 auto; width: 100%;">
                <div>
                    <h2 style="font-family: var(--font-family-display); font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark); margin: 0;">
                        Créer une Invitation
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0.25rem 0 0 0;">
                        Générer un lien de jeu pour la ville : <span style="font-weight: 700; color: var(--color-primary);">{{ city.name }}</span>
                    </p>
                </div>
                <Link
                    :href="route('admin.cities.index')"
                    class="premium-btn premium-btn-outline"
                    style="text-decoration: none;"
                >
                    Retour aux parcours
                </Link>
            </div>
        </template>

        <div class="premium-container" style="max-width: 800px; margin: 0 auto; padding: 2rem 1rem;">
            <div class="premium-card" style="border: 1px solid var(--border-color); box-shadow: var(--shadow-lg); background: var(--color-surface-light); padding: 2.5rem;">
                
                <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 2rem;">
                    
                    <!-- Section 1 : Mode de jeu -->
                    <div>
                        <label class="premium-label" style="font-size: 1rem; font-weight: 800; color: var(--color-primary-dark); margin-bottom: 0.75rem;">
                            1. Mode de Gameplay
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <!-- Mode Collectif -->
                            <label 
                                style="border-radius: var(--border-radius-md); padding: 1.25rem; border: 2px solid var(--border-color); display: flex; align-items: flex-start; gap: 1rem; cursor: pointer; transition: all var(--transition-normal);"
                                :style="form.mode === 'collectif' ? 'border-color: var(--color-primary); background: var(--color-bg-light);' : ''"
                            >
                                <input 
                                    type="radio" 
                                    v-model="form.mode" 
                                    value="collectif" 
                                    style="accent-color: var(--color-primary); margin-top: 0.25rem;"
                                />
                                <div>
                                    <span style="font-weight: 800; font-size: 0.95rem; display: block; color: var(--color-text-main);">👥 Mode Collectif</span>
                                    <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block; margin-top: 0.25rem; line-height: 1.3;">
                                        Tous les joueurs explorent ensemble et partagent le même lobby de jeu.
                                    </span>
                                </div>
                            </label>

                            <!-- Mode Mercenaire -->
                            <label 
                                style="border-radius: var(--border-radius-md); padding: 1.25rem; border: 2px solid var(--border-color); display: flex; align-items: flex-start; gap: 1rem; cursor: pointer; transition: all var(--transition-normal);"
                                :style="form.mode === 'mercenaire' ? 'border-color: var(--color-primary); background: var(--color-bg-light);' : ''"
                            >
                                <input 
                                    type="radio" 
                                    v-model="form.mode" 
                                    value="mercenaire" 
                                    style="accent-color: var(--color-primary); margin-top: 0.25rem;"
                                />
                                <div>
                                    <span style="font-weight: 800; font-size: 0.95rem; display: block; color: var(--color-text-main);">🦊 Mode Mercenaire</span>
                                    <span style="font-size: 0.75rem; color: var(--color-text-muted); display: block; margin-top: 0.25rem; line-height: 1.3;">
                                        Chaque joueur joue en solo ou en compétition individuelle.
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Section 2 : Configuration du parcours -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <!-- Difficulté -->
                        <div>
                            <label class="premium-label" style="font-weight: 700;">Difficulté requise</label>
                            <select v-model="form.difficulty" class="premium-input" required>
                                <option value="enfant">Enfant 👶</option>
                                <option value="facile">Facile 🏹</option>
                                <option value="moyen">Moyen 🦁</option>
                                <option value="difficile">Difficile 👑</option>
                            </select>
                        </div>

                        <!-- Moyen de transport -->
                        <div>
                            <label class="premium-label" style="font-weight: 700;">Moyen de transport conseillé</label>
                            <select v-model="form.locomotion" class="premium-input" required>
                                <option value="marche">🚶 Marche à pied</option>
                                <option value="velo">🚲 Vélo / VTT</option>
                                <option value="moto">🛵 Moto / Zem</option>
                                <option value="voiture">🚗 Voiture</option>
                            </select>
                        </div>
                    </div>

                    <!-- Section 3 : Limites & Expiration -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <!-- Durée en minutes -->
                        <div>
                            <label class="premium-label" style="font-weight: 700;">Durée du jeu (min)</label>
                            <input type="number" v-model="form.duration_minutes" class="premium-input" min="15" required />
                        </div>

                        <!-- Nombre max de joueurs -->
                        <div>
                            <label class="premium-label" style="font-weight: 700;">Joueurs max (facultatif)</label>
                            <input type="number" v-model="form.max_players" class="premium-input" min="1" placeholder="Illimité" />
                        </div>

                        <!-- Expiration du token en heures -->
                        <div>
                            <label class="premium-label" style="font-weight: 700;">Durée de validité (h)</label>
                            <input type="number" v-model="form.expires_in_hours" class="premium-input" min="1" required />
                        </div>
                    </div>

                    <div style="margin-top: 1rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; display: flex; justify-content: flex-end;">
                        <button 
                            type="submit" 
                            class="premium-btn premium-btn-primary" 
                            style="width: 100%; font-family: var(--font-family-display); font-weight: 800; text-transform: uppercase; font-size: 1rem; padding: 1rem; box-shadow: var(--shadow-glow);"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Génération de l\'invitation...' : '✨ Générer l\'Invitation & le QR Code' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </AdminLayout>
</template>
