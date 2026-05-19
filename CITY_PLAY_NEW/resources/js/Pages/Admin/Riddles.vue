<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    place: Object,
    riddles: Array,
});

const difficulties = [
    { key: 'child', label: 'Enfant 👶', value: 'enfant' },
    { key: 'force_1', label: 'Force 1 🏹', value: 'facile' },
    { key: 'force_2', label: 'Force 2 🦁', value: 'moyen' },
    { key: 'force_3', label: 'Force 3 👑', value: 'difficile' },
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
        forceFormData: true,
        onSuccess: () => alert('Énigmes sauvegardées avec succès !'),
        onError: (errors) => {
            console.error(errors);
            alert('Une erreur est survenue lors de la sauvegarde. Vérifiez les champs saisis.');
        }
    });
};

const activeTab = ref('enfant');
</script>

<template>
    <Head :title="'Énigmes - ' + place.name" />

    <AdminLayout>
        <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; width: 100%;">
                <div>
                    <h2 style="font-family: var(--font-family-display); font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark); margin: 0;">
                        Gestion des Énigmes
                    </h2>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin: 0.25rem 0 0 0;">
                        Lieu cible : <span style="font-weight: 700; color: var(--color-primary);">{{ place.name }}</span>
                    </p>
                </div>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="premium-btn premium-btn-primary"
                    style="gap: 0.5rem;"
                >
                    {{ form.processing ? 'Enregistrement...' : '💾 Sauvegarder tout' }}
                </button>
            </div>
        </template>

        <div class="premium-container" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
            
            <div style="display: grid; grid-template-columns: 1fr; lg:grid-template-columns: 2fr 1fr; gap: 2.5rem; align-items: start;">
                
                <!-- Colonne Gauche: Formulaires par Difficulté -->
                <div class="premium-card" style="padding: 0; overflow: hidden; border: 1px solid var(--border-color);">
                    <!-- Premium Tabs with Modern Benin style indicators -->
                    <div style="display: flex; border-bottom: 1px solid var(--border-color); background: var(--color-bg-light); padding: 0 1rem; overflow-x: auto; scrollbar-width: none;">
                        <button
                            v-for="diff in difficulties"
                            :key="diff.key"
                            @click="activeTab = diff.value"
                            :style="{
                                padding: '1.25rem 1rem',
                                background: 'transparent',
                                border: 'none',
                                borderBottom: activeTab === diff.value ? '4px solid var(--color-primary)' : '4px solid transparent',
                                color: activeTab === diff.value ? 'var(--color-primary-dark)' : 'var(--color-text-muted)',
                                fontWeight: activeTab === diff.value ? '800' : '600',
                                cursor: 'pointer',
                                transition: 'all var(--transition-fast)',
                                fontSize: '0.9rem',
                                fontFamily: 'var(--font-family-display)',
                                whitespace: 'nowrap'
                            }"
                        >
                            {{ diff.label }}
                        </button>
                    </div>

                    <!-- Form Content -->
                    <div style="padding: 2rem;">
                        <form @submit.prevent="submit">
                            <div v-for="(riddle, index) in form.riddles" :key="riddle.difficulty" v-show="activeTab === riddle.difficulty">
                                <div style="display: flex; flex-direction: column; gap: 2rem;">
                                    
                                    <!-- Configuration Info de base -->
                                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
                                        <div>
                                            <label class="premium-label" style="font-weight: 700;">Titre de l'énigme</label>
                                            <input
                                                v-model="riddle.title"
                                                type="text"
                                                class="premium-input"
                                                placeholder="Ex: Le secret de la jarre trouée"
                                            />
                                            <p v-if="form.errors['riddles.' + index + '.title']" style="color: red; font-size: 0.75rem; margin-top: 0.25rem;">{{ form.errors['riddles.' + index + '.title'] }}</p>
                                        </div>
                                        <div>
                                            <label class="premium-label" style="font-weight: 700;">XP Victoire</label>
                                            <input v-model="riddle.points_base" type="number" class="premium-input" />
                                            <p v-if="form.errors['riddles.' + index + '.points_base']" style="color: red; font-size: 0.75rem; margin-top: 0.25rem;">{{ form.errors['riddles.' + index + '.points_base'] }}</p>
                                        </div>
                                        <div>
                                            <label class="premium-label" style="font-weight: 700;">Chrono (sec)</label>
                                            <input v-model="riddle.time_limit_seconds" type="number" class="premium-input" />
                                            <p v-if="form.errors['riddles.' + index + '.time_limit_seconds']" style="color: red; font-size: 0.75rem; margin-top: 0.25rem;">{{ form.errors['riddles.' + index + '.time_limit_seconds'] }}</p>
                                        </div>
                                    </div>

                                    <!-- Question narrative -->
                                    <div>
                                        <label class="premium-label" style="font-weight: 700;">L'énigme / La devinette culturelle</label>
                                        <textarea
                                            v-model="riddle.question"
                                            rows="4"
                                            class="premium-input"
                                            placeholder="Ex: Je suis un roi célèbre d'Abomey, représenté par un oiseau pillard..."
                                        ></textarea>
                                        <p v-if="form.errors['riddles.' + index + '.question']" style="color: red; font-size: 0.75rem; margin-top: 0.25rem;">{{ form.errors['riddles.' + index + '.question'] }}</p>
                                    </div>

                                    <!-- Double panel : Illustrations & QCM -->
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                                        
                                        <!-- Images -->
                                        <div>
                                            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.5rem;">
                                                <label class="premium-label" style="margin-bottom: 0; font-weight: 700;">4 Images d'illustrations (Indicateurs visuels)</label>
                                            </div>
                                            <input
                                                type="file"
                                                multiple
                                                accept="image/jpeg,image/png"
                                                @input="handleMultipleUpload(index, $event.target.files)"
                                                class="premium-input"
                                                style="padding: 0.4rem; font-size: 0.8rem; margin-bottom: 1rem;"
                                            />
                                            
                                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
                                                <div v-for="imgIdx in [0, 1, 2, 3]" :key="imgIdx" style="border: 2px dashed var(--border-color); border-radius: var(--border-radius-md); padding: 0.25rem; text-align: center; transition: all var(--transition-fast);">
                                                    <div style="height: 80px; background: var(--color-bg-light); border-radius: var(--border-radius-sm); overflow: hidden; display: flex; align-items: center; justify-content: center;">
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
                                                        <span v-else style="color: var(--color-text-muted); font-size: 0.7rem;">Slot {{ imgIdx + 1 }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Options QCM -->
                                        <div style="background: var(--color-bg-light); border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); padding: 1.25rem;">
                                            <h4 style="margin: 0 0 1rem 0; font-family: var(--font-family-display); font-size: 1rem; color: var(--color-primary-dark); font-weight: 800;">Options de réponse (QCM)</h4>
                                            
                                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                                <div v-for="(opt, oIndex) in riddle.options" :key="oIndex" style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <input
                                                        type="radio"
                                                        :name="'correct_' + riddle.difficulty"
                                                        :value="riddle.options[oIndex]"
                                                        v-model="riddle.answer"
                                                        style="width: 20px; height: 20px; accent-color: var(--color-primary);"
                                                        title="Cocher comme bonne réponse"
                                                    />
                                                    <input
                                                        v-model="riddle.options[oIndex]"
                                                        type="text"
                                                        class="premium-input"
                                                        style="padding: 0.5rem 0.75rem; font-size: 0.85rem;"
                                                        :placeholder="'Choix ' + (oIndex + 1)"
                                                    />
                                                </div>
                                            </div>
                                            <p v-if="form.errors['riddles.' + index + '.options']" style="color: red; font-size: 0.75rem; margin-top: 0.5rem;">{{ form.errors['riddles.' + index + '.options'] }}</p>
                                            <p v-if="form.errors['riddles.' + index + '.answer']" style="color: red; font-size: 0.75rem; margin-top: 0.5rem;">{{ form.errors['riddles.' + index + '.answer'] }}</p>
                                            <p style="font-size: 0.7rem; color: var(--color-text-muted); margin-top: 1rem; font-style: italic;">
                                                ⚠️ Cochez le bouton de gauche pour désigner l'unique bonne réponse.
                                            </p>
                                        </div>

                                    </div>

                                    <!-- Indices -->
                                    <div style="background: var(--color-warning-bg); border: 1px solid #fcd34d; border-radius: var(--border-radius-lg); padding: 1.5rem;">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                            <h4 style="margin: 0; font-family: var(--font-family-display); font-size: 1rem; color: #b45309; font-weight: 800; display: flex; align-items: center; gap: 0.5rem;">
                                                <span>💡</span> Indices déblocables (Max 3)
                                            </h4>
                                            <button 
                                                type="button" 
                                                @click="addHint(index)" 
                                                v-if="riddle.hints.length < 3"
                                                class="premium-btn"
                                                style="background: #d97706; color: white; padding: 0.35rem 0.85rem; font-size: 0.75rem; font-weight: 700; border-radius: var(--border-radius-sm);"
                                            >
                                                + Ajouter
                                            </button>
                                        </div>

                                        <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                                            <div v-for="(hint, hIndex) in riddle.hints" :key="hIndex" style="background: white; border-radius: var(--border-radius-md); padding: 1rem; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                                    <span style="font-size: 0.75rem; font-weight: 800; color: #d97706;">INDICE #{{ hIndex + 1 }}</span>
                                                    <button @click="removeHint(index, hIndex)" type="button" style="background: transparent; border: none; color: var(--color-danger); font-size: 0.75rem; cursor: pointer; font-weight: 700;">Retirer</button>
                                                </div>
                                                <textarea
                                                    v-model="hint.content"
                                                    rows="2"
                                                    class="premium-input"
                                                    style="margin-bottom: 0.5rem; font-size: 0.85rem;"
                                                    placeholder="Rédigez l'indice d'aide..."
                                                ></textarea>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <label style="font-size: 0.75rem; color: var(--color-text-muted); font-weight: 600;">Pénalité de score :</label>
                                                    <input
                                                        v-model="hint.points_penalty"
                                                        type="number"
                                                        class="premium-input"
                                                        style="width: 80px; padding: 0.25rem 0.5rem; font-size: 0.8rem;"
                                                    />
                                                    <span style="font-size: 0.75rem; color: var(--color-text-muted);">points</span>
                                                </div>
                                            </div>
                                            
                                            <p v-if="riddle.hints.length === 0" style="text-align: center; font-size: 0.8rem; color: #b45309; font-style: italic; margin: 0;">
                                                Aucun indice d'aide configuré. Les joueurs devront s'appuyer uniquement sur leurs connaissances.
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>
