<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    accept_cgu: false,
    two_factor_enabled: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Création d'Avatar" />

        <div class="w-full max-w-sm mx-auto">
            <div class="mb-4 text-center">
                <h2 class="text-xl font-black text-[#2D1B16] uppercase tracking-tight">
                    Créer ton Avatar
                </h2>
                <p class="text-[11px] text-[#5C4033]/70 font-semibold mt-0.5">
                    Rejoins les éclaireurs et explore le Bénin.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-3">
                <div>
                    <InputLabel for="name" value="Nom d'explorateur (Pseudo)" class="text-[#2D1B16] text-[9px] font-black uppercase tracking-wider pl-1" />
                    <div class="relative mt-0.5">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#5C4033]/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 01-8 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <TextInput
                            id="name"
                            type="text"
                            class="block w-full h-11 pl-9 border border-orange-100/80 focus:border-[#E0531C] focus:ring-[#E0531C] rounded-xl shadow-sm font-semibold text-sm placeholder-[#5C4033]/25 bg-white/50"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Ex: Gbéto_229"
                            autocomplete="name"
                        />
                    </div>
                    <InputError class="mt-1 pl-1 font-bold text-[11px]" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Canal de communication (Email)" class="text-[#2D1B16] text-[9px] font-black uppercase tracking-wider pl-1" />
                    <div class="relative mt-0.5">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#5C4033]/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full h-11 pl-9 border border-orange-100/80 focus:border-[#E0531C] focus:ring-[#E0531C] rounded-xl shadow-sm font-semibold text-sm placeholder-[#5C4033]/25 bg-white/50"
                            v-model="form.email"
                            required
                            placeholder="ton-email@exemple.com"
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-1 pl-1 font-bold text-[11px]" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Clé de sécurité (Mot de passe)" class="text-[#2D1B16] text-[9px] font-black uppercase tracking-wider pl-1" />
                    <div class="relative mt-0.5">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#5C4033]/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full h-11 pl-9 border border-orange-100/80 focus:border-[#E0531C] focus:ring-[#E0531C] rounded-xl shadow-sm font-semibold text-sm placeholder-[#5C4033]/25 bg-white/50"
                            v-model="form.password"
                            required
                            placeholder="••••••••"
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError class="mt-1 pl-1 font-bold text-[11px]" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirmer la clé" class="text-[#2D1B16] text-[9px] font-black uppercase tracking-wider pl-1" />
                    <div class="relative mt-0.5">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#5C4033]/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="block w-full h-11 pl-9 border border-orange-100/80 focus:border-[#E0531C] focus:ring-[#E0531C] rounded-xl shadow-sm font-semibold text-sm placeholder-[#5C4033]/25 bg-white/50"
                            v-model="form.password_confirmation"
                            required
                            placeholder="••••••••"
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError class="mt-1 pl-1 font-bold text-[11px]" :message="form.errors.password_confirmation" />
                </div>

                <div class="p-2.5 rounded-xl bg-[#00A859]/5 border border-[#00A859]/10 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-[#00A859]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-[11px] font-black text-[#2D1B16] uppercase tracking-wide leading-none">Bouclier 2FA</p>
                            <p class="text-[9px] text-[#5C4033]/60 font-semibold mt-0.5">Sécurise ton score de jeu</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" v-model="form.two_factor_enabled" class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#00A859]"></div>
                    </label>
                </div>

                <div class="flex items-start gap-2 px-1 py-0.5">
                    <input
                        id="accept_cgu"
                        type="checkbox"
                        v-model="form.accept_cgu"
                        required
                        class="mt-0.5 rounded border-orange-200 text-[#E0531C] focus:ring-[#E0531C] h-3.5 w-3.5 transition-all cursor-pointer"
                    />
                    <label for="accept_cgu" class="text-[10px] text-[#5C4033]/80 font-semibold leading-tight cursor-pointer select-none">
                        J'accepte le <a href="#" class="text-[#E0531C] font-black hover:underline">pacte des éclaireurs (CGU)</a>.
                    </label>
                </div>

                <div class="pt-1">
                    <PrimaryButton
                        class="btn-primary-pulse w-full justify-center h-12 bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black text-xs rounded-xl shadow-md active:scale-[0.98] transition-all uppercase tracking-widest border-none"
                        :class="{ 'opacity-40 pointer-events-none': form.processing }"
                        :disabled="form.processing"
                    >
                        FORGER MON PROFIL
                    </PrimaryButton>
                </div>

                <div class="text-center pt-1">
                    <p class="text-[11px] text-[#5C4033]/80 font-semibold">
                        Déjà un compte ?
                        <Link :href="route('login')" class="text-[#E0531C] font-black hover:underline ml-0.5">
                            S'identifier ici !
                        </Link>
                    </p>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>

<style scoped>
.btn-primary-pulse {
    animation: simplePulse 3s infinite;
}
@keyframes simplePulse {
    0%, 100% { box-shadow: 0 4px 15px rgba(224, 83, 28, 0.15); transform: scale(1); }
    50% { box-shadow: 0 8px 20px rgba(224, 83, 28, 0.3); transform: scale(1.01); }
}
</style>