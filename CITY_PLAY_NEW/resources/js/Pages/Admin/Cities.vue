<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    cities: Array,
    errors: Object,
});

const publish = (cityId) => {
    router.post(route('admin.cities.publish', cityId));
};

const unpublish = (cityId) => {
    router.post(route('admin.cities.unpublish', cityId));
};
</script>

<template>
    <Head title="Publication des Parcours" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Publication des Parcours</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="errors.publish" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <span class="font-bold">Erreur de publication :</span>
                    <p>{{ errors.publish }}</p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="city in cities" :key="city.id" class="border rounded-lg p-4 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start">
                                    <h3 class="text-lg font-bold">{{ city.name }}</h3>
                                    <span :class="[
                                        city.is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800',
                                        'px-2 py-1 rounded text-xs font-semibold'
                                    ]">
                                        {{ city.is_published ? 'Publié' : 'Brouillon' }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">{{ city.description }}</p>
                                
                                <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                    <div class="bg-gray-50 p-2 rounded">
                                        <span class="block text-gray-400">Lieux</span>
                                        <span class="font-bold">{{ city.places_count }}</span>
                                    </div>
                                    <div class="bg-gray-50 p-2 rounded">
                                        <span class="block text-gray-400">Parties jouées</span>
                                        <span class="font-bold">{{ city.game_sessions_count }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <button
                                    v-if="!city.is_published"
                                    @click="publish(city.id)"
                                    class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition font-bold"
                                >
                                    Publier le parcours
                                </button>
                                <button
                                    v-else
                                    @click="unpublish(city.id)"
                                    class="w-full border border-red-600 text-red-600 py-2 rounded hover:bg-red-50 transition font-bold"
                                >
                                    Retirer de la publication
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
