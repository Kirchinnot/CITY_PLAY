<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onBeforeUnmount } from 'vue';

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
    lat: 6.3654,  // Centré par défaut sur Cotonou, Bénin
    lng: 2.4406,
    validation_radius: 30, // Par défaut 30m requis pour le geofencing
    order_index: 1,
    images: null,
});

// Références pour Leaflet instances
let mapInstance = null;
let markerInstance = null;

const handleImageUpload = (e) => {
    form.images = e.target.files;
    imagePreviews.value = [];
    if (e.target.files) {
        Array.from(e.target.files).forEach(file => {
            imagePreviews.value.push(URL.createObjectURL(file));
        });
    }
};

const initMap = () => {
    // Évite les doublons d'initialisation si l'élément n'est pas encore rendu
    const mapContainer = document.getElementById('map');
    if (!mapContainer || mapInstance) return;

    mapInstance = L.map('map').setView([form.lat, form.lng], 13);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap contributors © CARTO'
    }).addTo(mapInstance);

    markerInstance = L.marker([form.lat, form.lng], { draggable: true }).addTo(mapInstance);

    // Événements du marqueur et du clic sur la carte
    markerInstance.on('dragend', (e) => {
        const { lat, lng } = e.target.getLatLng();
        form.lat = lat.toFixed(6);
        form.lng = lng.toFixed(6);
    });

    mapInstance.on('click', (e) => {
        const { lat, lng } = e.latlng;
        form.lat = lat.toFixed(6);
        form.lng = lng.toFixed(6);
        markerInstance.setLatLng(e.latlng);
    });
};

const destroyMap = () => {
    if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
        markerInstance = null;
    }
};

const submit = () => {
    form.post(route('admin.places.store'), {
        onSuccess: () => {
            showForm.value = false;
            form.reset();
            imagePreviews.value = [];
            destroyMap();
        },
    });
};

const deleteImage = (imageId) => {
    if (confirm('Supprimer cette image ?')) {
        router.delete(route('admin.place-images.destroy', imageId));
    }
};

const deletePlace = (placeId) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce lieu ? Cela supprimera également ses énigmes et images.')) {
        router.delete(route('admin.places.destroy', placeId));
    }
};

// Surveillance de l'affichage du formulaire pour instancier/détruire la carte
watch(showForm, (isVisible) => {
    if (isVisible) {
        // Un délai minimal permet à Vue d'injecter le div#map dans le DOM via v-if
        setTimeout(initMap, 150);
    } else {
        destroyMap();
    }
});

// Nettoyage de sécurité si l'admin quitte la page brusquement
onBeforeUnmount(() => {
    destroyMap();
});
</script>

