<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch, onBeforeUnmount } from 'vue';
import { confirmModal } from '@/composables/usePrimeDialogs';

const props = defineProps({
    places: Array,
    cities: Array,
    filters: Object,
});

const showForm     = ref(false);
const selectedPlace = ref(null); // Lieu sélectionné pour la modale de détails
const imagePreviews = ref([]);

const form = useForm({
    city_id: '',
    name: '',
    description: '',
    lat: 6.3654,
    lng: 2.4406,
    validation_radius: 30,
    order_index: 1,
    images: null,
});

// Références pour les instances Leaflet
let mapInstance    = null;
let markerInstance = null;

// ── Gestion des images ───────────────────────────────────────────────────────
const handleImageUpload = (e) => {
    form.images = e.target.files;
    imagePreviews.value = [];
    if (e.target.files) {
        Array.from(e.target.files).forEach(file => {
            imagePreviews.value.push(URL.createObjectURL(file));
        });
    }
};

const removePreview = (index) => {
    imagePreviews.value.splice(index, 1);
    if (!form.images) return;
    const dt = new DataTransfer();
    Array.from(form.images).forEach((file, i) => { if (i !== index) dt.items.add(file); });
    form.images = dt.files;
};

const clearPreviews = () => { form.images = null; imagePreviews.value = []; };

// ── Copie des coordonnées ────────────────────────────────────────────────────
const copiedCoords = ref(false);
const copyCoords = async () => {
    try {
        await navigator.clipboard.writeText(`${form.lat}, ${form.lng}`);
    } catch {
        const tmp = document.createElement('textarea');
        tmp.value = `${form.lat}, ${form.lng}`;
        document.body.appendChild(tmp);
        tmp.select();
        document.execCommand('copy');
        document.body.removeChild(tmp);
    }
    copiedCoords.value = true;
    setTimeout(() => copiedCoords.value = false, 1500);
};

// ── Leaflet ──────────────────────────────────────────────────────────────────
const initMap = () => {
    const mapContainer = document.getElementById('map');
    if (!mapContainer || mapInstance) return;

    mapInstance = L.map('map').setView([form.lat, form.lng], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap contributors © CARTO',
    }).addTo(mapInstance);

    markerInstance = L.marker([form.lat, form.lng], { draggable: true }).addTo(mapInstance);

    markerInstance.on('dragend', (e) => {
        const { lat, lng } = e.target.getLatLng();
        form.lat = parseFloat(lat.toFixed(6));
        form.lng = parseFloat(lng.toFixed(6));
    });

    mapInstance.on('click', (e) => {
        const { lat, lng } = e.latlng;
        form.lat = parseFloat(lat.toFixed(6));
        form.lng = parseFloat(lng.toFixed(6));
        markerInstance.setLatLng(e.latlng);
    });
};

const destroyMap = () => {
    if (mapInstance) { mapInstance.remove(); mapInstance = null; markerInstance = null; }
};

// Mise à jour du marqueur quand l'admin tape les coordonnées manuellement
watch(() => form.lat, (val) => {
    if (markerInstance && !isNaN(val) && !isNaN(form.lng)) {
        markerInstance.setLatLng([val, form.lng]);
        mapInstance.setView([val, form.lng]);
    }
});
watch(() => form.lng, (val) => {
    if (markerInstance && !isNaN(form.lat) && !isNaN(val)) {
        markerInstance.setLatLng([form.lat, val]);
        mapInstance.setView([form.lat, val]);
    }
});

watch(showForm, (isVisible) => {
    if (isVisible) setTimeout(initMap, 150);
    else destroyMap();
});

onBeforeUnmount(() => destroyMap());

// ── Soumission du formulaire ─────────────────────────────────────────────────
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

// ── Suppression ──────────────────────────────────────────────────────────────
const deleteImage = async (imageId) => {
    if (!(await confirmModal({ header: 'Supprimer l\'image', message: 'Supprimer cette image ?', acceptLabel: 'Oui', rejectLabel: 'Annuler' }))) return;
    router.delete(route('admin.place-images.destroy', imageId));
};

const deletePlace = async (placeId) => {
    if (!(await confirmModal({ header: 'Supprimer le lieu', message: 'Supprimer ce lieu et toutes ses énigmes ?', acceptLabel: 'Oui, supprimer', rejectLabel: 'Annuler' }))) return;
    selectedPlace.value = null;
    router.delete(route('admin.places.destroy', placeId));
};
</script>

