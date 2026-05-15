<script setup>
import Checkbox from '@/Components/Checkbox.vue';
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
        <Head title="Connexion" />

        <div class="mb-8">
            <h2 class="text-3xl font-black text-cityplay-brown uppercase tracking-tight">Bon retour !</h2>
            <p class="text-gray-500 font-medium">Connecte-toi pour continuer l'aventure.</p>
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email" class="text-cityplay-brown font-bold" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-2 border-cityplay-lime/30 focus:border-cityplay-orange focus:ring-cityplay-orange rounded-xl shadow-sm"
                    v-model="form.email"
                    required
                    autofocus
                    placeholder="ton-email@exemple.com"
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Mot de passe" class="text-cityplay-brown font-bold" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-cityplay-orange font-bold hover:underline focus:outline-none"
                    >
                        Oublié ?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full border-2 border-cityplay-lime/30 focus:border-cityplay-orange focus:ring-cityplay-orange rounded-xl shadow-sm"
                    v-model="form.password"
                    required
                    placeholder="••••••••"
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" class="text-cityplay-orange border-2 border-cityplay-lime/30 rounded focus:ring-cityplay-orange" />
                    <span class="ms-2 text-sm text-cityplay-brown font-medium">Se souvenir de moi</span>
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-4 bg-cityplay-orange hover:bg-cityplay-yellow text-white font-black text-lg rounded-2xl shadow-lg transform transition active:scale-95 border-b-4 border-cityplay-brown/20"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    C'EST PARTI !
                </PrimaryButton>
            </div>

            <div class="text-center mt-6">
                <p class="text-gray-500 font-medium">
                    Pas encore de compte ?
                    <Link
                        :href="route('register')"
                        class="text-cityplay-orange font-black hover:underline"
                    >
                        Rejoins-nous !
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
