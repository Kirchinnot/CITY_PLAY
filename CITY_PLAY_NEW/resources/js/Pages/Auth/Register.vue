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
    phone: '',
    password: '',
    password_confirmation: '',
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
<<<<<<< HEAD
                <InputLabel for="password" value="Mot de passe" class="text-cityplay-brown font-bold" />
=======
                <InputLabel for="phone" value="Téléphone" />

                <TextInput
                    id="phone"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    required
                    autocomplete="tel"
                />

                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
>>>>>>> d0f1d232be28ecccb01dbf729987c14746d1aced

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
