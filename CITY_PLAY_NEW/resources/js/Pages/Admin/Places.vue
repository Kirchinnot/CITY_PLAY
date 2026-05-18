<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

const deleteImage = (imageId) => {
    if (confirm('Supprimer cette image ?')) {
        router.delete(route('admin.place-images.destroy', imageId));
    }
};

const props = defineProps({
    places: Array,
    cities: Array,
    filters: Object,
});

const showForm = ref(false);
const imagePreviews = ref([]);

const form = useForm({
    city_id: '',
    name: '',
    description: '',
    lat: 45.7597,
    lng: 4.8422,
    validation_radius: 5,
    order_index: 1,
    images: null,
});

const handleImageUpload = (e) => {
    form.images = e.target.files;
    imagePreviews.value = [];
    if (e.target.files) {
        Array.from(e.target.files).forEach(file => {
            imagePreviews.value.push(URL.createObjectURL(file));
        });
    }
};

let map = null;
let marker = null;

const initMap = () => {
    if (map) return;
    
    map = L.map('map').setView([form.lat, form.lng], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    marker = L.marker([form.lat, form.lng], { draggable: true }).addTo(map);

    marker.on('dragend', (e) => {
        const { lat, lng } = e.target.getLatLng();
        form.lat = lat.toFixed(6);
        form.lng = lng.toFixed(6);
    });

    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        form.lat = lat.toFixed(6);
        form.lng = lng.toFixed(6);
        marker.setLatLng(e.latlng);
    });
};

const submit = () => {
    form.post(route('admin.places.store'), {
        onSuccess: () => {
            showForm.value = false;
            form.reset();
            imagePreviews.value = [];
        },
    });
};

// On initialise la carte quand on affiche le formulaire
watch(showForm, (val) => {
    if (val) {
        setTimeout(initMap, 100);
    }
});
</script>

