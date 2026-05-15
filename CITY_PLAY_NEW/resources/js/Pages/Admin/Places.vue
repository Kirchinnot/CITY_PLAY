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
    <Head title="Gestion des Lieux" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestion des Lieux</h2>
                <button
                    @click="showForm = !showForm"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition"
                >
                    {{ showForm ? 'Annuler' : 'Ajouter un lieu' }}
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Formulaire d'ajout -->
                <div v-if="showForm" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Nouveau point d'intérêt</h3>
                    
                    <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ville</label>
                                <select v-model="form.city_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500" required>
                                    <option value="">Sélectionner une ville</option>
                                    <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom du lieu</label>
                                <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500" required />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Rayon validation (m)</label>
                                    <input v-model="form.validation_radius" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ordre de visite</label>
                                    <input v-model="form.order_index" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500" />
                                </div>
                            </div>

                            <div class="flex items-center space-x-4 bg-gray-50 p-3 rounded">
                                <div class="text-xs">
                                    <span class="font-bold">Lat:</span> {{ form.lat }}
                                </div>
                                <div class="text-xs">
                                    <span class="font-bold">Lng:</span> {{ form.lng }}
                                </div>
                                <p class="text-[10px] text-gray-500">Cliquez sur la carte pour déplacer le point</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Photos du lieu</label>
                                <input
                                    type="file"
                                    multiple
                                    @input="form.images = $event.target.files"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                />
                                <p class="text-[10px] text-gray-400 mt-1">JPEG/PNG, Max 2Mo par fichier.</p>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" :disabled="form.processing" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 disabled:opacity-50">
                                    {{ form.processing ? 'Envoi...' : 'Enregistrer le lieu' }}
                                </button>
                            </div>
                        </div>

                        <!-- Carte Leaflet -->
                        <div class="h-[400px] bg-gray-100 rounded-lg overflow-hidden border border-gray-300">
                            <div id="map" class="h-full w-full"></div>
                        </div>
                    </form>
                </div>

                <!-- Liste des lieux existants -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ordre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Coordonnées</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="place in places" :key="place.id">
                                    <td class="px-6 py-4 text-sm text-gray-500">#{{ place.order_index }}</td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ place.name }}</div>
                                        <div class="text-xs text-gray-500">{{ place.city.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">
                                        {{ place.lat }}, {{ place.lng }} ({{ place.validation_radius }}m)
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <div v-for="img in place.images" :key="img.id" class="relative group">
                                                <img :src="img.image_url" class="h-10 w-10 object-cover rounded border" />
                                                <button
                                                    @click="deleteImage(img.id)"
                                                    class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full p-0.5 opacity-0 group-hover:opacity-100 transition-opacity"
                                                    title="Supprimer l'image"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <Link
                                            :href="route('admin.riddles.index', place.id)"
                                            class="text-indigo-600 hover:text-indigo-900 font-bold"
                                        >
                                            Gérer les énigmes
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Patch pour Leaflet dans Inertia si besoin */
.leaflet-container {
    z-index: 1;
}
</style>
