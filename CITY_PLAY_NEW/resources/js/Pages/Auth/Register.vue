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
        <Head title="Inscription" />

        <div class="mb-8">
            <h2 class="text-3xl font-black text-cityplay-brown uppercase tracking-tight">Nouvelle aventure ?</h2>
            <p class="text-gray-500 font-medium">Rejoins CityPlay et explore ta ville autrement.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Ton nom / Pseudo" class="text-cityplay-brown font-bold" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full border-2 border-cityplay-lime/30 focus:border-cityplay-orange focus:ring-cityplay-orange rounded-xl shadow-sm"
                    v-model="form.name"
                    required
                    autofocus
                    placeholder="Ex: ExplorateurLyon69"
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" class="text-cityplay-brown font-bold" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-2 border-cityplay-lime/30 focus:border-cityplay-orange focus:ring-cityplay-orange rounded-xl shadow-sm"
                    v-model="form.email"
                    required
                    placeholder="ton-email@exemple.com"
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Mot de passe" class="text-cityplay-brown font-bold" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full border-2 border-cityplay-lime/30 focus:border-cityplay-orange focus:ring-cityplay-orange rounded-xl shadow-sm"
                    v-model="form.password"
                    required
                    placeholder="••••••••"
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirme ton mot de passe"
                    class="text-cityplay-brown font-bold"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full border-2 border-cityplay-lime/30 focus:border-cityplay-orange focus:ring-cityplay-orange rounded-xl shadow-sm"
                    v-model="form.password_confirmation"
                    required
                    placeholder="••••••••"
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <!-- Double Identification (2FA) switch -->
            <div class="mt-4 p-4 rounded-2xl bg-amber-500/5 border border-amber-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-black text-cityplay-brown">Activer la double identification (2FA)</p>
                        <p class="text-[10px] text-gray-500 font-medium">Un code de sécurité par SMS/e-mail vous sera demandé.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" v-model="form.two_factor_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cityplay-orange"></div>
                    </label>
                </div>
            </div>

            <!-- CGU & Data Policy Checkbox -->
            <div class="mt-4 flex items-start gap-3">
                <input
                    id="accept_cgu"
                    type="checkbox"
                    v-model="form.accept_cgu"
                    required
                    class="mt-1 rounded border-gray-300 text-cityplay-orange focus:ring-cityplay-orange"
                />
                <label for="accept_cgu" class="text-[11px] text-gray-500 font-semibold leading-relaxed">
                    J'accepte les <a href="#" class="text-cityplay-orange font-bold hover:underline">Conditions Générales d'Utilisation (CGU)</a> et la <a href="#" class="text-cityplay-orange font-bold hover:underline">politique de gestion des données</a>.
                </label>
            </div>

            <div class="pt-4">
                <PrimaryButton
                    class="w-full justify-center py-4 bg-cityplay-orange hover:bg-cityplay-yellow text-white font-black text-lg rounded-2xl shadow-lg transform transition active:scale-95 border-b-4 border-cityplay-brown/20 uppercase"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    CRÉER MON COMPTE
                </PrimaryButton>
            </div>

            <div class="text-center mt-6">
                <p class="text-gray-500 font-medium">
                    Déjà inscrit ?
                    <Link
                        :href="route('login')"
                        class="text-cityplay-orange font-black hover:underline"
                    >
                        Connecte-toi ici !
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
