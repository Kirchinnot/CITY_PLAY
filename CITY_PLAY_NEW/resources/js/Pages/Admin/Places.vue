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

const copiedCoords = ref(false);

const copyCoords = async () => {
    try {
        await navigator.clipboard.writeText(`${form.lat}, ${form.lng}`);
        copiedCoords.value = true;
        setTimeout(() => copiedCoords.value = false, 1500);
    } catch (err) {
        // fallback: sélectionne le texte pour copier manuellement
        const tmp = document.createElement('textarea');
        tmp.value = `${form.lat}, ${form.lng}`;
        document.body.appendChild(tmp);
        tmp.select();
        document.execCommand('copy');
        document.body.removeChild(tmp);
        copiedCoords.value = true;
        setTimeout(() => copiedCoords.value = false, 1500);
    }
};

const removePreview = (index) => {
    // retire l'aperçu et reconstruit form.images (FileList) sans le fichier supprimé
    imagePreviews.value.splice(index, 1);
    if (!form.images) return;
    const dt = new DataTransfer();
    Array.from(form.images).forEach((file, i) => {
        if (i !== index) dt.items.add(file);
    });
    form.images = dt.files;
};

const clearPreviews = () => {
    form.images = null;
    imagePreviews.value = [];
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
            <div class="max-w-7xl mx-auto w-full flex items-center justify-between px-4 py-3">
                <div>
                    <h2 class="font-sans text-2xl font-black text-[#2D1B16] m-0 leading-tight">Constructeur d'Étapes</h2>
                    <p class="text-xs text-[#5C4033]/70 mt-1">Points d'intérêt géolocalisés & Geofencing GPS</p>
                </div>
                <button @click="showForm = !showForm"
                        :class="showForm ? 'bg-[#FFF3DF] text-[#2D1B16] border border-[#E0531C]/20' : 'bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white'"
                        class="flex items-center gap-2 px-4 py-2 rounded-2xl font-black text-xs uppercase tracking-wider transition-all duration-200 active:scale-95">
                    <span class="text-lg font-bold">+</span>
                    <span>{{ showForm ? 'Annuler' : 'Ajouter un lieu' }}</span>
                </button>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 py-6">
            
            <!-- Formulaire d'ajout (Split Layout) -->
              <div v-if="showForm" class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">

                <!-- Colonne Gauche: Formulaire -->
                <div class="bg-white rounded-2xl border border-[#E0531C]/10 p-6 flex flex-col">
                    <h3 class="flex items-center gap-3 text-base font-black text-[#2D1B16] mb-4">
                        <svg class="w-5 h-5 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.5 7-10a7 7 0 10-14 0c0 5.5 7 10 7 10z"/></svg>
                        <span>Nouveau point d'intérêt</span>
                    </h3>

                    <form @submit.prevent="submit" class="flex flex-col gap-5 flex-1">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">Parcours associé (Aventure)</label>
                            <select v-model="form.city_id" class="w-full h-11 px-3 rounded-xl border border-[#E0531C]/20 bg-white text-sm" required>
                                <option value="">Choisir un parcours du Bénin...</option>
                                <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">Nom de l'étape / Monument</label>
                            <input v-model="form.name" type="text" class="w-full h-11 px-3 rounded-xl border border-[#E0531C]/20 text-sm" required placeholder="Ex: Porte du Non-Retour, Ouidah" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">Description du lieu (max 500 car.)</label>
                            <textarea v-model="form.description" rows="3" class="w-full p-3 pl-3 rounded-xl border border-[#E0531C]/20 text-sm bg-white" maxlength="500" required placeholder="Décrivez l'importance historique et donnez de subtils indices de recherche..."></textarea>
                            <div class="flex justify-between mt-1">
                                <span class="text-[10px] text-[#5C4033]/60 italic">Affiché après la découverte du lieu</span>
                                <span :class="form.description.length > 450 ? 'text-red-500' : 'text-[#5C4033]/70'" class="text-[10px] font-bold">{{ form.description.length }}/500</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">Rayon de validation (mètres)</label>
                                <input v-model="form.validation_radius" type="number" class="w-full h-11 px-3 rounded-xl border border-[#E0531C]/20 text-sm" min="5" placeholder="Par ex: 30" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">Ordre de passage</label>
                                <input v-model="form.order_index" type="number" class="w-full h-11 px-3 rounded-xl border border-[#E0531C]/20 text-sm" min="1" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 border border-dashed border-[#E0531C]/20 rounded-xl bg-[#FFF7EB]">
                            <div>
                                <div class="text-sm font-bold text-[#E0531C]">Positionnement GPS</div>
                                <div class="flex items-center gap-3 mt-1">
                                    <div class="font-mono text-sm text-[#2D1B16]">{{ form.lat }}, {{ form.lng }}</div>
                                    <button @click="copyCoords" type="button" class="text-xs px-2 py-1 bg-[#E0531C] text-white rounded-lg active:scale-95">Copier</button>
                                    <span v-if="copiedCoords" class="text-xs text-green-600">Copié !</span>
                                </div>
                            </div>
                            <span class="text-[10px] font-black uppercase text-[#B86A16]/80 px-2 py-1 rounded-md">Sélectionnez sur la carte</span>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">Photos touristiques (3 à 4 recommandées)</label>

                            <div class="flex items-center gap-3">
                                <label for="imagesInput" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-[#E0531C]/20 rounded-xl cursor-pointer text-sm font-bold text-[#2D1B16]">
                                    <svg class="w-4 h-4 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                                    <span>Choisir des photos</span>
                                    <span v-if="form.images && form.images.length" class="text-xs text-[#5C4033]/60">({{ form.images.length }})</span>
                                </label>
                                <input id="imagesInput" type="file" multiple accept="image/jpeg,image/png" @input="handleImageUpload" class="hidden" />

                                <button v-if="imagePreviews.length > 0" @click.prevent="clearPreviews()" class="text-xs text-[#E0531C] underline">Supprimer tout</button>
                            </div>

                            <p class="text-[10px] text-[#5C4033]/60 mt-1 italic">Ces photos illustreront le lieu une fois découvert.</p>

                            <!-- Prévisualisation améliorée -->
                            <div v-if="imagePreviews.length > 0" class="flex gap-3 mt-3 flex-wrap">
                                <div v-for="(preview, idx) in imagePreviews" :key="idx" class="w-20 h-20 rounded-md overflow-hidden border border-[#E0531C]/10 relative">
                                    <img :src="preview" class="w-full h-full object-cover" />
                                    <button @click.prevent="removePreview(idx)" class="absolute top-1 right-1 bg-white/90 text-[#E0531C] rounded-full w-6 h-6 flex items-center justify-center shadow">&times;</button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto pt-4">
                            <button type="submit" :disabled="form.processing" class="w-full h-12 rounded-2xl bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black text-sm uppercase tracking-wider active:scale-95 transition-all">
                                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5v12h14V9h-2"/></svg>
                                <span>{{ form.processing ? 'Envoi des médias...' : 'Enregistrer l\'étape' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Colonne Droite: Carte Leaflet -->
                <div class="bg-white rounded-2xl border border-[#E0531C]/10 overflow-hidden flex flex-col min-h-[360px]">
                    <div class="px-4 py-3 bg-[#FFF3DF] border-b border-[#E0531C]/10">
                        <h4 class="m-0 text-base font-black text-[#2D1B16]">Carte Géographique interactive</h4>
                        <p class="text-xs text-[#5C4033]/70 mt-1">Cliquez n'importe où pour y placer l'étape ou glissez le marqueur.</p>
                    </div>
                    <div id="map" class="flex-1 w-full min-h-[300px] rounded-md z-10"></div>
                </div>
            </div>

            <!-- Liste des étapes existantes -->
            <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem; align-items: start;" class="lg:grid-cols-3">
                
                <!-- Liste des étapes (Prend 2 colonnes sur grand écran) -->
                <div class="lg:col-span-2">
                    <h3 class="flex items-center gap-2 text-lg font-extrabold mb-6 text-[#2D1B16]">
                        <svg class="w-5 h-5 text-[#FFB700]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.553-1.97L9 1l6 2 5.447 2.724A2 2 0 0121 8.618v9.764a2 2 0 01-1.553 1.97L15 23l-6-3z"/></svg>
                        <span>Les étapes créées au Bénin</span>
                    </h3>

                    <div v-if="places.length === 0" class="text-center p-8 text-[#5C4033]/70 bg-[#FFF3DF] border border-[#E0531C]/10 rounded-2xl">
                        <div class="text-[2.5rem] mb-3 text-[#E0531C]"> 
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.5 7-10a7 7 0 10-14 0c0 5.5 7 10 7 10z"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold mb-2">Aucun lieu pour le moment</h3>
                        <p class="text-sm">Commencez par ajouter une étape à l'un de vos parcours.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
                        <div v-for="place in places" :key="place.id" class="bg-white border border-[#E0531C]/10 rounded-2xl p-4 flex flex-col shadow-sm">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-full bg-[#FFEBCC] text-[#E0531C] flex items-center justify-center font-black text-sm shadow-inner">#{{ place.order_index }}</div>
                                    <div>
                                        <h4 class="m-0 text-base font-black text-[#2D1B16]">{{ place.name }}</h4>
                                        <span class="text-xs text-[#5C4033]/70 font-bold uppercase">{{ place.city.name }}</span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm text-[#5C4033]/75 line-clamp-2 mb-3">{{ place.description || 'Aucune description rédigée.' }}</p>

                            <!-- Galerie miniatures -->
                            <div class="flex flex-wrap gap-2 mb-3 p-2 bg-[#FFF7EB] rounded-md border border-[#E0531C]/10">
                                <div v-for="img in place.images" :key="img.id" class="relative rounded-sm overflow-hidden border border-[#E0531C]/10">
                                    <img :src="img.image_url" class="w-11 h-11 object-cover" />
                                    <button @click="deleteImage(img.id)" class="absolute -top-2 -right-2 bg-[#E0531C] text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow">&times;</button>
                                </div>
                                <div v-if="!place.images || place.images.length === 0" class="text-sm text-[#5C4033]/70 italic pl-2 flex items-center">Aucune photo illustrée</div>
                            </div>

                            <div class="bg-[#FFF3DF] border border-[#E0531C]/10 rounded-md p-3 mb-4">
                                <div class="text-xs text-[#5C4033]/70">
                                    <div class="font-bold text-[#2D1B16]">Geofencing : {{ place.validation_radius }}m</div>
                                    <div class="font-mono text-[12px]">{{ place.lat }}, {{ place.lng }}</div>
                                </div>
                            </div>

                            <div class="mt-auto grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <Link :href="route('admin.riddles.index', place.id)" class="flex items-center justify-center gap-2 py-2 rounded-2xl border border-[#E0531C]/10 text-sm font-bold text-[#2D1B16] bg-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4M7 7h.01M17 7h.01"/></svg>
                                    <span>Gérer les énigmes</span>
                                </Link>
                                <button @click="deletePlace(place.id)" class="flex items-center justify-center py-2 rounded-2xl bg-[#FFEFEF] border border-red-200 text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M10 3h4a1 1 0 011 1v2H9V4a1 1 0 011-1z"/></svg>
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

        <!-- Floating add button for mobile -->
        <button @click="showForm = true" aria-label="Ajouter un lieu" class="md:hidden fixed bottom-6 right-4 z-50 p-4 rounded-full bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white shadow-lg active:scale-95 transition-all duration-200">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
        </button>
    </AdminLayout>
</template>