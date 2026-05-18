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
    { key: 'force_1', label: 'Force 1', value: 'facile' },
    { key: 'force_2', label: 'Force 2', value: 'moyen' },
    { key: 'force_3', label: 'Force 3', value: 'difficile' },
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
        previews: [null, null, null, null],
        existing_images: existing.images || [],
        hints: existing.hints ? existing.hints.map(h => ({ content: h.content, points_penalty: h.points_penalty })) : [],
    };
});

const form = useForm({
    riddles: initialRiddles,
});

const handleMultipleUpload = (riddleIndex, files) => {
    const riddle = form.riddles[riddleIndex];
    const fileArray = Array.from(files).slice(0, 4); // Prend max 4 images
    
    // Remplace les images locales existantes
    riddle.images = [null, null, null, null];
    riddle.previews = [null, null, null, null];
    
    fileArray.forEach((file, idx) => {
        riddle.images[idx] = file;
        riddle.previews[idx] = URL.createObjectURL(file);
    });
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
        onSuccess: () => alert('Énigmes sauvegardées avec succès !'),
    });
};

const activeTab = ref('enfant');
</script>

<template>
    <Head :title="'Énigmes - ' + place.name" />

    <AuthenticatedLayout>
        <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-family: var(--font-family-display); font-size: 1.5rem; font-weight: 700; color: var(--color-text-main);">
                    Gestion des Énigmes : <span style="color: var(--color-primary);">{{ place.name }}</span>
                </h2>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="premium-btn premium-btn-primary"
                >
                    {{ form.processing ? 'Enregistrement...' : 'Sauvegarder tout' }}
                </button>
            </div>
        </template>

        <div class="premium-container">
            <div class="premium-card" style="padding: 0;">
                
                <!-- Premium Tabs -->
                <div style="display: flex; border-bottom: 1px solid var(--border-color); background: var(--color-bg-light); padding: 0 1.5rem;">
                    <button
                        v-for="diff in difficulties"
                        :key="diff.key"
                        @click="activeTab = diff.value"
                        :style="{
                            padding: '1.25rem 1.5rem',
                            background: 'transparent',
                            border: 'none',
                            borderBottom: activeTab === diff.value ? '3px solid var(--color-primary)' : '3px solid transparent',
                            color: activeTab === diff.value ? 'var(--color-primary-dark)' : 'var(--color-text-muted)',
                            fontWeight: activeTab === diff.value ? '700' : '500',
                            cursor: 'pointer',
                            transition: 'all var(--transition-fast)',
                            fontSize: '0.95rem'
                        }"
                    >
                        {{ diff.label }}
                    </button>
                </div>

                <!-- Form Content -->
                <div style="padding: 2rem;">
                    <form @submit.prevent="submit">
                        <div v-for="(riddle, index) in form.riddles" :key="riddle.difficulty" v-show="activeTab === riddle.difficulty">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
                                
                                <!-- Colonne Gauche : Énigme Principale -->
                                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                    <div>
                                        <label class="premium-label">Titre de l'énigme</label>
                                        <input
                                            v-model="riddle.title"
                                            type="text"
                                            class="premium-input"
                                            placeholder="Ex: Le mystère du lion"
                                        />
                                    </div>

                                    <div>
                                        <label class="premium-label">Question / Énigme</label>
                                        <textarea
                                            v-model="riddle.question"
                                            rows="5"
                                            class="premium-input"
                                            placeholder="Écrivez l'énigme ici..."
                                        ></textarea>
                                    </div>

                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                        <div>
                                            <label class="premium-label">Points (Réussite)</label>
                                            <input v-model="riddle.points_base" type="number" class="premium-input" />
                                        </div>
                                        <div>
                                            <label class="premium-label">Temps limite (sec)</label>
                                            <input v-model="riddle.time_limit_seconds" type="number" class="premium-input" />
                                        </div>
                                    </div>

                                    <div>
                                        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.5rem;">
                                            <label class="premium-label" style="margin-bottom: 0;">Illustrations (Maximum 4)</label>
                                            <input
                                                type="file"
                                                multiple
                                                accept="image/jpeg,image/png"
                                                @input="handleMultipleUpload(index, $event.target.files)"
                                                class="premium-input"
                                                style="width: auto; padding: 0.25rem; font-size: 0.75rem;"
                                            />
                                        </div>
                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                            <div v-for="imgIdx in [0, 1, 2, 3]" :key="imgIdx" style="border: 2px dashed var(--border-color); border-radius: var(--border-radius-md); padding: 0.5rem; text-align: center; transition: all var(--transition-fast);">
                                                <div style="height: 100px; background: var(--color-bg-light); border-radius: var(--border-radius-sm); overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                                    <img 
                                                        v-if="riddle.previews[imgIdx]" 
                                                        :src="riddle.previews[imgIdx]" 
                                                        style="width: 100%; height: 100%; object-fit: cover;"
                                                    />
                                                    <img 
                                                        v-else-if="riddle.existing_images.find(i => i.display_order === imgIdx + 1)" 
                                                        :src="riddle.existing_images.find(i => i.display_order === imgIdx + 1).image_url" 
                                                        style="width: 100%; height: 100%; object-fit: cover;"
                                                    />
                                                    <span v-else style="color: var(--color-text-muted); font-size: 0.75rem;">Slot {{ imgIdx + 1 }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <p style="text-align: right; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.5rem;">Sélectionnez plusieurs images d'un coup (JPEG/PNG).</p>
                                    </div>
                                </div>

                                <!-- Colonne Droite : QCM & Indices -->
                                <div style="display: flex; flex-direction: column; gap: 2rem;">
                                    
                                    <!-- QCM -->
                                    <div style="background: var(--color-bg-light); border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); padding: 1.5rem;">
                                        <h4 style="margin: 0 0 1rem 0; font-size: 1rem; color: var(--color-primary-dark); font-weight: 700;">Configuration QCM</h4>
                                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                            <div v-for="(opt, oIndex) in riddle.options" :key="oIndex" style="display: flex; align-items: center; gap: 0.75rem;">
                                                <input
                                                    type="radio"
                                                    :name="'correct_' + riddle.difficulty"
                                                    :value="riddle.options[oIndex]"
                                                    v-model="riddle.answer"
                                                    style="width: 18px; height: 18px; accent-color: var(--color-primary);"
                                                />
                                                <input
                                                    v-model="riddle.options[oIndex]"
                                                    type="text"
                                                    class="premium-input"
                                                    style="padding: 0.5rem 1rem;"
                                                    :placeholder="'Option ' + (oIndex + 1)"
                                                />
                                            </div>
                                        </div>
                                        <p style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 1rem; font-style: italic;">
                                            Sélectionnez le bouton radio correspondant à la bonne réponse.
                                        </p>
                                    </div>

                                    <!-- Indices -->
                                    <div style="background: var(--color-warning-bg); border: 1px solid #fcd34d; border-radius: var(--border-radius-lg); padding: 1.5rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                            <h4 style="margin: 0; font-size: 1rem; color: #b45309; font-weight: 700;">Indices (Max 3)</h4>
                                            <button 
                                                type="button" 
                                                @click="addHint(index)" 
                                                v-if="riddle.hints.length < 3"
                                                class="premium-btn"
                                                style="background: #d97706; color: white; padding: 0.25rem 0.75rem; font-size: 0.75rem;"
                                            >
                                                + Ajouter
                                            </button>
                                        </div>

                                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                                            <div v-for="(hint, hIndex) in riddle.hints" :key="hIndex" style="background: white; border-radius: var(--border-radius-md); padding: 1rem; box-shadow: var(--shadow-sm);">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                                    <span style="font-size: 0.7rem; font-weight: 700; color: #d97706;">INDICE #{{ hIndex + 1 }}</span>
                                                    <button @click="removeHint(index, hIndex)" type="button" style="background: transparent; border: none; color: var(--color-danger); font-size: 0.75rem; cursor: pointer;">Retirer</button>
                                                </div>
                                                <textarea
                                                    v-model="hint.content"
                                                    rows="2"
                                                    class="premium-input"
                                                    style="margin-bottom: 0.5rem; font-size: 0.85rem;"
                                                    placeholder="Contenu de l'indice..."
                                                ></textarea>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <label style="font-size: 0.75rem; color: var(--color-text-muted);">Pénalité (points) :</label>
                                                    <input
                                                        v-model="hint.points_penalty"
                                                        type="number"
                                                        class="premium-input"
                                                        style="width: 80px; padding: 0.25rem 0.5rem;"
                                                    />
                                                </div>
                                            </div>
                                            <p v-if="riddle.hints.length === 0" style="text-align: center; font-size: 0.85rem; color: #d97706; font-style: italic;">
                                                Aucun indice configuré pour cette difficulté.
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
