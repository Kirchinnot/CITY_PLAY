<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;

const form = useForm({
    name:  user.name,
    email: user.email,
});
</script>

<template>
    <section class="profile-card">
        <!-- En-tête section -->
        <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(214,90,49,0.12); border: 1px solid rgba(214,90,49,0.2);">
                <svg class="w-4 h-4 text-[#d65a31]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-black text-white uppercase tracking-widest">Informations</h2>
                <p class="text-[11px] text-white/30 font-medium mt-0.5">Nom d'affichage et adresse e-mail</p>
            </div>
        </div>

        <form @submit.prevent="form.patch(route('player.profile.update'))" class="space-y-4">

            <!-- Nom -->
            <div class="field-group">
                <label for="name" class="field-label">Nom d'utilisateur</label>
                <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    required autofocus autocomplete="name"
                    class="field-input"
                    placeholder="Votre nom"
                />
                <InputError :message="form.errors.name" class="mt-1.5 text-xs text-red-400" />
            </div>

            <!-- Email -->
            <div class="field-group">
                <label for="email" class="field-label">Adresse e-mail</label>
                <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required autocomplete="username"
                    class="field-input"
                    placeholder="votre@email.com"
                />
                <InputError :message="form.errors.email" class="mt-1.5 text-xs text-red-400" />
            </div>

            <!-- Vérification email -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null"
                 class="flex items-start gap-3 p-3 rounded-xl"
                 style="background: rgba(234,179,8,0.08); border: 1px solid rgba(234,179,8,0.2);">
                <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <div>
                    <p class="text-xs font-bold text-yellow-400">E-mail non vérifié</p>
                    <Link :href="route('verification.send')" method="post" as="button"
                          class="text-[11px] text-yellow-400/70 underline hover:text-yellow-400 transition mt-0.5">
                        Renvoyer le lien de vérification
                    </Link>
                </div>
            </div>
            <div v-if="status === 'verification-link-sent'"
                 class="p-3 rounded-xl text-xs font-bold text-emerald-400"
                 style="background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.2);">
                ✓ Lien de vérification envoyé.
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4 pt-2">
                <button type="submit" :disabled="form.processing" class="save-btn">
                    <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span>{{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}</span>
                </button>
                <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-300" leave-to-class="opacity-0">
                    <span v-if="form.recentlySuccessful" class="text-xs font-bold text-emerald-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Sauvegardé
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
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: rgba(255,255,255,0.4);
}
.field-input {
    width: 100%;
    height: 48px;
    padding: 0 16px;
    border-radius: 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    outline: none;
    transition: border-color 0.2s, background 0.2s;
}
.field-input::placeholder { color: rgba(255,255,255,0.2); }
.field-input:focus {
    border-color: rgba(214,90,49,0.5);
    background: rgba(214,90,49,0.04);
}
.save-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 44px;
    padding: 0 24px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #fff;
    background: linear-gradient(135deg, #d65a31, #b84a24);
    box-shadow: 0 6px 20px rgba(214,90,49,0.35);
    transition: transform 0.15s, box-shadow 0.15s;
}
.save-btn:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(214,90,49,0.45); }
.save-btn:active:not(:disabled) { transform: scale(0.97); }
.save-btn:disabled { opacity: 0.5; cursor: not-allowed; }
@keyframes spin { to { transform: rotate(360deg); } }
.animate-spin { animation: spin 0.8s linear infinite; }
</style>