<template>
    <Head title="CityPlay - Gestion des Étapes" />

    <AdminLayout>
        <template #header>
            <div class="max-w-7xl mx-auto w-full flex items-center justify-between px-4 py-3">
                <div>
                    <h2 class="font-sans text-xl font-black text-[#2D1B16] m-0 leading-tight">Constructeur d'Étapes</h2>
                    <p class="text-xs text-[#5C4033]/70 mt-0.5">Points d'intérêt géolocalisés & Geofencing GPS</p>
                </div>
                <button @click="showForm = !showForm"
                        :class="showForm ? 'bg-[#FFF3DF] text-[#2D1B16] border border-[#E0531C]/20' : 'bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white'"
                        class="flex items-center gap-2 px-4 py-2 rounded-2xl font-black text-xs uppercase tracking-wider transition-all duration-200 active:scale-95">
                    <span class="text-lg font-bold">{{ showForm ? '×' : '+' }}</span>
                    <span>{{ showForm ? 'Annuler' : 'Ajouter' }}</span>
                </button>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 py-5">

            <!-- ── Formulaire d'ajout (mobile first) ─────────────────────────── -->
            <div v-if="showForm" class="mb-6">
                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

                    <!-- Colonne Formulaire -->
                    <div class="bg-white rounded-2xl border border-[#E0531C]/10 p-5 flex flex-col gap-4">
                        <h3 class="text-sm font-black text-[#2D1B16] flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.5 7-10a7 7 0 10-14 0c0 5.5 7 10 7 10z"/></svg>
                            Nouveau point d'intérêt
                        </h3>

                        <form @submit.prevent="submit" class="flex flex-col gap-4">
                            <!-- Parcours -->
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-1">Parcours associé</label>
                                <select v-model="form.city_id" class="w-full h-11 px-3 rounded-xl border border-[#E0531C]/20 bg-white text-sm" required>
                                    <option value="">Choisir un parcours...</option>
                                    <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                                </select>
                            </div>

                            <!-- Nom -->
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-1">Nom du lieu</label>
                                <input v-model="form.name" type="text" class="w-full h-11 px-3 rounded-xl border border-[#E0531C]/20 text-sm" required placeholder="Ex: Porte du Non-Retour" />
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-1">Description (max 500 car.)</label>
                                <textarea v-model="form.description" rows="3" class="w-full p-3 rounded-xl border border-[#E0531C]/20 text-sm bg-white resize-none" maxlength="500" placeholder="Décrivez l'importance historique du lieu..."></textarea>
                                <div class="flex justify-end mt-1">
                                    <span :class="form.description.length > 450 ? 'text-red-500' : 'text-[#5C4033]/60'" class="text-[10px] font-bold">{{ form.description.length }}/500</span>
                                </div>
                            </div>

                            <!-- GPS : saisie manuelle + affichage -->
                            <div class="bg-[#FFF7EB] border border-[#E0531C]/20 rounded-xl p-3 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase text-[#E0531C]">📍 Positionnement GPS</span>
                                    <button @click="copyCoords" type="button" class="text-[10px] px-2 py-1 bg-[#E0531C] text-white rounded-lg active:scale-95 font-bold">
                                        {{ copiedCoords ? 'Copié ✓' : 'Copier' }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-[#5C4033]/70 mb-1">Latitude</label>
                                        <input v-model.number="form.lat" type="number" step="0.000001" class="w-full h-10 px-3 rounded-xl border border-[#E0531C]/20 text-sm font-mono bg-white" placeholder="6.365400" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black uppercase text-[#5C4033]/70 mb-1">Longitude</label>
                                        <input v-model.number="form.lng" type="number" step="0.000001" class="w-full h-10 px-3 rounded-xl border border-[#E0531C]/20 text-sm font-mono bg-white" placeholder="2.440600" />
                                    </div>
                                </div>
                                <p class="text-[10px] text-[#5C4033]/60 italic">Ou cliquez/déplacez le marqueur directement sur la carte →</p>
                            </div>

                            <!-- Rayon & Ordre -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-1">Rayon validation (m)</label>
                                    <input v-model="form.validation_radius" type="number" class="w-full h-11 px-3 rounded-xl border border-[#E0531C]/20 text-sm" min="5" placeholder="30" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-1">Ordre de passage</label>
                                    <input v-model="form.order_index" type="number" class="w-full h-11 px-3 rounded-xl border border-[#E0531C]/20 text-sm" min="1" />
                                </div>
                            </div>

                            <!-- Photos -->
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">Photos (3-4 recommandées)</label>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <label for="imagesInput" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-[#E0531C]/20 rounded-xl cursor-pointer text-sm font-bold text-[#2D1B16]">
                                        <svg class="w-4 h-4 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
                                        Choisir
                                        <span v-if="form.images?.length" class="text-xs text-[#5C4033]/60">({{ form.images.length }})</span>
                                    </label>
                                    <input id="imagesInput" type="file" multiple accept="image/jpeg,image/png" @input="handleImageUpload" class="hidden" />
                                    <button v-if="imagePreviews.length" @click.prevent="clearPreviews()" class="text-xs text-[#E0531C] underline">Tout supprimer</button>
                                </div>
                                <div v-if="imagePreviews.length" class="flex gap-3 mt-3 flex-wrap">
                                    <div v-for="(preview, idx) in imagePreviews" :key="idx" class="w-16 h-16 rounded-xl overflow-hidden border border-[#E0531C]/10 relative">
                                        <img :src="preview" class="w-full h-full object-cover" />
                                        <button @click.prevent="removePreview(idx)" class="absolute top-0.5 right-0.5 bg-white/90 text-[#E0531C] rounded-full w-5 h-5 flex items-center justify-center shadow text-xs">×</button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" :disabled="form.processing" class="w-full h-12 rounded-2xl bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black text-sm uppercase tracking-wider active:scale-95 transition-all mt-2">
                                {{ form.processing ? 'Envoi...' : 'Enregistrer l\'étape' }}
                            </button>
                        </form>
                    </div>

                    <!-- Colonne Carte Leaflet -->
                    <div class="bg-white rounded-2xl border border-[#E0531C]/10 overflow-hidden flex flex-col min-h-[340px]">
                        <div class="px-4 py-3 bg-[#FFF3DF] border-b border-[#E0531C]/10">
                            <h4 class="m-0 text-sm font-black text-[#2D1B16]">Carte interactive</h4>
                            <p class="text-[11px] text-[#5C4033]/70 mt-0.5">Cliquez ou déplacez le marqueur · La saisie manuelle met aussi à jour la carte.</p>
                        </div>
                        <div id="map" class="flex-1 w-full min-h-[280px]"></div>
                    </div>
                </div>
            </div>

            <!-- ── Liste compacte des lieux ───────────────────────────────────── -->
            <div>
                <h3 class="text-sm font-black uppercase tracking-[0.15em] text-[#2D1B16] mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#FFB700]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 011.553-1.97L9 1l6 2 5.447 2.724A2 2 0 0121 8.618v9.764a2 2 0 01-1.553 1.97L15 23l-6-3z"/></svg>
                    Étapes créées
                    <span class="ml-auto text-[11px] font-bold text-[#5C4033]/60 normal-case tracking-normal">{{ places.length }} lieu{{ places.length > 1 ? 'x' : '' }}</span>
                </h3>

                <div v-if="places.length === 0" class="text-center p-8 text-[#5C4033]/70 bg-[#FFF3DF] border border-[#E0531C]/10 rounded-2xl">
                    <svg class="w-10 h-10 mx-auto mb-3 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.5 7-10a7 7 0 10-14 0c0 5.5 7 10 7 10z"/></svg>
                    <p class="text-sm font-bold">Aucun lieu pour le moment</p>
                    <p class="text-xs mt-1">Commencez par ajouter une étape.</p>
                </div>

                <!-- Liste compacte mobile-first -->
                <div v-else class="divide-y divide-[#E0531C]/10 bg-white rounded-2xl border border-[#E0531C]/10 overflow-hidden">
                    <button
                        v-for="place in places"
                        :key="place.id"
                        @click="selectedPlace = place"
                        class="w-full flex items-center gap-3 px-4 py-3 text-left transition hover:bg-[#FFF7EB] active:bg-[#FFE9D1]"
                    >
                        <!-- Numéro d'ordre -->
                        <div class="w-8 h-8 rounded-full bg-[#FFEBCC] text-[#E0531C] flex items-center justify-center font-black text-xs shrink-0">
                            #{{ place.order_index }}
                        </div>

                        <!-- Nom + Ville -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-black text-[#2D1B16] truncate">{{ place.name }}</p>
                            <p class="text-[11px] font-bold text-[#5C4033]/60 uppercase truncate">{{ place.city.name }}</p>
                        </div>

                        <!-- Badges rapides -->
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="hidden sm:inline text-[10px] font-bold text-[#5C4033]/50 bg-[#FFF3DF] px-2 py-1 rounded-full">{{ place.images?.length ?? 0 }} photo{{ (place.images?.length ?? 0) > 1 ? 's' : '' }}</span>
                            <svg class="w-4 h-4 text-[#E0531C]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Modale de détails du lieu (mobile first) ───────────────────────── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-150 ease-in"
                leave-to-class="opacity-0"
            >
                <div v-if="selectedPlace" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 backdrop-blur-sm" @click.self="selectedPlace = null">
                    <div class="w-full sm:max-w-lg bg-white rounded-t-[2rem] sm:rounded-[2rem] overflow-hidden shadow-2xl max-h-[90vh] flex flex-col">

                        <!-- Header modale -->
                        <div class="flex items-start justify-between gap-3 px-5 pt-5 pb-3 border-b border-[#E0531C]/10">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#E0531C]">{{ selectedPlace.city.name }}</p>
                                <h3 class="text-lg font-black text-[#2D1B16] leading-tight">{{ selectedPlace.name }}</h3>
                            </div>
                            <button @click="selectedPlace = null" class="w-9 h-9 flex items-center justify-center rounded-full bg-[#FFF3DF] text-[#E0531C] font-black text-lg shrink-0 active:scale-95">×</button>
                        </div>

                        <!-- Corps de la modale (scrollable) -->
                        <div class="overflow-y-auto flex-1 p-5 space-y-4">

                            <!-- Description -->
                            <div v-if="selectedPlace.description" class="bg-[#FFF7EB] rounded-xl p-4">
                                <p class="text-[10px] font-black uppercase text-[#B86A16] mb-1">Description</p>
                                <p class="text-sm text-[#5C4033] leading-relaxed">{{ selectedPlace.description }}</p>
                            </div>

                            <!-- GPS & Geofencing -->
                            <div class="bg-[#FFF3DF] border border-[#E0531C]/10 rounded-xl p-4">
                                <p class="text-[10px] font-black uppercase text-[#B86A16] mb-2">📍 Géolocalisation</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <p class="text-[10px] text-[#5C4033]/60 uppercase font-bold">Latitude</p>
                                        <p class="font-mono text-sm font-black text-[#2D1B16]">{{ selectedPlace.lat }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-[#5C4033]/60 uppercase font-bold">Longitude</p>
                                        <p class="font-mono text-sm font-black text-[#2D1B16]">{{ selectedPlace.lng }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-[#5C4033]/60 uppercase font-bold">Rayon GPS</p>
                                        <p class="font-mono text-sm font-black text-[#2D1B16]">{{ selectedPlace.validation_radius }} m</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-[#5C4033]/60 uppercase font-bold">Ordre</p>
                                        <p class="font-mono text-sm font-black text-[#2D1B16]">#{{ selectedPlace.order_index }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Galerie photos -->
                            <div>
                                <p class="text-[10px] font-black uppercase text-[#B86A16] mb-2">Photos ({{ selectedPlace.images?.length ?? 0 }})</p>
                                <div v-if="selectedPlace.images?.length" class="flex gap-2 flex-wrap">
                                    <div v-for="img in selectedPlace.images" :key="img.id" class="relative w-20 h-20 rounded-xl overflow-hidden border border-[#E0531C]/10">
                                        <img :src="img.image_url" class="w-full h-full object-cover" />
                                        <button @click="deleteImage(img.id)" class="absolute top-0.5 right-0.5 bg-[#E0531C] text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow">×</button>
                                    </div>
                                </div>
                                <p v-else class="text-xs text-[#5C4033]/50 italic">Aucune photo pour ce lieu.</p>
                            </div>
                        </div>

                        <!-- Footer modale : actions -->
                        <div class="px-5 pb-5 pt-3 border-t border-[#E0531C]/10 grid grid-cols-2 gap-3">
                            <Link :href="route('admin.riddles.index', selectedPlace.id)"
                                  class="flex items-center justify-center gap-2 py-3 rounded-2xl border border-[#E0531C]/20 text-sm font-black text-[#2D1B16] bg-[#FFF7EB] active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2-2 4 4M7 7h.01M17 7h.01"/></svg>
                                Énigmes
                            </Link>
                            <button @click="deletePlace(selectedPlace.id)"
                                    class="flex items-center justify-center gap-2 py-3 rounded-2xl bg-red-50 border border-red-200 text-sm font-black text-red-600 active:scale-95 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M10 3h4a1 1 0 011 1v2H9V4a1 1 0 011-1z"/></svg>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Bouton flottant mobile -->
        <button @click="showForm = true" aria-label="Ajouter un lieu" class="md:hidden fixed bottom-6 right-4 z-50 p-4 rounded-full bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white shadow-lg active:scale-95 transition-all duration-200">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
        </button>
    </AdminLayout>
</template>