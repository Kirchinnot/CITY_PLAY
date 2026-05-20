<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion Éclaireur" />

        <div v-if="status" class="mb-4 text-sm font-bold text-green-600 bg-green-50 p-3 rounded-xl border border-green-100 text-center">
            {{ status }}
        </div>

        <div class="w-full max-w-sm mx-auto">
            <div class="mb-5 text-center">
                <h2 class="text-xl font-black text-[#2D1B16] uppercase tracking-tight">
                    Connexion Éclaireur
                </h2>
                <p class="text-[11px] text-[#5C4033]/70 font-semibold mt-0.5">
                    Reprends ton exploration urbaine au Bénin.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="email" value="Identifiant ou Email" class="text-[#2D1B16] text-[9px] font-black uppercase tracking-wider pl-1" />
                    <div class="relative mt-0.5">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#5C4033]/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                            </svg>
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            class="block w-full h-11 pl-9 border border-orange-100/80 focus:border-[#E0531C] focus:ring-[#E0531C] rounded-xl shadow-sm font-semibold text-sm placeholder-[#5C4033]/25 bg-white/50"
                            v-model="form.email"
                            required
                            autofocus
                            placeholder="ex: tovi@cityplay.bj"
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-1 pl-1 font-bold text-[11px]" :message="form.errors.email" />
                </div>

                <div>
                    <div class="flex justify-between items-center px-1">
                        <InputLabel for="password" value="Code secret (Mot de passe)" class="text-[#2D1B16] text-[9px] font-black uppercase tracking-wider" />
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-[9px] text-[#5C4033]/60 font-bold hover:text-[#E0531C] uppercase tracking-wide transition-colors"
                        >
                            Perdu ?
                        </Link>
                    </div>
                    <div class="relative mt-0.5">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[#5C4033]/40">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m-5-3a3 3 0 11-6 0 3 3 0 016 0zM4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="block w-full h-11 pl-9 border border-orange-100/80 focus:border-[#E0531C] focus:ring-[#E0531C] rounded-xl shadow-sm font-semibold text-sm placeholder-[#5C4033]/25 bg-white/50"
                            v-model="form.password"
                            required
                            placeholder="••••••••"
                            autocomplete="current-password"
                        />
                    </div>
                    <InputError class="mt-1 pl-1 font-bold text-[11px]" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center cursor-pointer select-none gap-2">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            class="rounded border-orange-200 text-[#E0531C] focus:ring-[#E0531C] h-3.5 w-3.5 transition-all cursor-pointer"
                        />
                        <span class="text-[10px] text-[#5C4033]/80 font-semibold">Rester connecté</span>
                    </label>
                </div>

                <div class="pt-2">
                    <PrimaryButton
                        class="btn-primary-pulse w-full justify-center h-12 bg-gradient-to-r from-[#E0531C] to-[#FFB700] text-white font-black text-xs rounded-xl shadow-md active:scale-[0.98] transition-all uppercase tracking-widest border-none"
                        :class="{ 'opacity-40 pointer-events-none': form.processing }"
                        :disabled="form.processing"
                    >
                        REPRENDRE L'EXPLORATION
                    </PrimaryButton>
                </div>

                <div class="text-center pt-2">
                    <p class="text-[11px] text-[#5C4033]/80 font-semibold">
                        Nouveau dans la région ?
                        <Link :href="route('register')" class="text-[#E0531C] font-black hover:underline ml-0.5">
                            Rejoindre le clan !
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