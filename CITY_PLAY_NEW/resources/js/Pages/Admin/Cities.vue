<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cities: Array,
    errors: Object,
});

const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    name: '',
    description: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (city) => {
    isEditing.value = true;
    form.id = city.id;
    form.name = city.name;
    form.description = city.description;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => form.reset(), 300);
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
    <Head title="Gestion des Parcours" />

    <AuthenticatedLayout>
        <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-family: var(--font-family-display); font-size: 1.5rem; font-weight: 700; color: var(--color-text-main);">
                    Gestion des Parcours
                </h2>
                <button @click="openCreateModal" class="premium-btn premium-btn-primary">
                    + Créer un parcours
                </button>
            </div>
        </template>

        <div class="premium-container">
            <!-- Alertes d'erreur -->
            <div v-if="errors.publish" style="background: var(--color-danger-bg); border-left: 4px solid var(--color-danger); padding: 1rem; margin-bottom: 2rem; border-radius: var(--border-radius-md);">
                <span style="font-weight: 700; color: var(--color-danger);">Erreur de publication :</span>
                <p style="color: var(--color-danger); margin-top: 0.5rem;">{{ errors.publish }}</p>
            </div>

            <!-- Grille des Parcours -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                <div v-for="city in cities" :key="city.id" class="premium-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <h3 style="font-size: 1.25rem; margin: 0; color: var(--color-primary-dark);">{{ city.name }}</h3>
                            <span :class="['premium-badge', city.is_published ? 'badge-success' : 'badge-warning']">
                                {{ city.is_published ? 'Publié' : 'Brouillon' }}
                            </span>
                        </div>
                        
                        <p style="color: var(--color-text-muted); font-size: 0.9rem; line-height: 1.5; margin-bottom: 1.5rem; min-height: 3rem;">
                            {{ city.description }}
                        </p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1.5rem;">
                            <div style="background: var(--color-bg-light); padding: 0.75rem; border-radius: var(--border-radius-sm); text-align: center;">
                                <span style="display: block; font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">Étapes</span>
                                <span style="display: block; font-size: 1.25rem; font-weight: 700; color: var(--color-text-main);">{{ city.places_count }}</span>
                            </div>
                            <div style="background: var(--color-bg-light); padding: 0.75rem; border-radius: var(--border-radius-sm); text-align: center;">
                                <span style="display: block; font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">Parties</span>
                                <span style="display: block; font-size: 1.25rem; font-weight: 700; color: var(--color-text-main);">{{ city.game_sessions_count }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; gap: 0.5rem;">
                            <button @click="openEditModal(city)" class="premium-btn premium-btn-outline" style="flex: 1; padding: 0.5rem;">Modifier</button>
                            <button @click="deleteCity(city.id)" class="premium-btn premium-btn-danger" style="flex: 1; padding: 0.5rem;">Supprimer</button>
                        </div>
                        <button
                            v-if="!city.is_published"
                            @click="publish(city.id)"
                            class="premium-btn premium-btn-primary"
                            style="width: 100%;"
                        >
                            Publier le parcours
                        </button>
                        <button
                            v-else
                            @click="unpublish(city.id)"
                            class="premium-btn premium-btn-outline"
                            style="width: 100%; color: var(--color-danger); border-color: var(--color-danger);"
                        >
                            Retirer de la publication
                        </button>
                    </div>
                </div>
                
                <!-- Carte Ajouter -->
                <div @click="openCreateModal" class="premium-card" style="display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; border: 2px dashed var(--color-primary-light); background: transparent; min-height: 300px; box-shadow: none;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--color-primary-light); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem;">
                        +
                    </div>
                    <h3 style="color: var(--color-primary); font-weight: 600;">Nouveau Parcours</h3>
                </div>
            </div>
        </div>

        <!-- Modal CRUD -->
        <div v-if="isModalOpen" class="premium-modal-backdrop" @click.self="closeModal">
            <div class="premium-modal-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="font-family: var(--font-family-display); font-size: 1.5rem; margin: 0; color: var(--color-text-main);">
                        {{ isEditing ? 'Modifier le parcours' : 'Nouveau parcours' }}
                    </h3>
                    <button @click="closeModal" style="background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: var(--color-text-muted);">&times;</button>
                </div>
                
                <form @submit.prevent="submitForm">
                    <div style="margin-bottom: 1.25rem;">
                        <label class="premium-label">Nom du parcours (max 150 car.)</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            class="premium-input" 
                            maxlength="150" 
                            required 
                            placeholder="Ex: Le Trésor de Lugdunum"
                        />
                        <div style="text-align: right; font-size: 0.75rem; margin-top: 0.25rem;" :style="{ color: form.name.length > 140 ? 'var(--color-danger)' : 'var(--color-text-muted)' }">
                            {{ form.name.length }} / 150
                        </div>
                        <p v-if="form.errors.name" style="color: var(--color-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{ form.errors.name }}</p>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label class="premium-label">Description de présentation (max 500 car.)</label>
                        <textarea 
                            v-model="form.description" 
                            class="premium-input" 
                            rows="4" 
                            maxlength="500" 
                            required 
                            placeholder="Décrivez l'aventure que les joueurs vont vivre..."
                        ></textarea>
                        <div style="text-align: right; font-size: 0.75rem; margin-top: 0.25rem;" :style="{ color: form.description.length > 480 ? 'var(--color-danger)' : 'var(--color-text-muted)' }">
                            {{ form.description.length }} / 500
                        </div>
                        <p v-if="form.errors.description" style="color: var(--color-danger); font-size: 0.85rem; margin-top: 0.25rem;">{{ form.errors.description }}</p>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                        <button type="button" @click="closeModal" class="premium-btn premium-btn-outline" style="border-color: var(--border-color); color: var(--color-text-main);">Annuler</button>
                        <button type="submit" class="premium-btn premium-btn-primary" :disabled="form.processing">
                            {{ form.processing ? 'Enregistrement...' : 'Sauvegarder' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
