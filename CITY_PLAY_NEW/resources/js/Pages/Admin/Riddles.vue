<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
        onSuccess: () => alert('Énigmes sauvegardées avec succès !'),
    });
};

const activeTab = ref('enfant');

watch(activeTab, () => {
    setTimeout(() => {
        document.getElementById('mobile-riddle-preview-frame')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 50);
});
</script>

<template>
    <Head :title="'Énigmes - ' + place.name" />

    <AuthenticatedLayout>
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
                                        </div>
                                        <div>
                                            <label class="premium-label" style="font-weight: 700;">XP Victoire</label>
                                            <input v-model="riddle.points_base" type="number" class="premium-input" />
                                        </div>
                                        <div>
                                            <label class="premium-label" style="font-weight: 700;">Chrono (sec)</label>
                                            <input v-model="riddle.time_limit_seconds" type="number" class="premium-input" />
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

                <!-- Rendu Mobile Live - Gameplay de l'énigme (Screen 7) -->
                <div>
                    <h3 style="font-family: var(--font-family-display); font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--color-text-main); display: flex; align-items: center; gap: 0.5rem;">
                        <span style="color: var(--color-secondary);">📱</span> Rendu Gameplay Live
                    </h3>

                    <!-- Simulated Smartphone Frame -->
                    <div id="mobile-riddle-preview-frame" style="width: 100%; max-width: 340px; margin: 0 auto; background: var(--color-bg-dark); border: 10px solid #111; border-radius: var(--border-radius-xl); box-shadow: var(--shadow-premium); overflow: hidden; position: relative;">
                        <!-- Speaker notch -->
                        <div style="width: 110px; height: 18px; background: #111; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; margin: 0 auto; position: absolute; left: 50%; transform: translateX(-50%); z-index: 10; display: flex; justify-content: center; align-items: center;">
                            <div style="width: 40px; height: 3px; background: #333; border-radius: 2px;"></div>
                        </div>

                        <!-- Screen Content (Simulates Screen 7: Gameplay principal) -->
                        <div v-for="riddle in form.riddles" :key="riddle.difficulty" v-show="activeTab === riddle.difficulty" style="background: var(--color-bg-light); color: var(--color-text-main); font-family: var(--font-family-sans); min-height: 520px; max-height: 520px; overflow-y: auto; padding-top: 18px; scrollbar-width: none; display: flex; flex-direction: column;">
                            
                            <!-- Gameplay Top Stats Panel -->
                            <div style="background: var(--color-surface-light); padding: 0.75rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; font-size: 0.65rem;">
                                <div style="display: flex; align-items: center; gap: 0.25rem; font-weight: 800; color: var(--color-primary-dark);">
                                    <span>🏆</span> {{ riddle.points_base }} XP
                                </div>
                                <div style="font-weight: 800; color: var(--color-danger); display: flex; align-items: center; gap: 0.25rem;">
                                    <span>⏱️</span> {{ Math.floor(riddle.time_limit_seconds / 60) }}:{{ (riddle.time_limit_seconds % 60).toString().padStart(2, '0') }}
                                </div>
                                <div style="background: var(--color-bg-light); padding: 0.2rem 0.4rem; border-radius: 4px; font-weight: 700; color: var(--color-text-muted);">
                                    Niveau {{ activeTab.toUpperCase() }}
                                </div>
                            </div>

                            <!-- Game Body -->
                            <div style="padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem; flex: 1;">
                                <!-- Question Box -->
                                <div style="background: var(--color-surface-light); border-radius: var(--border-radius-md); padding: 0.85rem; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                                    <span style="font-size: 0.55rem; text-transform: uppercase; font-weight: 800; color: var(--color-primary-light); letter-spacing: 0.05em; display: block; margin-bottom: 0.25rem;">Énigme Secrète</span>
                                    <h4 style="margin: 0; font-family: var(--font-family-display); font-size: 0.85rem; font-weight: 800; color: var(--color-primary-dark); line-height: 1.3;">
                                        {{ riddle.title || 'Devinette mystère' }}
                                    </h4>
                                    <p style="margin: 0.5rem 0 0 0; font-size: 0.75rem; color: var(--color-text-muted); line-height: 1.45;">
                                        {{ riddle.question || 'Écrivez une énigme captivante à gauche pour voir la simulation.' }}
                                    </p>
                                </div>

                                <!-- 4-Image Grid -->
                                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
                                    <div v-for="imgIdx in [0, 1, 2, 3]" :key="imgIdx" style="height: 70px; background: #e6dfd5; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); overflow: hidden; display: flex; align-items: center; justify-content: center; position: relative;">
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
                                        <div v-else style="font-size: 0.55rem; color: var(--color-text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Illustr. {{ imgIdx + 1 }}</div>
                                    </div>
                                </div>

                                <!-- Answer Selection Buttons (Gamified QCM) -->
                                <div style="display: flex; flex-direction: column; gap: 0.4rem; margin-top: 0.25rem;">
                                    <div 
                                        v-for="(opt, oIndex) in riddle.options" 
                                        :key="oIndex" 
                                        style="border-radius: var(--border-radius-md); padding: 0.65rem 0.85rem; font-size: 0.75rem; font-weight: 700; transition: all var(--transition-bounce); display: flex; align-items: center; justify-content: space-between;"
                                        :style="{
                                            background: riddle.answer && riddle.answer === opt ? 'linear-gradient(135deg, var(--color-primary), var(--color-primary-light))' : 'var(--color-surface-light)',
                                            color: riddle.answer && riddle.answer === opt ? 'white' : 'var(--color-text-main)',
                                            border: riddle.answer && riddle.answer === opt ? '1px solid var(--color-primary-dark)' : '1px solid var(--border-color)',
                                            boxShadow: riddle.answer && riddle.answer === opt ? '0 4px 8px rgba(200,92,50,0.25)' : 'var(--shadow-sm)'
                                        }"
                                    >
                                        <span>{{ opt || 'Option de réponse vide...' }}</span>
                                        <span v-if="riddle.answer && riddle.answer === opt" style="font-size: 0.8rem;">🎯</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Help & Submit Drawer (Gamified) -->
                            <div style="background: var(--color-surface-light); padding: 0.85rem; border-top: 1px solid var(--border-color); display: flex; align-items: center; gap: 0.5rem; margin-top: auto;">
                                <button 
                                    type="button" 
                                    style="flex: 1; background: var(--color-warning-bg); border: 1px solid #fcd34d; border-radius: var(--border-radius-sm); padding: 0.6rem; color: #b45309; font-size: 0.7rem; font-weight: 800; display: flex; justify-content: center; align-items: center; gap: 0.25rem;"
                                    @click="alert(riddle.hints.length > 0 ? 'Indice 1: ' + riddle.hints[0].content : 'Aucun indice disponible.')"
                                >
                                    💡 INDICE ({{ riddle.hints.length }})
                                </button>
                                <button type="button" class="premium-btn premium-btn-primary" style="flex: 1.5; padding: 0.6rem; font-size: 0.75rem; font-family: var(--font-family-display); font-weight: 800; border-radius: var(--border-radius-sm); display: flex; justify-content: center; align-items: center; gap: 0.25rem;">
                                    VALIDER ⚔️
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>
