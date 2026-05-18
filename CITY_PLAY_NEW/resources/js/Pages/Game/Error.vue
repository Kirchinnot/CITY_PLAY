<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    message: {
        type: String,
        default: 'Une erreur est survenue lors de l\'accès à la partie.'
    }
});
</script>

<template>
    <GuestLayout>
        <Head title="Oups !" />

        <div class="text-center py-8">
            <div class="text-6xl mb-6">🏜️</div>
            <h2 class="text-3xl font-black text-cityplay-brown uppercase tracking-tight mb-4">Mince alors !</h2>
            <p class="text-gray-500 font-medium mb-8 leading-relaxed">
                {{ message }}
            </p>

            <Link
                href="/"
                class="inline-block px-8 py-4 bg-cityplay-brown text-white font-black text-lg rounded-2xl shadow-lg transform transition active:scale-95 uppercase tracking-wider"
            >
                Retour à l'accueil
            </Link>
        </div>
    </GuestLayout>
</template>
