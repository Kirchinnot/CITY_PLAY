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
        <Head title="CityPlay - Mince alors !" />

        <div style="text-align: center; padding: 3rem 1rem;">
            <div style="font-size: 4.5rem; margin-bottom: 1.5rem; animation: wobble 2.5s infinite;">🏜️</div>
            
            <h2 style="font-family: var(--font-family-display); font-size: 2rem; font-weight: 800; color: var(--color-primary-dark); text-transform: uppercase; margin: 0 0 1rem 0; letter-spacing: -0.02em;">
                Mince alors !
            </h2>
            
            <p style="font-size: 0.95rem; color: var(--color-text-muted); font-weight: 500; line-height: 1.6; margin: 0 0 2.5rem 0; max-width: 320px; margin-left: auto; margin-right: auto;">
                {{ message }}
            </p>

            <Link
                href="/"
                class="premium-btn premium-btn-primary"
                style="display: inline-flex; width: 100%; max-width: 260px; font-family: var(--font-family-display); font-weight: 800; text-transform: uppercase; padding: 1rem; text-decoration: none; font-size: 1rem;"
            >
                Retour à l'accueil
            </Link>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes wobble {
    0%, 100% { transform: rotate(0deg); }
    15% { transform: rotate(-8deg); }
    30% { transform: rotate(6deg); }
    45% { transform: rotate(-4deg); }
    60% { transform: rotate(2deg); }
    75% { transform: rotate(-1deg); }
}
</style>
