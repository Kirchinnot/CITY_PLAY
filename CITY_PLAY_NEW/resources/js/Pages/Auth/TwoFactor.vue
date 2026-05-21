<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({ code: '' });

const submit = () => {
    form.post(route('two-factor'));
};

const resend = () => {
    form.post(route('two-factor.resend'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Vérification 2FA" />

        <div class="w-full max-w-sm mx-auto text-center py-8">
            <h2 class="text-lg font-bold">Code de sécurité requis</h2>
            <p class="text-sm text-gray-600 mt-2">Nous avons envoyé un code à l'adresse liée à ton compte. Entre-le pour terminer la connexion.</p>

            <form @submit.prevent="submit" class="mt-6 space-y-3">
                <input v-model="form.code" type="text" maxlength="6" placeholder="000000" class="w-full p-3 rounded-lg border" />
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 btn-primary">Vérifier</button>
                    <button type="button" @click.prevent="resend" class="flex-1 btn-outline">Renvoyer</button>
                </div>
            </form>
            <p class="text-xs text-gray-500 mt-3">Problème ? <Link :href="route('login')" class="text-orange-600">Retourner au login</Link></p>
        </div>
    </GuestLayout>
</template>

<style scoped>
.btn-primary { background: linear-gradient(90deg,#E0531C,#FFB700); color: white; padding: 0.6rem 1rem; border-radius: 0.75rem; font-weight: 800; }
.btn-outline { background: transparent; border: 2px solid #E0531C; color: #E0531C; padding: 0.6rem 1rem; border-radius: 0.75rem; font-weight: 800; }
</style>
