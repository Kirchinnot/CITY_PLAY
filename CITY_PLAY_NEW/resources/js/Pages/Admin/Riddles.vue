<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { alertModal } from '@/composables/usePrimeDialogs';

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
        forceFormData: true,
        onSuccess: async () => await alertModal({
            header: 'Enigmes sauvegardées',
            message: 'Énigmes sauvegardées avec succès !',
            icon: 'pi pi-check-circle',
            acceptLabel: 'Super',
        }),
        onError: async (errors) => {
            console.error(errors);
            await alertModal({
                header: 'Erreur',
                message: 'Une erreur est survenue lors de la sauvegarde. Vérifiez les champs saisis.',
                icon: 'pi pi-exclamation-triangle',
                acceptLabel: 'D\'accord',
            });
        }
    });
};

const activeTab = ref('enfant');
</script>

<template>
    <Head :title="'Énigmes - ' + place.name" />

    <AdminLayout>
        <NotificationDescartes />

        <template #header>
            <div class="max-w-7xl mx-auto w-full px-4 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-[#2D1B16] tracking-tight uppercase">Gestion des Énigmes</h2>
                        <p class="text-xs text-[#5C4033]/70 font-bold mt-1 uppercase tracking-wider">
                            Lieu cible : <span class="text-[#E0531C]">{{ place.name }}</span>
                        </p>
                    </div>
                    
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all active:scale-95 disabled:opacity-50 shrink-0"
                        :class="form.processing 
                            ? 'bg-[#FFF3DF] text-[#5C4033]/50' 
                            : 'bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white shadow-lg'"
                    >
                        <svg v-if="!form.processing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5v12h14V9h-2"/></svg>
                        <span v-else class="w-4 h-4 border-2 border-[#5C4033]/30 border-t-[#E0531C] rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Enregistrement...' : 'Sauvegarder l\'Énigme' }}</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 gap-8 items-start">
                
                <div class="bg-white border border-[#E0531C]/10 rounded-2xl shadow-sm overflow-hidden">
                    
                    <div class="flex border-b border-[#E0531C]/10 bg-[#FFF7EB] px-4 overflow-x-auto scrollbar-none">
                        <button
                            v-for="diff in difficulties"
                            :key="diff.key"
                            @click="activeTab = diff.value"
                            class="px-5 py-4 bg-transparent border-b-4 text-xs uppercase tracking-widest transition-all whitespace-nowrap focus:outline-none font-bold"
                            :class="activeTab === diff.value 
                                ? 'border-[#E0531C] text-[#2D1B16]' 
                                : 'border-transparent text-[#5C4033]/60 hover:text-[#5C4033]/80'"
                        >
                            {{ diff.label }}
                        </button>
                    </div>

                    <div class="p-6 md:p-8 bg-white">
                        <form @submit.prevent="submit">
                            <div v-for="(riddle, index) in form.riddles" :key="riddle.difficulty" v-show="activeTab === riddle.difficulty" class="space-y-8">
                                
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">Titre de l'énigme</label>
                                        <input
                                            v-model="riddle.title"
                                            type="text"
                                            class="w-full bg-white border border-[#E0531C]/20 rounded-xl px-4 py-3 text-sm text-[#2D1B16] placeholder-[#5C4033]/40 focus:outline-none focus:border-[#E0531C] focus:ring-1 focus:ring-[#E0531C]/30 transition-colors"
                                            placeholder="Ex: Le secret de la jarre trouée"
                                        />
                                        <p v-if="form.errors['riddles.' + index + '.title']" class="text-red-500 text-xs font-bold mt-1.5">{{ form.errors['riddles.' + index + '.title'] }}</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">XP Victoire</label>
                                        <div class="relative">
                                            <input 
                                                v-model="riddle.points_base" 
                                                type="number" 
                                                class="w-full bg-white border border-[#E0531C]/20 rounded-xl pl-4 pr-10 py-3 text-sm text-[#2D1B16] focus:outline-none focus:border-[#E0531C] focus:ring-1 focus:ring-[#E0531C]/30 transition-colors font-bold" 
                                            />
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-black text-[#E0531C]/50 uppercase tracking-wider">XP</span>
                                        </div>
                                        <p v-if="form.errors['riddles.' + index + '.points_base']" class="text-red-500 text-xs font-bold mt-1.5">{{ form.errors['riddles.' + index + '.points_base'] }}</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">Chrono (sec)</label>
                                        <div class="relative">
                                            <input 
                                                v-model="riddle.time_limit_seconds" 
                                                type="number" 
                                                class="w-full bg-white border border-[#E0531C]/20 rounded-xl pl-4 pr-10 py-3 text-sm text-[#2D1B16] focus:outline-none focus:border-[#E0531C] focus:ring-1 focus:ring-[#E0531C]/30 transition-colors font-bold" 
                                            />
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[#E0531C]/50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </span>
                                        </div>
                                        <p v-if="form.errors['riddles.' + index + '.time_limit_seconds']" class="text-red-500 text-xs font-bold mt-1.5">{{ form.errors['riddles.' + index + '.time_limit_seconds'] }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-2">L'énigme / La devinette culturelle</label>
                                    <textarea
                                        v-model="riddle.question"
                                        rows="4"
                                        class="w-full bg-white border border-[#E0531C]/20 rounded-xl px-4 py-3 text-sm text-[#2D1B16] placeholder-[#5C4033]/40 focus:outline-none focus:border-[#E0531C] focus:ring-1 focus:ring-[#E0531C]/30 transition-colors leading-relaxed"
                                        placeholder="Ex: Je suis un roi célèbre d'Abomey, mon emblème contient un oiseau pillard..."
                                    ></textarea>
                                    <p v-if="form.errors['riddles.' + index + '.question']" class="text-red-500 text-xs font-bold mt-1.5">{{ form.errors['riddles.' + index + '.question'] }}</p>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    
                                    <div class="bg-[#FFF7EB] border border-[#E0531C]/10 rounded-xl p-5 space-y-4">
                                        <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16]">
                                            4 Images d'illustrations <span class="text-[#5C4033]/50">(Indices visuels)</span>
                                        </label>
                                        
                                        <label class="flex flex-col items-center justify-center w-full h-12 bg-white hover:bg-[#FFF3DF] border border-dashed border-[#E0531C]/40 rounded-xl cursor-pointer transition-colors group">
                                            <span class="text-xs font-bold text-[#5C4033]/60 group-hover:text-[#5C4033]/80 transition-colors">Parcourir les fichiers d'indices</span>
                                            <input
                                                type="file"
                                                multiple
                                                accept="image/jpeg,image/png"
                                                @input="handleMultipleUpload(index, $event.target.files)"
                                                class="hidden"
                                            />
                                        </label>
                                        
                                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                            <div v-for="imgIdx in [0, 1, 2, 3]" :key="imgIdx" class="aspect-square bg-white border border-[#E0531C]/10 rounded-xl p-1 overflow-hidden flex items-center justify-center relative group">
                                                <img 
                                                    v-if="riddle.previews[imgIdx]" 
                                                    :src="riddle.previews[imgIdx]" 
                                                    class="w-full h-full object-cover rounded-lg"
                                                />
                                                <img 
                                                    v-else-if="riddle.existing_images.find(i => i.display_order === imgIdx + 1)" 
                                                    :src="riddle.existing_images.find(i => i.display_order === imgIdx + 1).image_url" 
                                                    class="w-full h-full object-cover rounded-lg"
                                                />
                                                <span v-else class="text-[10px] font-black text-[#5C4033]/20 uppercase tracking-widest">Slot {{ imgIdx + 1 }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white border border-[#E0531C]/10 rounded-xl p-5">
                                        <h4 class="text-xs uppercase tracking-widest text-[#E0531C] font-black mb-4">
                                            Options de réponse (QCM)
                                        </h4>
                                        
                                        <div class="space-y-3">
                                            <div v-for="(opt, oIndex) in riddle.options" :key="oIndex" class="flex items-center gap-3">
                                                <input
                                                    type="radio"
                                                    :name="'correct_' + riddle.difficulty"
                                                    :value="riddle.options[oIndex]"
                                                    v-model="riddle.answer"
                                                    class="w-4 h-4 text-[#E0531C] bg-white border-[#E0531C]/20 focus:ring-0 focus:ring-offset-0 accent-[#E0531C] cursor-pointer"
                                                    title="Définir comme la réponse exacte"
                                                />
                                                <input
                                                    v-model="riddle.options[oIndex]"
                                                    type="text"
                                                    class="w-full bg-white border border-[#E0531C]/20 rounded-xl px-3 py-2 text-xs text-[#2D1B16] placeholder-[#5C4033]/40 focus:outline-none focus:border-[#E0531C] transition-colors"
                                                    :placeholder="'Alternative ' + (oIndex + 1)"
                                                />
                                            </div>
                                        </div>
                                        
                                        <p v-if="form.errors['riddles.' + index + '.options']" class="text-red-500 text-xs font-bold mt-2">{{ form.errors['riddles.' + index + '.options'] }}</p>
                                        <p v-if="form.errors['riddles.' + index + '.answer']" class="text-red-500 text-xs font-bold mt-2">{{ form.errors['riddles.' + index + '.answer'] }}</p>
                                        
                                        <p class="text-[10px] font-medium text-[#5C4033]/60 mt-4 italic flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2M7 9h.01M7 13h.01M7 17h.01M11 9h.01M11 13h.01M11 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
                                            Activez le sélecteur rond pour désigner la seule bonne réponse attendue.
                                        </p>
                                    </div>
                                </div>

                                <div class="bg-[#FFF3DF] border border-[#E0531C]/10 rounded-xl p-5 md:p-6 space-y-4">
                                    <div class="flex justify-between items-center">
                                        <h4 class="text-xs uppercase tracking-widest text-[#E0531C] font-black flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Indices Déblocables</span>
                                        </h4>
                                        <button 
                                            type="button" 
                                            @click="addHint(index)" 
                                            v-if="riddle.hints.length < 3"
                                            class="bg-white text-[#E0531C] border border-[#E0531C]/20 text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-lg hover:bg-[#FFF7EB] transition-colors"
                                        >
                                            + 
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div v-for="(hint, hIndex) in riddle.hints" :key="hIndex" class="bg-white border border-[#E0531C]/10 rounded-xl p-4 flex flex-col justify-between shadow-sm">
                                            <div>
                                                <div class="flex justify-between items-center mb-2.5">
                                                    <span class="text-[10px] font-black text-[#E0531C] uppercase tracking-widest">Indice #{{ hIndex + 1 }}</span>
                                                    <button @click="removeHint(index, hIndex)" type="button" class="text-[10px] font-bold text-red-500/60 hover:text-red-500 uppercase tracking-wider transition-colors">Retirer</button>
                                                </div>
                                                <textarea
                                                    v-model="hint.content"
                                                    rows="2"
                                                    class="w-full bg-white border border-[#E0531C]/10 rounded-lg px-3 py-2 text-xs text-[#2D1B16] placeholder-[#5C4033]/40 focus:outline-none focus:border-[#E0531C] transition-colors resize-none leading-relaxed"
                                                    placeholder="Rédigez le texte d'aide..."
                                                ></textarea>
                                            </div>
                                            
                                            <div class="flex items-center gap-2 mt-4 pt-3 border-t border-[#E0531C]/10">
                                                <label class="text-[10px] font-black uppercase tracking-wider text-[#5C4033]/60 shrink-0">Malus :</label>
                                                <div class="relative w-20">
                                                    <input
                                                        v-model="hint.points_penalty"
                                                        type="number"
                                                        class="w-full bg-white border border-[#E0531C]/10 rounded-lg pl-2 pr-7 py-1 text-xs font-bold text-[#2D1B16] text-center focus:outline-none focus:border-[#E0531C]"
                                                    />
                                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] font-bold text-[#5C4033]/50">XP</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="riddle.hints.length === 0" class="text-center py-4 text-xs font-medium text-[#5C4033]/50 italic">
                                        Aucun indice d'aide configuré pour cette épreuve. Les joueurs ne pourront compter que sur eux-mêmes.
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
