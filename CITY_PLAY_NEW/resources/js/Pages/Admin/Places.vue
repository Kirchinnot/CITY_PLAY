<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

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

const props = defineProps({
    places: Array,
    cities: Array,
    filters: Object,
});

const showForm = ref(false);
const imagePreviews = ref([]);
const activePreviewPlace = ref(null);

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
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap contributors © CARTO'
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

const selectPlacePreview = (place) => {
    activePreviewPlace.value = activePreviewPlace.value?.id === place.id ? null : place;
    if (activePreviewPlace.value) {
        setTimeout(() => {
            document.getElementById('mobile-gps-preview-frame')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 50);
    }
};

// On initialise la carte quand on affiche le formulaire
watch(showForm, (val) => {
    if (val) {
        setTimeout(initMap, 100);
    }
});
</script>

<template>
    <Head title="CityPlay - Gestion des Étapes" />

    <AuthenticatedLayout>
        <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; width: 100%;">
                <div>
                    <h2 style="font-family: var(--font-family-display); font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark); margin: 0;">
                        Constructeur d'Étapes
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0.25rem 0 0 0;">Points d'intérêt géolocalisés & Geofencing GPS</p>
                </div>
                <button
                    @click="showForm = !showForm"
                    :class="['premium-btn', showForm ? 'premium-btn-outline' : 'premium-btn-primary']"
                    style="gap: 0.5rem;"
                >
                    {{ showForm ? 'Annuler' : '+ Ajouter un lieu' }}
                </button>
            </div>
        </template>

        <div class="premium-container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
            <!-- Formulaire d'ajout (Split Layout si actif) -->
            <div v-if="showForm" style="display: grid; grid-template-columns: 1fr; lg:grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem; align-items: stretch;">
                
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
                            <input
                                type="file"
                                multiple
                                accept="image/jpeg,image/png"
                                @input="handleImageUpload"
                                style="width: 100%; padding: 0.5rem; background: var(--color-bg-light); border-radius: var(--border-radius-md); border: 1px solid var(--border-color); font-size: 0.85rem;"
                            />
                            
                            <!-- Prévisualisation des images sélectionnées -->
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

            <!-- Double Colonne : Liste des étapes existantes et Simulation GPS Mobile -->
            <div style="display: grid; grid-template-columns: 1fr; lg:grid-template-columns: 2fr 1fr; gap: 2.5rem; align-items: start;">
                
                <!-- Liste des étapes -->
                <div>
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
                                    <button
                                        @click="deleteImage(img.id)"
                                        style="position: absolute; top: -2px; right: -2px; background: var(--color-danger); color: white; border: none; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; font-size: 10px; cursor: pointer; box-shadow: var(--shadow-sm);"
                                        title="Supprimer cette image"
                                    >
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

                            <div style="margin-top: auto; display: grid; grid-template-columns: 1fr auto auto; gap: 0.5rem;">
                                <Link
                                    :href="route('admin.riddles.index', place.id)"
                                    class="premium-btn premium-btn-outline"
                                    style="text-decoration: none; text-align: center; font-size: 0.75rem; padding: 0.6rem;"
                                >
                                    🎭 Gérer les énigmes
                                </Link>
                                <button
                                    @click="selectPlacePreview(place)"
                                    class="premium-btn premium-btn-outline"
                                    style="padding: 0.5rem; font-size: 0.75rem; border-color: var(--color-secondary); color: var(--color-secondary);"
                                    title="Simuler sur mobile"
                                >
                                    📱 Aperçu GPS
                                </button>
                                <button
                                    @click="deletePlace(place.id)"
                                    class="premium-btn premium-btn-danger"
                                    style="padding: 0.5rem; aspect-ratio: 1;"
                                    title="Supprimer ce lieu"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 1.1rem; height: 1.1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Simulation GPS Écran Mobile -->
                <div id="mobile-gps-preview-frame">
                    <h3 style="font-family: var(--font-family-display); font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--color-text-main); display: flex; align-items: center; gap: 0.5rem;">
                        <span style="color: var(--color-secondary);">📱</span> Rendu Boussole / GPS
                    </h3>

                    <!-- Simulated Smartphone Frame -->
                    <div style="width: 100%; max-width: 340px; margin: 0 auto; background: var(--color-bg-dark); border: 10px solid #111; border-radius: var(--border-radius-xl); box-shadow: var(--shadow-premium); overflow: hidden; position: relative;">
                        <!-- Speaker Notch -->
                        <div style="width: 110px; height: 18px; background: #111; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; margin: 0 auto; position: absolute; left: 50%; transform: translateX(-50%); z-index: 10; display: flex; justify-content: center; align-items: center;">
                            <div style="width: 40px; height: 3px; background: #333; border-radius: 2px;"></div>
                        </div>

                        <!-- Screen Content -->
                        <div style="background: var(--color-bg-light); color: var(--color-text-main); font-family: var(--font-family-sans); min-height: 520px; max-height: 520px; overflow-y: auto; padding-top: 18px; scrollbar-width: none; display: flex; flex-direction: column;">
                            
                            <div v-if="!activePreviewPlace" style="padding: 2.5rem 1.5rem 1.5rem 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; min-height: 480px;">
                                <span style="font-size: 3rem;">🧭</span>
                                <h4 style="font-family: var(--font-family-display); font-size: 1.25rem; font-weight: 800; color: var(--color-primary-dark); margin: 1rem 0 0.5rem 0;">Simulation GPS</h4>
                                <p style="font-size: 0.8rem; color: var(--color-text-muted); line-height: 1.4; margin: 0;">Sélectionnez "Aperçu GPS" sur l'un de vos lieux pour simuler le gameplay de géolocalisation.</p>
                            </div>

                            <div v-else style="display: flex; flex-direction: column; flex: 1; justify-content: space-between; height: 100%;">
                                <!-- Immersive Screen 8: Interface GPS / carte -->
                                <div style="padding: 1rem; background: var(--color-surface-light); border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <span style="font-size: 0.65rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase;">Aventure active</span>
                                        <h5 style="margin: 0; font-family: var(--font-family-display); font-size: 0.85rem; font-weight: 800; color: var(--color-primary-dark);">{{ activePreviewPlace.city.name }}</h5>
                                    </div>
                                    <div style="background: var(--color-success-bg); color: var(--color-success); font-size: 0.65rem; padding: 0.25rem 0.5rem; border-radius: var(--border-radius-sm); font-weight: 800;">
                                        Étape {{ activePreviewPlace.order_index }}
                                    </div>
                                </div>

                                <!-- Simulated Map Section (Using Place Images or visual compass mock) -->
                                <div style="flex: 1; background: #e0dacf; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; height: 220px;">
                                    <!-- Simulated radar background -->
                                    <div style="position: absolute; width: 160px; height: 160px; border: 2px solid var(--color-primary-light); border-radius: 50%; opacity: 0.2; animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
                                    <div style="position: absolute; width: 100px; height: 100px; border: 1.5px dashed var(--color-primary); border-radius: 50%; opacity: 0.4;"></div>
                                    
                                    <!-- Compass Pointer -->
                                    <div style="z-index: 2; display: flex; flex-direction: column; align-items: center;">
                                        <span style="font-size: 2.5rem; transform: rotate(45deg); display: inline-block; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));">🧭</span>
                                        <span style="font-size: 0.85rem; font-weight: 800; color: var(--color-primary-dark); margin-top: 0.5rem; font-family: var(--font-family-display);">28 mètres</span>
                                        <span style="font-size: 0.55rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase;">Zone de Geofencing proche</span>
                                    </div>

                                    <!-- Immersive Benin Pattern overlay on sides -->
                                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(28,24,22,0.9), transparent); padding: 0.75rem 1rem; color: white;">
                                        <span style="font-size: 0.7rem; opacity: 0.8;">Cible identifiée</span>
                                        <h6 style="margin: 0; font-family: var(--font-family-display); font-size: 1rem; font-weight: 800;">{{ activePreviewPlace.name }}</h6>
                                    </div>
                                </div>

                                <!-- Bottom Info / Action Drawer -->
                                <div style="background: var(--color-surface-light); padding: 1.25rem; border-top: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 48px; height: 48px; border-radius: var(--border-radius-sm); overflow: hidden; border: 1px solid var(--border-color); flex-shrink: 0; background: #eee;">
                                            <img v-if="activePreviewPlace.images && activePreviewPlace.images.length > 0" :src="activePreviewPlace.images[0].image_url" style="width: 100%; height: 100%; object-fit: cover;" />
                                            <span v-else style="font-size: 1.5rem; display: flex; align-items: center; justify-content: center; height: 100%;">🎭</span>
                                        </div>
                                        <div>
                                            <span style="display: block; font-size: 0.65rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase;">Indice narratif</span>
                                            <p style="margin: 0; font-size: 0.7rem; color: var(--color-text-main); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ activePreviewPlace.description || 'Aucun indice fourni.' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Big validation radius circle info -->
                                    <div style="background: var(--color-bg-light); border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: 0.5rem 0.75rem; text-align: center; font-size: 0.65rem;">
                                        ⚡ Rayon de tolérance requis : <span style="font-weight: 800; color: var(--color-primary-dark);">{{ activePreviewPlace.validation_radius }} mètres</span>
                                    </div>

                                    <!-- Action Button simulated -->
                                    <Link :href="route('admin.riddles.index', activePreviewPlace.id)" class="premium-btn premium-btn-primary" style="width: 100%; padding: 0.75rem; font-size: 0.8rem; font-family: var(--font-family-display); font-weight: 800; text-align: center; text-decoration: none;">
                                        🔑 RÉSOUDRE L'ÉNIGME
                                    </Link>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes ping {
    0% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    100% {
        transform: scale(1.4);
        opacity: 0;
    }
}
.leaflet-container {
    z-index: 1 !important;
    font-family: var(--font-family-sans);
    border-radius: var(--border-radius-sm);
}
</style>
