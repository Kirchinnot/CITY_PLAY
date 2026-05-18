<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({ password: '' });

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('player.profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="profile-card">
        <!-- En-tête -->
        <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);">
                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-black text-white uppercase tracking-widest">Supprimer le compte</h2>
                <p class="text-[11px] text-white/30 font-medium mt-0.5">Action irréversible</p>
            </div>
        </div>

        <!-- Avertissement -->
        <div class="flex items-start gap-3 p-4 rounded-2xl mb-6"
             style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.15);">
            <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <p class="text-xs text-red-400/80 font-medium leading-relaxed">
                Une fois votre compte supprimé, toutes vos données — parties, scores et badges — seront définitivement effacées. Cette action est irréversible.
            </p>
        </div>

        <button @click="confirmUserDeletion" class="delete-btn">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Supprimer mon compte
        </button>
    </section>

    <!-- ── MODAL CONFIRMATION ── -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-all duration-200"
            leave-to-class="opacity-0"
        >
            <div v-if="confirmingUserDeletion"
                 class="fixed inset-0 z-[80] flex items-center justify-center p-6"
                 style="background: rgba(13,17,23,0.92); backdrop-filter: blur(16px);">

                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 scale-95"
                    leave-active-class="transition-all duration-200"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="confirmingUserDeletion" class="modal-box w-full max-w-sm">
                        <!-- Icône danger -->
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-5"
                             style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.25);">
                            <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>

                        <h3 class="text-lg font-black text-white text-center mb-1">Confirmer la suppression</h3>
                        <p class="text-xs text-white/35 text-center font-medium mb-6 leading-relaxed">
                            Entrez votre mot de passe pour confirmer la suppression définitive de votre compte.
                        </p>

                        <!-- Champ mot de passe -->
                        <div class="mb-4">
                            <input
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                placeholder="Votre mot de passe"
                                @keyup.enter="deleteUser"
                                class="modal-input"
                            />
                            <InputError :message="form.errors.password" class="mt-1.5 text-xs text-red-400" />
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button @click="closeModal" class="cancel-btn flex-1">Annuler</button>
                            <button
                                @click="deleteUser"
                                :disabled="form.processing"
                                class="confirm-delete-btn flex-1"
                            >
                                <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span>{{ form.processing ? 'Suppression...' : 'Supprimer' }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.profile-card {
    background: linear-gradient(145deg, #161d2e 0%, #111827 100%);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 24px;
    padding: 24px;
}
.delete-btn {
    display: inline-flex; align-items: center; gap: 8px;
    height: 44px; padding: 0 20px; border-radius: 14px;
    font-size: 12px; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.1em;
    color: #f87171;
    background: rgba(239,68,68,0.08);
    border: 1px solid rgba(239,68,68,0.2);
    transition: background 0.2s, border-color 0.2s, transform 0.15s;
}
.delete-btn:hover { background: rgba(239,68,68,0.14); border-color: rgba(239,68,68,0.35); transform: translateY(-1px); }
.delete-btn:active { transform: scale(0.97); }

/* Modal */
.modal-box {
    background: linear-gradient(145deg, #161d2e 0%, #111827 100%);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 28px;
    padding: 28px;
    box-shadow: 0 32px 80px rgba(0,0,0,0.6);
}
.modal-input {
    width: 100%; height: 48px;
    padding: 0 16px; border-radius: 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(239,68,68,0.2);
    color: #fff; font-size: 14px; font-weight: 600;
    outline: none;
    transition: border-color 0.2s;
}
.modal-input::placeholder { color: rgba(255,255,255,0.2); }
.modal-input:focus { border-color: rgba(239,68,68,0.5); }
.cancel-btn {
    height: 44px; border-radius: 14px;
    font-size: 12px; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.1em;
    color: rgba(255,255,255,0.4);
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    transition: background 0.15s, color 0.15s;
}
.cancel-btn:hover { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); }
.confirm-delete-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    height: 44px; border-radius: 14px;
    font-size: 12px; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.1em; color: #fff;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 6px 20px rgba(239,68,68,0.35);
    transition: transform 0.15s, box-shadow 0.15s;
}
.confirm-delete-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(239,68,68,0.45); }
.confirm-delete-btn:active:not(:disabled) { transform: scale(0.97); }
.confirm-delete-btn:disabled { opacity: 0.5; cursor: not-allowed; }
@keyframes spin { to { transform: rotate(360deg); } }
.animate-spin { animation: spin 0.8s linear infinite; }
</style>
