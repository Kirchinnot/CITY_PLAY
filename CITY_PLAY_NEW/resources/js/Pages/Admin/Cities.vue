<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    cities: {
        type: Array,
        default: () => []
    },
    errors: {
        type: Object,
        default: () => ({})
    },
});

const isModalOpen = ref(false);
const isEditing = ref(false);
let resetTimeout = null;

const form = useForm({
    id: null,
    name: '',
    description: '',
    retention_days: 365,
    outro_config: {
        message: '',
        recommendations: '',
        restaurant_tip: '',
        shop_url: '',
    }
});

const openCreateModal = () => {
    if (resetTimeout) clearTimeout(resetTimeout);
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (city) => {
    if (resetTimeout) clearTimeout(resetTimeout);
    isEditing.value = true;
    form.id = city.id;
    form.name = city.name;
    form.description = city.description;
    form.retention_days = city.retention_days || 365;
    form.outro_config = city.outro_config || { message: '', recommendations: '', restaurant_tip: '', shop_url: '', };
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    resetTimeout = setTimeout(() => {
        form.reset();
        form.clearErrors();
    }, 300);
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.cities.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.cities.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteCity = (cityId) => {
    if (confirm('Voulez-vous vraiment supprimer ce parcours ? Cette action est irréversible.')) {
        router.delete(route('admin.cities.destroy', cityId));
    }
};

const publish = (cityId) => { router.post(route('admin.cities.publish', cityId)); };
const unpublish = (cityId) => { router.post(route('admin.cities.unpublish', cityId)); };
</script>

<template>
    <Head title="CityPlay - Grimoire des Cités" />

    <AdminLayout>
        <template #header>
            <div class="max-w-7xl mx-auto w-full flex flex-col gap-4 md:flex-row md:justify-between md:items-center py-4 px-4 sm:px-0">
                <div class="flex flex-col justify-center gap-1">
                    <h2 class="font-sans text-2xl font-black text-[#2D1B16] m-0 leading-tight">
                        Grimoire des Cités
                    </h2>
                    <p class="text-xs text-[#5C4033]/70 font-semibold mt-1 max-w-xl">
                        Configurez les mythes urbains et aventures du Bénin avec un rendu lumineux et compact.
                    </p>
                </div>
                <button @click="openCreateModal" class="group relative overflow-hidden flex items-center justify-center gap-2.5 px-5 py-3 rounded-2xl bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black text-xs uppercase tracking-wider shadow-[0_20px_50px_-35px_rgba(224,83,28,0.8)] active:scale-95 transition-all duration-300">
                    <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-shine"></div>
                    <span class="text-xl leading-none font-bold relative z-10">+</span>
                    <span class="relative z-10">Injecter une ville</span>
                </button>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-5 py-6 sm:py-8 sm:px-0">
            <div v-if="errors && errors.publish" class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-xl animate-slide-up">
                <span class="font-black text-red-700 text-sm">Erreur de publication :</span>
                <p class="text-red-600 mt-1 text-xs font-semibold leading-relaxed">{{ errors.publish }}</p>
            </div>

            <div class="grid grid-cols-1 gap-10 items-start">
                <div>
                    <h3 class="font-sans text-lg font-extrabold mb-6 text-[#2D1B16] flex items-center gap-2.5 uppercase tracking-wide px-1 sm:px-0">
                        <svg class="w-5 h-5 text-[#FFB700]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Cités actives du Royaume</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        <div @click="openCreateModal" class="group flex flex-col items-center justify-center cursor-pointer border-2 border-dashed border-[#E0531C]/30 hover:border-[#E0531C]/60 bg-[#FFF7EB] hover:bg-[#FFF3DF] rounded-3xl transition-all duration-300 p-6 shadow-sm hover:shadow-lg hover:shadow-orange-500/10 min-h-[320px]">
                            <div class="w-14 h-14 rounded-full bg-[#FFEBCC] text-[#E0531C] flex items-center justify-center shadow-lg font-black text-3xl mb-4 group-hover:scale-105 transition-transform duration-300 border border-[#FFD69A]">
                                +
                            </div>
                            <h3 class="text-[#E0531C] font-black font-sans text-xs uppercase tracking-wider">
                                Nouveau Mythe Urbain
                            </h3>
                            <p class="text-[11px] text-[#5C4033]/60 text-center font-medium mt-1.5 px-4 leading-normal">
                                Initialisez une nouvelle expérience immersive au Bénin.
                            </p>
                        </div>

                        <div v-for="city in cities" :key="city.id" class="bg-white border border-orange-100/70 rounded-3xl shadow-sm overflow-hidden flex flex-col min-h-[340px] relative hover:shadow-[0_20px_50px_-40px_rgba(224,83,28,0.7)] transition-shadow duration-300">
                            <div class="min-h-[110px] bg-gradient-to-br from-[#FFE5C2] via-[#FFCD7F] to-[#E0531C]/10 relative flex items-end p-4">
                                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_top_left,#FFB700_2px,transparent_22px)] [background-size:18px_18px]"></div>
                                <span :class="[city.is_published ? 'bg-[#2D1B16] text-[#FFB700]' : 'bg-[#E0531C] text-white']" class="absolute top-4 right-4 z-10 px-2.5 py-1 rounded-full font-black text-[9px] uppercase tracking-widest shadow-sm">
                                    {{ city.is_published ? 'Publié' : 'Brouillon' }}
                                }</span>
                                <h3 class="font-sans text-base font-black text-[#2D1B16] m-0 z-10 tracking-tight drop-shadow-sm truncate w-full">
                                    {{ city.name }}
                                }</h3>
                            </div>

                            <div class="p-5 flex flex-col flex-1 justify-between bg-white">
                                <p class="text-[#5C4033]/75 font-semibold text-sm leading-relaxed m-0 line-clamp-2">
                                    {{ city.description }}
                                }</p>

                                <div class="grid grid-cols-2 gap-3 my-4 pt-4 border-t border-orange-100/30">
                                    <div class="bg-[#2D1B16]/5 p-2.5 rounded-xl border border-orange-100/40 flex items-center gap-2.5 shadow-inner">
                                        <svg class="w-4 h-4 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <div>
                                            <span class="block text-[8px] text-[#5C4033]/50 uppercase font-black tracking-wider leading-none">Lieux</span>
                                            <span class="block text-xs font-black text-[#2D1B16] mt-0.5">{{ city.places_count || 0 }} étapes</span>
                                        </div>
                                    </div>
                                    <div class="bg-[#2D1B16]/5 p-2.5 rounded-xl border border-orange-100/40 flex items-center gap-2.5 shadow-inner">
                                        <svg class="w-4 h-4 text-[#FFB700]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <div>
                                            <span class="block text-[8px] text-[#5C4033]/50 uppercase font-black tracking-wider leading-none">RGPD</span>
                                            <span class="block text-xs font-black text-[#2D1B16] mt-0.5">{{ city.retention_days || 365 }} j</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <div class="flex gap-2">
                                        <button @click.stop="openEditModal(city)" class="flex-1 flex items-center justify-center gap-2 py-2 px-3 border border-orange-100/70 hover:border-orange-200 text-xs font-bold text-[#5C4033] bg-orange-50/10 hover:bg-orange-50/40 rounded-xl transition-colors" title="Modifier les infos narratives">
                                            <svg class="w-4 h-4 text-[#5C4033]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h6M4 17.25V20h2.75L17.81 8.94l-2.75-2.75L4 17.25z"/></svg>
                                            <span>Modifier</span>
                                        </button>
                                        <button @click.stop="deleteCity(city.id)" class="p-2 border border-red-100 hover:border-red-200 text-red-500 hover:text-red-600 bg-red-50/20 hover:bg-red-50/50 rounded-xl transition-colors" title="Supprimer définitivement le parcours">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 3h4a1 1 0 011 1v2H9V4a1 1 0 011-1z"/></svg>
                                        </button>
                                    </div>
                                    <button v-if="!city.is_published" @click.stop="publish(city.id)" class="w-full flex items-center justify-center gap-2 py-2.5 bg-gradient-to-r from-[#E0531C] to-[#FFB700] hover:from-[#d94a11] hover:to-[#f4a500] text-white text-xs font-black uppercase tracking-wider rounded-2xl transition-all duration-300 shadow-lg shadow-orange-500/20 active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v12m0 0l4-4m-4 4L8 10"/></svg>
                                        <span>Publier le parcours</span>
                                    </button>
                                    <button v-else @click.stop="unpublish(city.id)" class="w-full flex items-center justify-center gap-2 py-2.5 border border-[#E0531C]/20 hover:bg-[#FFF3DF] text-[#C03911] text-xs font-black uppercase tracking-wider rounded-2xl transition-all duration-200 active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 11V7a5 5 0 0110 0v4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span>Retirer de l'App</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 flex items-center justify-center z-50 p-4 bg-[#2D1B16]/20 backdrop-blur-sm transition-opacity" @click.self="closeModal">
            <div class="bg-[#FFF8F0] border border-[#E0531C]/15 rounded-[28px] w-full max-w-lg shadow-[0_30px_70px_-40px_rgba(224,83,28,0.9)] max-h-[90vh] overflow-y-auto p-6 flex flex-col">
                <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start pb-4 mb-5 border-b border-[#E0531C]/10">
                    <div>
                        <span class="text-[10px] text-[#E0531C] font-black uppercase tracking-wider">CityPlay Régisseur</span>
                        <h3 class="font-sans text-lg font-black text-[#2D1B16] mt-0.5">
                            {{ isEditing ? 'Éditer le mythe' : 'Nouveau mythe urbain' }}
                        </h3>
                    </div>
                    <button type="button" @click="closeModal" class="bg-white border border-[#E0531C]/20 hover:bg-[#FFE3C6] rounded-full w-10 h-10 flex items-center justify-center text-lg text-[#5C4033] font-bold transition-colors">
                        &times;
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="flex flex-col gap-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2 relative">
                            <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-1.5 pl-1">Territoire à explorer</label>
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pt-6 pointer-events-none text-base">
                                <svg class="w-5 h-5 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.5 7-10a7 7 0 10-14 0c0 5.5 7 10 7 10z"/></svg>
                            </span>
                            <input v-model="form.name" type="text" class="w-full h-12 pl-10 border border-[#E0531C]/20 rounded-2xl px-3 font-semibold text-sm focus:border-[#E0531C] focus:ring-[#E0531C]/10 bg-white placeholder-[#5C4033]/30" maxlength="150" required placeholder="Ex: Les secrets de Ouidah" />
                        </div>
                        <div class="relative">
                            <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-1.5 pl-1">Cycle des données BJ</label>
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pt-6 pointer-events-none text-base">
                                <svg class="w-5 h-5 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="13" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></rect><path d="M16 3v4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <input v-model="form.retention_days" type="number" class="w-full h-12 pl-9 border border-[#E0531C]/20 rounded-2xl px-3 font-semibold text-sm focus:border-[#E0531C] focus:ring-[#E0531C]/10 bg-white" min="1" required />
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-[#2D1B16] mb-1.5 pl-1">Le mythe de la cité ( max 500 car. )</label>
                        <span class="absolute top-10 left-3.5 pointer-events-none text-base">
                            <svg class="w-5 h-5 text-[#E0531C]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v2a2 2 0 002 2h4a2 2 0 002-2V2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 7h16v13a2 2 0 01-2 2H6a2 2 0 01-2-2V7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <textarea v-model="form.description" class="w-full p-3 pl-10 border border-[#E0531C]/20 rounded-2xl font-medium text-sm focus:border-[#E0531C] focus:ring-[#E0531C]/10 bg-white placeholder-[#5C4033]/30 leading-relaxed" rows="3" maxlength="500" required placeholder="Décrivez l'intrigue et les mystères de cette cité..."></textarea>
                    </div>

                    <div class="border border-[#E0531C]/20 p-4 rounded-2xl bg-[#FFF3DF] shadow-inner">
                        <h4 class="text-[10px] font-black text-[#E0531C] uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span>✨</span> Fin du Parcours Éclaireur
                        </h4>
                        
                        <div class="flex flex-col gap-4">
                            <div>
                                <label class="block text-[9px] font-bold uppercase tracking-wide text-[#2D1B16]/80 mb-1 pl-0.5">Message de fin</label>
                                <textarea v-model="form.outro_config.message" class="w-full p-3 border border-[#E0531C]/20 rounded-2xl font-medium text-xs focus:border-[#E0531C] focus:ring-[#E0531C]/10 bg-white placeholder-[#5C4033]/30" rows="2" placeholder="Félicitations aux joueurs d'avoir décodé l'identité des ancêtres..."></textarea>
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold uppercase tracking-wide text-[#2D1B16]/80 mb-1 pl-0.5">Recommandations touristiques</label>
                                <textarea v-model="form.outro_config.recommendations" class="w-full p-3 border border-[#E0531C]/20 rounded-2xl font-medium text-xs focus:border-[#E0531C] focus:ring-[#E0531C]/10 bg-white placeholder-[#5C4033]/30" rows="2" placeholder="Visitez la boutique artisanale locale ou..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 pt-3 border-t border-[#E0531C]/10 sm:flex-row">
                        <button type="button" @click="closeModal" class="flex-1 h-12 border border-[#E0531C]/20 hover:bg-[#FFF3DF] text-[#5C4033] font-bold text-xs uppercase tracking-wider rounded-2xl transition-colors">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 h-12 bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black text-xs uppercase tracking-wider rounded-2xl hover:opacity-95 active:scale-95 transition-all disabled:opacity-40" :disabled="form.processing">
                            {{ form.processing ? 'Enregistrement...' : 'Sauvegarder l\'aventure' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Floating create button for mobile -->
        <button @click="openCreateModal" aria-label="Créer une ville" class="md:hidden fixed bottom-6 right-4 z-50 p-4 rounded-full bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white shadow-lg active:scale-95 transition-all duration-200">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
        </button>
    </AdminLayout>
</template>

<style scoped>
/* Fléch d'animation de brillance au survol */
@keyframes shine {
    100% { transform: translateX(100%); }
}
.group:hover .group-hover\:animate-shine {
    animation: shine 0.7s ease-out;
}
</style>