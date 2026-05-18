<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cities: {
        type: Array,
        default: () => []
    },
    errors: {
        type: Object,
        default: () => ({})
    },
});

const isModalOpen = ref(false);
const isEditing = ref(false);
let resetTimeout = null;

const form = useForm({
    id: null,
    name: '',
    description: '',
});

const openCreateModal = () => {
    if (resetTimeout) clearTimeout(resetTimeout);
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (city) => {
    if (resetTimeout) clearTimeout(resetTimeout);
    isEditing.value = true;
    form.id = city.id;
    form.name = city.name;
    form.description = city.description;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    // On attend la fin de l'animation CSS avant de reset le contenu visuel
    resetTimeout = setTimeout(() => {
        form.reset();
        form.clearErrors();
    }, 300);
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.cities.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.cities.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCity = (cityId) => {
    if (confirm('Voulez-vous vraiment supprimer ce parcours ? Cette action est irréversible.')) {
        router.delete(route('admin.cities.destroy', cityId));
    }
};

const publish = (cityId) => {
    router.post(route('admin.cities.publish', cityId));
};

const unpublish = (cityId) => {
    router.post(route('admin.cities.unpublish', cityId));
};
</script>

<template>

    <Head title="CityPlay - Liste des Parcours" />

    <AdminLayout>
        <template #header>
            <div
                style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; width: 100%; padding: 0.5rem 0;">
                <div style="display: flex; flex-direction: column; justify-content: center;">
                    <h2
                        style="font-family: var(--font-family-display); font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark); margin: 0; line-height: 1.2;">
                        À la découverte d'une ville
                    </h2>
                    <p
                        style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0.35rem 0 0 0; line-height: 1;">
                        Identité Culturelle & Exploration du Bénin
                    </p>
                </div>
                <button @click="openCreateModal" class="premium-btn premium-btn-primary"
                    style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; height: fit-content; padding: 0.75rem 1.25rem; margin: 0;">
                    <span style="font-size: 1.2rem; line-height: 1; font-weight: 700;">+</span>
                    <span>Créer une aventure</span>
                </button>
            </div>
        </template>

        <div class="premium-container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
            <!-- Alertes d'erreur de publication globale -->
            <div v-if="errors && errors.publish"
                style="background: var(--color-danger-bg); border-left: 4px solid var(--color-danger); padding: 1rem; margin-bottom: 2rem; border-radius: var(--border-radius-md);">
                <span style="font-weight: 700; color: var(--color-danger);">Erreur de publication :</span>
                <p style="color: var(--color-danger); margin-top: 0.25rem; font-size: 0.9rem;">{{ errors.publish }}</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem; align-items: start;">

                <!-- Liste des Villes / Parcours -->
                <div>
                    <h3
                        style="font-family: var(--font-family-display); font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--color-text-main); display: flex; align-items: center; gap: 0.5rem;">
                        <span>📍</span> Villes à découvrir
                    </h3>

                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                        <!-- Carte Ajouter -->
                        <div @click="openCreateModal" class="premium-card"
                            style="display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; border: 2px dashed var(--color-primary-light); background: transparent; min-height: 380px; box-shadow: none; transition: all var(--transition-normal); border-radius: var(--border-radius-md);">
                            <div
                                style="width: 56px; height: 56px; border-radius: 50%; background: var(--color-bg-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1rem; box-shadow: var(--shadow-sm); font-weight: 700;">
                                +
                            </div>
                            <h3
                                style="color: var(--color-primary); font-weight: 700; font-family: var(--font-family-display); font-size: 1.1rem; margin: 0;">
                                Nouveau Parcours</h3>
                            <p
                                style="font-size: 0.75rem; color: var(--color-text-muted); text-align: center; margin-top: 0.5rem; padding: 0 1.5rem;">
                                Créez une nouvelle expérience immersive au Bénin.</p>
                        </div>

                        <!-- Cartes des villes -->
                        <div v-for="city in cities" :key="city.id" class="premium-card"
                            style="padding: 0; overflow: hidden; display: flex; flex-direction: column; height: 380px; position: relative; border-radius: var(--border-radius-md);">
                            <!-- Pattern Visuel -->
                            <div
                                style="height: 120px; background: linear-gradient(135deg, var(--color-primary-dark), var(--color-primary)); position: relative; display: flex; align-items: flex-end; padding: 1rem;">
                                <div
                                    style="position: absolute; inset: 0; opacity: 0.15; background-image: radial-gradient(var(--color-secondary) 1.5px, transparent 1.5px), radial-gradient(var(--color-secondary) 1.5px, var(--color-primary-dark) 1.5px); background-size: 24px 24px; background-position: 0 0, 12px 12px;">
                                </div>
                                <span :class="['premium-badge', city.is_published ? 'badge-success' : 'badge-warning']"
                                    style="position: absolute; top: 1rem; right: 1rem; z-index: 2; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.05em;">
                                    {{ city.is_published ? 'Publié' : 'Brouillon' }}
                                </span>
                                <h3
                                    style="font-family: var(--font-family-display); font-size: 1.35rem; font-weight: 800; color: white; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.3); z-index: 1;">
                                    {{ city.name }}
                                </h3>
                            </div>

                            <!-- Contenu de la carte -->
                            <div
                                style="padding: 1.25rem; display: flex; flex-direction: column; flex: 1; justify-content: space-between;">
                                <p
                                    style="color: var(--color-text-muted); font-size: 0.85rem; line-height: 1.5; margin: 0 0 1rem 0; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ city.description }}
                                </p>

                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem;">
                                    <div
                                        style="background: var(--color-bg-light); padding: 0.5rem 0.75rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-size: 1.1rem;">📍</span>
                                        <div>
                                            <span
                                                style="display: block; font-size: 0.65rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 700;">Lieux</span>
                                            <span
                                                style="display: block; font-size: 0.9rem; font-weight: 800; color: var(--color-text-main);">{{
                                                city.places_count || 0 }} étapes</span>
                                        </div>
                                    </div>
                                    <div
                                        style="background: var(--color-bg-light); padding: 0.5rem 0.75rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-size: 1.1rem;">⚡</span>
                                        <div>
                                            <span
                                                style="display: block; font-size: 0.65rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 700;">Difficulté</span>
                                            <span
                                                style="display: block; font-size: 0.9rem; font-weight: 800; color: var(--color-primary-dark);">Moyenne</span>
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button @click.stop="openEditModal(city)"
                                            class="premium-btn premium-btn-outline"
                                            style="flex: 1; padding: 0.5rem; font-size: 0.8rem; border-color: var(--border-color); color: var(--color-text-muted);"
                                            title="Modifier les infos">✏️ Modifier</button>
                                        <button @click.stop="deleteCity(city.id)" class="premium-btn premium-btn-danger"
                                            style="padding: 0.5rem; font-size: 0.8rem;"
                                            title="Supprimer le parcours">🗑️</button>
                                    </div>
                                    <button v-if="!city.is_published" @click.stop="publish(city.id)"
                                        class="premium-btn premium-btn-primary"
                                        style="width: 100%; font-size: 0.8rem; padding: 0.6rem;">
                                        🚀 Publier le parcours
                                    </button>
                                    <button v-else @click.stop="unpublish(city.id)"
                                        class="premium-btn premium-btn-outline"
                                        style="width: 100%; font-size: 0.8rem; padding: 0.6rem; color: var(--color-danger); border-color: var(--color-danger);">
                                        🔒 Retirer de l'App
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Création / Édition -->
        <div v-if="isModalOpen" class="premium-modal-backdrop" @click.self="closeModal"
            style="backdrop-filter: blur(6px); background: rgba(28,24,22,0.6); position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 100;">
            <div class="premium-modal-content"
                style="max-width: 480px; width: 100%; background: white; padding: 1.5rem; border-radius: var(--border-radius-lg); border: 2px solid var(--border-color); box-shadow: var(--shadow-premium);">
                <div
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                    <div>
                        <span
                            style="font-size: 0.75rem; color: var(--color-primary); font-weight: 800; text-transform: uppercase;">CityPlay
                            Créateur</span>
                        <h3
                            style="font-family: var(--font-family-display); font-size: 1.35rem; font-weight: 800; margin: 0.1rem 0 0 0; color: var(--color-text-main);">
                            {{ isEditing ? 'Éditer le parcours' : 'Nouveau parcours' }}
                        </h3>
                    </div>
                    <button type="button" @click="closeModal"
                        style="background: var(--color-bg-light); border: 1px solid var(--border-color); border-radius: 50%; width: 32px; height: 32px; font-size: 1.1rem; cursor: pointer; color: var(--color-text-muted); display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>

                <form @submit.prevent="submitForm" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <div>
                        <label class="premium-label"
                            style="font-weight: 700; color: var(--color-primary-dark); display: block; margin-bottom: 0.35rem;">Nom
                            de l'aventure</label>
                        <input v-model="form.name" type="text" class="premium-input" maxlength="150" required
                            placeholder="Ex: Les secrets de Ouidah" />
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem;">
                            <span style="font-size: 0.65rem; color: var(--color-text-muted);">Titre court et
                                accrocheur</span>
                            <span style="font-size: 0.7rem; font-weight: 600;"
                                :style="{ color: (form.name || '').length > 140 ? 'var(--color-danger)' : 'var(--color-text-muted)' }">
                                {{ (form.name || '').length }} / 150
                            </span>
                        </div>
                        <p v-if="form.errors.name"
                            style="color: var(--color-danger); font-size: 0.85rem; margin-top: 0.25rem;">
                            {{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="premium-label"
                            style="font-weight: 700; color: var(--color-primary-dark); display: block; margin-bottom: 0.35rem;">Présentation
                            narrative (max 500 car.)</label>
                        <textarea v-model="form.description" class="premium-input" rows="5" maxlength="500" required
                            placeholder="Introduisez les mystères de cette ville et l'intrigue historique..."></textarea>
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem;">
                            <span style="font-size: 0.65rem; color: var(--color-text-muted);">Sera lue par le joueur sur
                                l'écran
                                d'accueil</span>
                            <span style="font-size: 0.7rem; font-weight: 600;"
                                :style="{ color: (form.description || '').length > 480 ? 'var(--color-danger)' : 'var(--color-text-muted)' }">
                                {{ (form.description || '').length }} / 500
                            </span>
                        </div>
                        <p v-if="form.errors.description"
                            style="color: var(--color-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{
                            form.errors.description }}</p>
                    </div>

                    <div
                        style="display: flex; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <button type="button" @click="closeModal" class="premium-btn premium-btn-outline"
                            style="flex: 1; border-color: var(--border-color); color: var(--color-text-muted);">Annuler</button>
                        <button type="submit" class="premium-btn premium-btn-primary"
                            style="flex: 2; box-shadow: 0 4px 10px rgba(200,92,50,0.25);" :disabled="form.processing">
                            {{ form.processing ? 'Enregistrement...' : 'Sauvegarder' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AdminLayout>
</template>