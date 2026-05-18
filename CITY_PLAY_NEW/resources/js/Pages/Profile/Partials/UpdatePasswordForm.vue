<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput        = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};

// Afficher/masquer les mots de passe
const showCurrent  = ref(false);
const showNew      = ref(false);
const showConfirm  = ref(false);
</script>

<template>
    <section class="profile-card">
        <!-- En-tête -->
        <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(99,102,241,0.12); border: 1px solid rgba(99,102,241,0.25);">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-black text-white uppercase tracking-widest">Sécurité</h2>
                <p class="text-[11px] text-white/30 font-medium mt-0.5">Modifier votre mot de passe</p>
            </div>
        </div>

        <form @submit.prevent="updatePassword" class="space-y-4">

            <!-- Mot de passe actuel -->
            <div class="field-group">
                <label for="current_password" class="field-label">Mot de passe actuel</label>
                <div class="field-wrapper">
                    <input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        :type="showCurrent ? 'text' : 'password'"
                        autocomplete="current-password"
                        class="field-input"
                        placeholder="••••••••"
                    />
                    <button type="button" @click="showCurrent = !showCurrent" class="eye-btn">
                        <svg v-if="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <InputError :message="form.errors.current_password" class="mt-1.5 text-xs text-red-400" />
            </div>

            <!-- Nouveau mot de passe -->
            <div class="field-group">
                <label for="password" class="field-label">Nouveau mot de passe</label>
                <div class="field-wrapper">
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        :type="showNew ? 'text' : 'password'"
                        autocomplete="new-password"
                        class="field-input"
                        placeholder="••••••••"
                    />
                    <button type="button" @click="showNew = !showNew" class="eye-btn">
                        <svg v-if="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <InputError :message="form.errors.password" class="mt-1.5 text-xs text-red-400" />
            </div>

            <!-- Confirmation -->
            <div class="field-group">
                <label for="password_confirmation" class="field-label">Confirmer le mot de passe</label>
                <div class="field-wrapper">
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showConfirm ? 'text' : 'password'"
                        autocomplete="new-password"
                        class="field-input"
                        placeholder="••••••••"
                    />
                    <button type="button" @click="showConfirm = !showConfirm" class="eye-btn">
                        <svg v-if="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                <InputError :message="form.errors.password_confirmation" class="mt-1.5 text-xs text-red-400" />
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" :disabled="form.processing" class="save-btn">
                    <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span>{{ form.processing ? 'Mise à jour...' : 'Mettre à jour' }}</span>
                </button>
                <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-300" leave-to-class="opacity-0">
                    <span v-if="form.recentlySuccessful" class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Mis à jour
                    </span>
                </Transition>
            </div>
        </form>
    </section>
</template>

<style scoped>
.profile-card {
    background: linear-gradient(145deg, #161d2e 0%, #111827 100%);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 24px;
    padding: 24px;
}
.field-group { display: flex; flex-direction: column; gap: 6px; }
.field-label {
    font-size: 11px; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.12em;
    color: rgba(255,255,255,0.4);
}
.field-wrapper { position: relative; }
.field-input {
    width: 100%; height: 48px;
    padding: 0 44px 0 16px;
    border-radius: 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    color: #fff; font-size: 14px; font-weight: 600;
    outline: none;
    transition: border-color 0.2s, background 0.2s;
}
.field-input::placeholder { color: rgba(255,255,255,0.2); }
.field-input:focus {
    border-color: rgba(99,102,241,0.5);
    background: rgba(99,102,241,0.04);
}
.eye-btn {
    position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
    color: rgba(255,255,255,0.25);
    transition: color 0.15s;
}
.eye-btn:hover { color: rgba(255,255,255,0.6); }
.save-btn {
    display: inline-flex; align-items: center; gap: 8px;
    height: 44px; padding: 0 24px; border-radius: 14px;
    font-size: 12px; font-weight: 900;
    text-transform: uppercase; letter-spacing: 0.1em; color: #fff;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    box-shadow: 0 6px 20px rgba(99,102,241,0.35);
    transition: transform 0.15s, box-shadow 0.15s;
}
.save-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(99,102,241,0.45); }
.save-btn:active:not(:disabled) { transform: scale(0.97); }
.save-btn:disabled { opacity: 0.5; cursor: not-allowed; }
@keyframes spin { to { transform: rotate(360deg); } }
.animate-spin { animation: spin 0.8s linear infinite; }
</style>
