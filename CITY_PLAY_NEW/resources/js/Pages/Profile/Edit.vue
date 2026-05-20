<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import PlayerLayout from '@/Layouts/PlayerLayout.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { confirmModal } from '@/composables/usePrimeDialogs';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Initiales avatar
const initials = computed(() => {
    const parts = (user.value?.name || '').split(' ');
    return parts.length >= 2
        ? parts[0][0] + parts[1][0]
        : (parts[0]?.[0] || '?');
});

// Onglet actif
const activeTab = ref('info');
const tabs = [
    { id: 'info',     label: 'Infos',      icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { id: 'password', label: 'Sécurité',   icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
    { id: 'danger',   label: 'Compte',     icon: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' },
];

// Refs GSAP
const heroRef    = ref(null);
const tabsRef    = ref(null);
const contentRef = ref(null);

onMounted(() => {
    const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
    tl.fromTo(heroRef.value,    { opacity: 0, y: 32 }, { opacity: 1, y: 0, duration: 0.7 })
      .fromTo(tabsRef.value,    { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.5 }, '-=0.3')
      .fromTo(contentRef.value, { opacity: 0, y: 24 }, { opacity: 1, y: 0, duration: 0.5 }, '-=0.3');
});

const switchTab = (id) => {
    gsap.fromTo(contentRef.value,
        { opacity: 0, y: 12 },
        { opacity: 1, y: 0, duration: 0.35, ease: 'power2.out' }
    );
    activeTab.value = id;
};

// ── Gestion Déconnexion Premium ──
const showLogoutModal = ref(false);
const isLoggingOut = ref(false);
const isDeletingProfile = ref(false);

const logoutAndKeep = () => {
    isLoggingOut.value = true;
    router.post(route('logout'), {}, {
        onFinish: () => { isLoggingOut.value = false; }
    });
};

const logoutAndDelete = async () => {
    if (!(await confirmModal({
        header: 'Supprimer le compte',
        message: 'Cette action supprimera définitivement votre compte et votre progression après votre déconnexion. Confirmer ?',
        acceptLabel: 'Oui, supprimer',
        rejectLabel: 'Annuler',
    }))) return;

    isDeletingProfile.value = true;
    router.post(route('profile.logout-delete'), {}, {
        onFinish: () => { isDeletingProfile.value = false; }
    });
};
</script>

<template>
    <Head title="Mon Profil" />

    <PlayerLayout>
        <div class="max-w-lg mx-auto pb-10">

            <!-- ── HERO PROFIL ── -->
            <div ref="heroRef" class="relative mb-6 rounded-[28px] overflow-hidden"
                 style="background: linear-gradient(145deg, #161d2e 0%, #111827 100%); border: 1px solid rgba(255,255,255,0.07);">
                <!-- Lueur orange coin haut-droit -->
                <div class="absolute top-0 right-0 w-48 h-48 rounded-full pointer-events-none"
                     style="background: radial-gradient(circle, rgba(214,90,49,0.15) 0%, transparent 70%); transform: translate(30%, -30%);"></div>
                <!-- Lueur indigo coin bas-gauche -->
                <div class="absolute bottom-0 left-0 w-40 h-40 rounded-full pointer-events-none"
                     style="background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, transparent 70%); transform: translate(-30%, 30%);"></div>

                <div class="relative z-10 p-6 flex items-center gap-5">
                    
                    <!-- Bouton Déconnexion Premium -->
                    <button @click="showLogoutModal = true" class="absolute top-4 right-4 w-9 h-9 flex items-center justify-center rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>

                    <!-- Avatar -->
                    <div class="relative shrink-0 mt-2">
                        <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-2xl font-black text-white shadow-xl"
                             style="background: linear-gradient(135deg, #d65a31 0%, #b84a24 100%); box-shadow: 0 12px 32px rgba(214,90,49,0.4);">
                            {{ initials.toUpperCase() }}
                        </div>
                        <!-- Badge rôle -->
                        <div class="absolute -bottom-2 -right-2 px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest"
                             style="background: rgba(99,102,241,0.2); border: 1px solid rgba(99,102,241,0.35); color: #a5b4fc;">
                            {{ user.role || 'Joueur' }}
                        </div>
                    </div>

                    <!-- Infos -->
                    <div class="flex-1 min-w-0 mt-2">
                        <h1 class="text-xl font-black text-white leading-tight truncate pr-8">{{ user.name }}</h1>
                        <p class="text-sm text-white/40 font-medium truncate mt-0.5">{{ user.email }}</p>
                        <!-- Stats rapides -->
                        <div class="flex items-center gap-3 mt-3">
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg"
                                 style="background: rgba(214,90,49,0.12); border: 1px solid rgba(214,90,49,0.2);">
                                <svg class="w-3 h-3 text-[#d65a31]" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                <span class="text-[10px] font-black text-[#d65a31]">Explorateur</span>
                            </div>
                            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg"
                                 style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2);">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span class="text-[10px] font-black text-emerald-400">Actif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── ONGLETS ── -->
            <div ref="tabsRef" class="flex gap-2 mb-5 p-1 rounded-2xl" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="switchTab(tab.id)"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-200"
                    :style="activeTab === tab.id
                        ? 'background: rgba(214,90,49,0.15); border: 1px solid rgba(214,90,49,0.3); color: #d65a31;'
                        : 'background: transparent; border: 1px solid transparent; color: rgba(255,255,255,0.3);'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon"/>
                    </svg>
                    {{ tab.label }}
                </button>
            </div>

            <!-- ── CONTENU ONGLETS ── -->
            <div ref="contentRef">
                <UpdateProfileInformationForm
                    v-if="activeTab === 'info'"
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
                <UpdatePasswordForm v-if="activeTab === 'password'" />
                <DeleteUserForm    v-if="activeTab === 'danger'" />
            </div>

        </div>

        <!-- ══ MODAL DÉCONNEXION & RÉTENTION RGPD ══ -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" leave-active-class="transition-all duration-150" leave-to-class="opacity-0">
                <div v-if="showLogoutModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4" style="background: rgba(13,17,23,0.9); backdrop-filter: blur(12px);" @click.self="showLogoutModal = false">
                    <div class="bg-[#1c2128] border border-white/10 w-full max-w-sm rounded-[28px] overflow-hidden shadow-2xl animate-bounce-in p-6">
                        
                        <div class="w-16 h-16 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/20">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>

                        <h3 class="text-xl font-black text-white text-center uppercase tracking-tight mb-2">Déconnexion</h3>
                        <p class="text-xs text-gray-400 text-center font-medium leading-relaxed mb-6">
                            Souhaitez-vous supprimer définitivement votre profil ou le conserver pour continuer plus tard ?
                        </p>

                        <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 mb-6">
                            <p class="text-[10px] text-amber-500/90 font-bold leading-relaxed text-center">
                                <span class="text-amber-400 block mb-1 text-sm">⚠️ Info Rétention</span>
                                Si vous le conservez, votre profil sera actif pendant la durée maximale de conservation des données définie par la mairie. Au-delà, vos données seront purgées.
                            </p>
                        </div>

                        <div class="space-y-3">
                            <button @click="logoutAndKeep" :disabled="isLoggingOut || isDeletingProfile"
                                    class="w-full h-12 rounded-xl text-xs font-black uppercase tracking-widest text-white flex items-center justify-center gap-2 transition bg-[#C85C32] hover:bg-[#A04422] shadow-lg shadow-[#C85C32]/20">
                                <svg v-if="isLoggingOut" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span>Conserver et Quitter</span>
                            </button>
                            <button @click="logoutAndDelete" :disabled="isLoggingOut || isDeletingProfile"
                                    class="w-full h-12 rounded-xl text-xs font-black uppercase tracking-widest text-white flex items-center justify-center gap-2 transition bg-red-500/10 border border-red-500/20 hover:bg-red-500/20 text-red-500 hover:text-red-400">
                                <svg v-if="isDeletingProfile" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span>Supprimer le profil</span>
                            </button>
                            <button @click="showLogoutModal = false" :disabled="isLoggingOut || isDeletingProfile"
                                    class="w-full h-10 mt-2 text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-white transition">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </PlayerLayout>
</template>