<template>
    <Head title="Constructeur d'Étapes" />

    <AuthenticatedLayout>
        <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-family: var(--font-family-display); font-size: 1.5rem; font-weight: 700; color: var(--color-text-main);">
                    Constructeur d'Étapes (Lieux)
                </h2>
                <button
                    @click="showForm = !showForm"
                    :class="['premium-btn', showForm ? 'premium-btn-outline' : 'premium-btn-primary']"
                >
                    {{ showForm ? 'Annuler' : '+ Ajouter un lieu' }}
                </button>
            </div>
        </template>

        <div class="premium-container">
            <!-- Formulaire d'ajout (Split Layout si actif) -->
            <div v-if="showForm" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; align-items: stretch;">
                
                <!-- Colonne Gauche: Formulaire -->
                <div class="premium-card" style="display: flex; flex-direction: column;">
                    <h3 style="font-family: var(--font-family-display); font-size: 1.25rem; margin-bottom: 1.5rem; color: var(--color-primary-dark);">
                        Nouveau point d'intérêt
                    </h3>
                    
                    <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 1.25rem; flex: 1;">
                        <div>
                            <label class="premium-label">Parcours (Ville)</label>
                            <select v-model="form.city_id" class="premium-input" required>
                                <option value="">Sélectionner une ville</option>
                                <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="premium-label">Nom du lieu</label>
                            <input v-model="form.name" type="text" class="premium-input" required placeholder="Ex: Place Bellecour" />
                        </div>

                        <div>
                            <label class="premium-label">Description (Indice de recherche)</label>
                            <textarea v-model="form.description" rows="3" class="premium-input" placeholder="Où se trouve cet endroit ?"></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label class="premium-label">Rayon Geofencing (m)</label>
                                <input v-model="form.validation_radius" type="number" class="premium-input" min="1" />
                            </div>
                            <div>
                                <label class="premium-label">Ordre de visite</label>
                                <input v-model="form.order_index" type="number" class="premium-input" min="1" />
                            </div>
                        </div>

                        <div style="background: var(--color-bg-light); border: 1px dashed var(--border-color); padding: 1rem; border-radius: var(--border-radius-md); display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: 0.85rem; font-weight: 600; color: var(--color-primary);">Coordonnées GPS</div>
                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">{{ form.lat }}, {{ form.lng }}</div>
                            </div>
                            <span class="premium-badge badge-warning" style="font-size: 0.65rem;">Cliquez sur la carte</span>
                        </div>

                        <div>
                            <label class="premium-label">Photos du lieu</label>
                            <input
                                type="file"
                                multiple
                                accept="image/jpeg,image/png"
                                @input="handleImageUpload"
                                style="width: 100%; padding: 0.5rem; background: var(--color-bg-light); border-radius: var(--border-radius-md); border: 1px solid var(--border-color); font-size: 0.85rem;"
                            />
                            <p style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.25rem;">JPEG/PNG, Max 2Mo par fichier. Vous pouvez sélectionner plusieurs images.</p>
                            
                            <!-- Prévisualisation des images sélectionnées -->
                            <div v-if="imagePreviews.length > 0" style="display: flex; gap: 0.5rem; margin-top: 0.75rem; flex-wrap: wrap;">
                                <div v-for="(preview, idx) in imagePreviews" :key="idx" style="width: 60px; height: 60px; border-radius: var(--border-radius-sm); overflow: hidden; border: 1px solid var(--border-color); position: relative;">
                                    <img :src="preview" style="width: 100%; height: 100%; object-fit: cover;" />
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: auto; padding-top: 1.5rem; display: flex; justify-content: flex-end;">
                            <button type="submit" :disabled="form.processing" class="premium-btn premium-btn-primary" style="width: 100%;">
                                {{ form.processing ? 'Upload en cours...' : 'Enregistrer le lieu' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Colonne Droite: Carte Leaflet -->
                <div class="premium-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                    <div style="padding: 1rem; background: var(--color-surface-light); border-bottom: 1px solid var(--border-color);">
                        <h4 style="margin: 0; font-size: 1rem; font-weight: 600; color: var(--color-text-main);">Carte Interactive</h4>
                        <p style="margin: 0; font-size: 0.75rem; color: var(--color-text-muted);">Positionnez le marqueur ou cliquez sur la carte.</p>
                    </div>
                    <div id="map" style="flex: 1; width: 100%; min-height: 400px; z-index: 1;"></div>
                </div>
            </div>

            <!-- Liste des lieux (Cards) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                <div v-for="place in places" :key="place.id" class="premium-card" style="display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--color-primary-light); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.25rem;">
                                {{ place.order_index }}
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--color-text-main);">{{ place.name }}</h4>
                                <span style="font-size: 0.8rem; color: var(--color-text-muted);">{{ place.city.name }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem; min-height: 50px;">
                        <div v-for="img in place.images" :key="img.id" style="position: relative; border-radius: var(--border-radius-sm); overflow: hidden; border: 1px solid var(--border-color);">
                            <img :src="img.image_url" style="height: 50px; width: 50px; object-fit: cover;" />
                            <button
                                @click="deleteImage(img.id)"
                                style="position: absolute; top: 2px; right: 2px; background: var(--color-danger); color: white; border: none; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer;"
                                title="Supprimer l'image"
                            >
                                &times;
                            </button>
                        </div>
                        <div v-if="!place.images || place.images.length === 0" style="font-size: 0.8rem; color: var(--color-text-muted); font-style: italic; display: flex; align-items: center;">
                            Aucune image
                        </div>
                    </div>

                    <div style="background: var(--color-bg-light); border-radius: var(--border-radius-sm); padding: 0.75rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                            <span style="display: block; font-weight: 600;">Geofencing : {{ place.validation_radius }}m</span>
                            <span style="display: block;">{{ place.lat }}, {{ place.lng }}</span>
                        </div>
                    </div>

                    <div style="margin-top: auto;">
                        <Link
                            :href="route('admin.riddles.index', place.id)"
                            class="premium-btn premium-btn-outline"
                            style="width: 100%; text-decoration: none; text-align: center;"
                        >
                            Gérer les énigmes
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="places.length === 0" style="text-align: center; padding: 4rem 2rem; color: var(--color-text-muted);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📍</div>
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">Aucun lieu pour le moment</h3>
                <p>Commencez par ajouter une étape à l'un de vos parcours.</p>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Leaflet Reset pour éviter les conflits z-index */
.leaflet-container {
    z-index: 1 !important;
    font-family: var(--font-family-sans);
}
</style>
