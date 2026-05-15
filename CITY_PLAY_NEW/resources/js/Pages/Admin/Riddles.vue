<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    place: Object,
    riddles: Array,
});

const difficulties = [
    { key: 'child', label: 'Enfant', value: 'enfant' },
    { key: 'force_1', label: 'Force 1 (Facile)', value: 'facile' },
    { key: 'force_2', label: 'Force 2 (Moyen)', value: 'moyen' },
    { key: 'force_3', label: 'Force 3 (Difficile)', value: 'difficile' },
];

// Initialisation du formulaire avec les 4 niveaux
const initialRiddles = difficulties.map(diff => {
    const existing = props.riddles.find(r => r.difficulty === diff.value) || {};
    return {
        difficulty: diff.value,
        title: existing.title || '',
        question: existing.question || '',
        options: existing.options || ['', '', '', ''],
        answer: existing.answer || '',
        points_base: existing.points_base || 100,
        time_limit_seconds: existing.time_limit_seconds || 300,
        images: [null, null, null, null],
        existing_images: existing.images || [],
        hints: existing.hints ? existing.hints.map(h => ({ content: h.content, points_penalty: h.points_penalty })) : [],
    };
});

const form = useForm({
    riddles: initialRiddles,
});

const setFile = (riddleIndex, imgIndex, file) => {
    form.riddles[riddleIndex].images[imgIndex] = file;
};

const addHint = (riddleIndex) => {
    if (form.riddles[riddleIndex].hints.length < 3) {
        form.riddles[riddleIndex].hints.push({ content: '', points_penalty: 10 });
    }
};

const removeHint = (riddleIndex, hintIndex) => {
    form.riddles[riddleIndex].hints.splice(hintIndex, 1);
};

const submit = () => {
    form.post(route('admin.riddles.store', props.place.id), {
        onSuccess: () => alert('Énigmes sauvegardées !'),
    });
};

const activeTab = ref('enfant');
</script>

<template>
    <Head :title="'Énigmes - ' + place.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestion des Énigmes : {{ place.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <!-- Tabs pour les difficultés -->
                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8">
                            <button
                                v-for="diff in difficulties"
                                :key="diff.key"
                                @click="activeTab = diff.value"
                                :class="[
                                    activeTab === diff.value
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors'
                                ]"
                            >
                                {{ diff.label }}
                            </button>
                        </nav>
                    </div>

                    <form @submit.prevent="submit">
                        <div v-for="(riddle, index) in form.riddles" :key="riddle.difficulty" v-show="activeTab === riddle.difficulty">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <!-- Gauche : Énigme -->
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Titre de l'énigme</label>
                                        <input
                                            v-model="riddle.title"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Ex: Le mystère du lion"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Question / Énigme</label>
                                        <textarea
                                            v-model="riddle.question"
                                            rows="4"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Écrivez l'énigme ici..."
                                        ></textarea>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Points de base</label>
                                            <input
                                                v-model="riddle.points_base"
                                                type="number"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Temps (sec)</label>
                                            <input
                                                v-model="riddle.time_limit_seconds"
                                                type="number"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Illustrations (4 photos suggérées)</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div v-for="imgIdx in [0, 1, 2, 3]" :key="imgIdx" class="relative group border-2 border-dashed border-gray-200 rounded-lg p-2 hover:border-indigo-300 transition-colors bg-white">
                                                <!-- Prévisualisation ou Placeholder -->
                                                <div class="h-24 w-full flex items-center justify-center bg-gray-50 rounded mb-2 overflow-hidden">
                                                    <img 
                                                        v-if="riddle.existing_images.find(i => i.display_order === imgIdx + 1)" 
                                                        :src="riddle.existing_images.find(i => i.display_order === imgIdx + 1).image_url" 
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <div v-else class="text-[10px] text-gray-400 text-center">
                                                        Slot {{ imgIdx + 1 }}
                                                    </div>
                                                </div>
                                                
                                                <input
                                                    type="file"
                                                    @input="setFile(index, imgIdx, $event.target.files[0])"
                                                    class="block w-full text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                                />
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-2 italic">JPEG/PNG, Max 2Mo par photo.</p>
                                    </div>
                                </div>

                                <!-- Droite : QCM & Indices -->
                                <div class="space-y-6">
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <h4 class="font-bold text-gray-700 border-b pb-2 mb-4 uppercase text-xs tracking-wider">Configuration QCM</h4>
                                        
                                        <div class="space-y-3">
                                            <div v-for="(opt, oIndex) in riddle.options" :key="oIndex">
                                                <div class="flex items-center space-x-2">
                                                    <input
                                                        type="radio"
                                                        :name="'correct_' + riddle.difficulty"
                                                        :value="riddle.options[oIndex]"
                                                        v-model="riddle.answer"
                                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                                    />
                                                    <input
                                                        v-model="riddle.options[oIndex]"
                                                        type="text"
                                                        class="block w-full rounded-md border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                                        :placeholder="'Option ' + (oIndex + 1)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-[10px] text-gray-500 mt-3 italic">
                                            * Cochez le bouton radio à gauche de la bonne réponse.
                                        </p>
                                    </div>

                                    <!-- SECTION INDICES -->
                                    <div class="bg-amber-50 p-4 rounded-lg border border-amber-200">
                                        <div class="flex justify-between items-center border-b border-amber-200 pb-2 mb-4">
                                            <h4 class="font-bold text-amber-800 uppercase text-xs tracking-wider">Indices (Max 3)</h4>
                                            <button 
                                                type="button" 
                                                @click="addHint(index)" 
                                                v-if="riddle.hints.length < 3"
                                                class="text-[10px] bg-amber-600 text-white px-2 py-1 rounded hover:bg-amber-700"
                                            >
                                                + Ajouter un indice
                                            </button>
                                        </div>

                                        <div class="space-y-4">
                                            <div v-for="(hint, hIndex) in riddle.hints" :key="hIndex" class="bg-white p-3 rounded border border-amber-100 shadow-sm">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-[10px] font-bold text-amber-600">INDICE #{{ hIndex + 1 }}</span>
                                                    <button @click="removeHint(index, hIndex)" type="button" class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
                                                </div>
                                                <textarea
                                                    v-model="hint.content"
                                                    rows="2"
                                                    class="block w-full rounded-md border-gray-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-xs mb-2"
                                                    placeholder="Texte de l'indice..."
                                                ></textarea>
                                                <div class="flex items-center space-x-2">
                                                    <label class="text-[10px] text-gray-500">Pénalité de points :</label>
                                                    <input
                                                        v-model="hint.points_penalty"
                                                        type="number"
                                                        class="block w-20 rounded-md border-gray-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-[10px]"
                                                    />
                                                </div>
                                            </div>
                                            <p v-if="riddle.hints.length === 0" class="text-center text-xs text-amber-600 italic py-4">
                                                Aucun indice configuré pour ce niveau.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <span v-if="form.processing">Enregistrement...</span>
                                <span v-else>Sauvegarder tous les niveaux</span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