<template>
    <Head title="CityPlay - Gestion des Étapes" />

    <AdminLayout>
        <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; width: 100%;">
                <div>
                    <h2 style="font-family: var(--font-family-display); font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark); margin: 0;">
                        Constructeur d'Étapes
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0.25rem 0 0 0;">
                        Points d'intérêt géolocalisés & Geofencing GPS
                    </p>
                </div>
                <button @click="showForm = !showForm"
                        :class="['premium-btn', showForm ? 'premium-btn-outline' : 'premium-btn-primary']"
                        style="gap: 0.5rem;">
                    {{ showForm ? 'Annuler' : '+ Ajouter un lieu' }}
                </button>
            </div>
        </template>

        <div class="premium-container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
            
            <!-- Formulaire d'ajout (Split Layout) -->
            <div v-if="showForm"
                 style="display: grid; grid-template-columns: 1fr; gap: 2rem; margin-bottom: 3rem; align-items: stretch;"
                 class="lg:grid-cols-2">

                <!-- Colonne Gauche: Formulaire -->
                <div class="premium-card" style="display: flex; flex-direction: column; border: 1px solid var(--border-color);">
                    <h3 style="font-family: var(--font-family-display); font-size: 1.25rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--color-primary-dark); display: flex; align-items: center; gap: 0.5rem;">
                        <span>📍</span> Nouveau point d'intérêt
                    </h3>

                    <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 1.25rem; flex: 1;">
                        <div>
                            <label class="premium-label">Parcours associé (Aventure)</label>
                            <select v-model="form.city_id" class="premium-input" required>
                                <option value="">Choisir un parcours du Bénin...</option>
                                <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="premium-label">Nom de l'étape / Monument</label>
                            <input v-model="form.name" type="text" class="premium-input" required placeholder="Ex: Porte du Non-Retour, Ouidah" />
                        </div>

                        <div>
                            <label class="premium-label">Description d'introduction (Indice ou Lore)</label>
                            <textarea v-model="form.description" rows="3" class="premium-input" placeholder="Décrivez l'importance historique et donnez de subtils indices de recherche..."></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label class="premium-label">Rayon de validation (mètres)</label>
                                <input v-model="form.validation_radius" type="number" class="premium-input" min="5" placeholder="Par ex: 30" />
                            </div>
                            <div>
                                <label class="premium-label">Ordre de passage</label>
                                <input v-model="form.order_index" type="number" class="premium-input" min="1" />
                            </div>
                        </div>

                        <div style="background: var(--color-bg-light); border: 1px dashed var(--border-color); padding: 1rem; border-radius: var(--border-radius-md); display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <div style="font-size: 0.85rem; font-weight: 700; color: var(--color-primary);">Positionnement GPS</div>
                                <div style="font-size: 0.75rem; color: var(--color-text-muted); font-family: monospace;">{{ form.lat }}, {{ form.lng }}</div>
                            </div>
                            <span class="premium-badge badge-warning" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase;">Sélectionnez sur la carte</span>
                        </div>

                        <div>
                            <label class="premium-label">Photos touristiques (Sélection multiple)</label>
                            <input type="file" multiple accept="image/jpeg,image/png" @input="handleImageUpload" style="width: 100%; padding: 0.5rem; background: var(--color-bg-light); border-radius: var(--border-radius-md); border: 1px solid var(--border-color); font-size: 0.85rem;" />

                            <!-- Prévisualisation -->
                            <div v-if="imagePreviews.length > 0" style="display: flex; gap: 0.5rem; margin-top: 0.75rem; flex-wrap: wrap;">
                                <div v-for="(preview, idx) in imagePreviews" :key="idx" style="width: 60px; height: 60px; border-radius: var(--border-radius-sm); overflow: hidden; border: 1px solid var(--border-color); position: relative;">
                                    <img :src="preview" style="width: 100%; height: 100%; object-fit: cover;" />
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: auto; padding-top: 1.5rem;">
                            <button type="submit" :disabled="form.processing" class="premium-btn premium-btn-primary" style="width: 100%; font-family: var(--font-family-display); font-weight: 800;">
                                {{ form.processing ? 'Envoi des médias sur Cloudinary...' : '💾 Enregistrer l\'étape' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Colonne Droite: Carte Leaflet -->
                <div class="premium-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; border: 1px solid var(--border-color); min-height: 480px;">
                    <div style="padding: 1rem; background: var(--color-surface-light); border-bottom: 1px solid var(--border-color);">
                        <h4 style="margin: 0; font-family: var(--font-family-display); font-size: 1rem; font-weight: 800; color: var(--color-primary-dark);">Carte Géographique interactive</h4>
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.75rem; color: var(--color-text-muted);">Cliquez n'importe où pour y placer l'étape ou glissez le marqueur.</p>
                    </div>
                    <div id="map" style="flex: 1; width: 100%; min-height: 400px; z-index: 1;"></div>
                </div>
            </div>

            <!-- Liste des étapes existantes -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem; align-items: start;" class="lg:grid-cols-3">
                
                <!-- Liste des étapes (Prend 2 colonnes sur grand écran) -->
                <div class="lg:col-span-2">
                    <h3 style="font-family: var(--font-family-display); font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--color-text-main); display: flex; align-items: center; gap: 0.5rem;">
                        <span>🗺️</span> Les étapes créées au Bénin
                    </h3>

                    <div v-if="places.length === 0" style="text-align: center; padding: 4rem 2rem; color: var(--color-text-muted); background: var(--color-surface-light); border: 1px solid var(--border-color); border-radius: var(--border-radius-lg);">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📍</div>
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; font-family: var(--font-family-display);">Aucun lieu pour le moment</h3>
                        <p style="font-size: 0.85rem;">Commencez par ajouter une étape à l'un de vos parcours.</p>
                    </div>

                    <div v-else style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                        <div v-for="place in places" :key="place.id" class="premium-card" style="display: flex; flex-direction: column; border: 1px solid var(--border-color); transition: all var(--transition-bounce);">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-primary-light); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; box-shadow: var(--shadow-sm); font-family: var(--font-family-display);">
                                        #{{ place.order_index }}
                                    </div>
                                    <div>
                                        <h4 style="margin: 0; font-family: var(--font-family-display); font-size: 1.1rem; font-weight: 800; color: var(--color-text-main);">{{ place.name }}</h4>
                                        <span style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase;">{{ place.city.name }}</span>
                                    </div>
                                </div>
                            </div>

                            <p style="color: var(--color-text-muted); font-size: 0.8rem; line-height: 1.5; margin: 0 0 1rem 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; height: 2.4rem;">
                                {{ place.description || 'Aucune description rédigée.' }}
                            </p>

                            <!-- Galerie miniatures -->
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem; min-height: 50px; background: var(--color-bg-light); padding: 0.5rem; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color);">
                                <div v-for="img in place.images" :key="img.id" style="position: relative; border-radius: var(--border-radius-sm); overflow: hidden; border: 1px solid var(--border-color);">
                                    <img :src="img.image_url" style="height: 44px; width: 44px; object-fit: cover;" />
                                    <button @click="deleteImage(img.id)" style="position: absolute; top: -2px; right: -2px; background: var(--color-danger); color: white; border: none; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer; box-shadow: var(--shadow-sm);">
                                        &times;
                                    </button>
                                </div>
                                <div v-if="!place.images || place.images.length === 0" style="font-size: 0.75rem; color: var(--color-text-muted); font-style: italic; display: flex; align-items: center; padding-left: 0.5rem;">
                                    Aucune photo illustrée
                                </div>
                            </div>

                            <div style="background: var(--color-surface-light); border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); padding: 0.5rem 0.75rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                                    <span style="display: block; font-weight: 700; color: var(--color-primary-dark);">Geofencing : {{ place.validation_radius }}m</span>
                                    <span style="display: block; font-size: 0.65rem; font-family: monospace;">{{ place.lat }}, {{ place.lng }}</span>
                                </div>
                            </div>

                            <div style="margin-top: auto; display: grid; grid-template-columns: 1fr auto; gap: 0.5rem;">
                                <Link :href="route('admin.riddles.index', place.id)" class="premium-btn premium-btn-outline" style="text-decoration: none; text-align: center; font-size: 0.75rem; padding: 0.6rem;">
                                    🎭 Gérer les énigmes
                                </Link>
                                <button @click="deletePlace(place.id)" class="premium-btn premium-btn-danger" style="padding: 0.5rem; aspect-ratio: 1;">
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 1.1rem; height: 1.1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section filtre ou statistiques à droite optionnelle (laisse la grille propre) -->
                <div class="lg:col-span-1">
                    <!-- Peut accueillir un composant de résumé des parcours du Bénin si besoin -->
                </div>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
@keyframes ping {
    0% { transform: scale(0.8); opacity: 0.5; }
    100% { transform: scale(1.4); opacity: 0; }
}
.leaflet-container {
    z-index: 1 !important;
    font-family: var(--font-family-sans);
    border-radius: var(--border-radius-sm);
}
</style>