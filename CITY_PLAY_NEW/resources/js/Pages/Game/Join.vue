<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: Object
});

const form = useForm({});

const submit = () => {
    form.post(route('game.session.join', props.invitation.token));
};

const getLocomotionIcon = (type) => {
    const icons = {
        marche: '🚶',
        velo: '🚲',
        moto: '🛵',
        voiture: '🚗'
    };
    return icons[type] || '🚶';
};

const getDifficultyLabel = (level) => {
    const labels = {
        facile: 'Facile 🏹',
        moyen: 'Moyen 🦁',
        difficile: 'Difficile 👑'
    };
    return labels[level] || level;
};

const getDifficultyColor = (level) => {
    const colors = {
        facile: 'color: var(--color-success)',
        moyen: 'color: var(--color-primary-light)',
        difficile: 'color: var(--color-primary-dark)'
    };
    return colors[level] || 'color: var(--color-primary-light)';
};
</script>

<template>
    <GuestLayout>
        <Head title="CityPlay - Rejoindre l'aventure" />

        <div style="text-align: center; margin-bottom: 2rem;">
            <span style="font-size: 3rem; display: block; margin-bottom: 0.5rem; animation: pulse 2s infinite;">🌍</span>
            <h2 style="font-family: var(--font-family-display); font-size: 2rem; font-weight: 800; color: var(--color-primary-dark); text-transform: uppercase; margin: 0; letter-spacing: -0.02em;">
                L'aventure t'attend !
            </h2>
            <p style="font-size: 0.9rem; color: var(--color-text-muted); margin: 0.25rem 0 0 0; font-weight: 500;">
                Tu as été invité à explorer une ville béninoise à travers des énigmes.
            </p>
        </div>

        <div class="premium-card" style="border: 1px solid var(--border-color); background: var(--color-surface-light); padding: 1.75rem; margin-bottom: 2rem; box-shadow: var(--shadow-lg);">
            <div style="display: flex; items-start: center; gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                <div style="background: var(--color-bg-light); padding: 0.75rem; border-radius: var(--border-radius-md); font-size: 2rem; display: flex; align-items: center; justify-content: center; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                    📍
                </div>
                <div>
                    <span style="font-size: 0.65rem; color: var(--color-text-muted); font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: block;">Destination</span>
                    <h3 style="font-family: var(--font-family-display); font-size: 1.35rem; font-weight: 800; color: var(--color-primary-dark); margin: 0; text-transform: uppercase;">
                        {{ invitation.city.name }}
                    </h3>
                    <p style="font-size: 0.8rem; color: var(--color-text-muted); margin: 0.25rem 0 0 0; line-height: 1.3; font-weight: 500;">
                        {{ invitation.city.description }}
                    </p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="background: var(--color-bg-light); padding: 0.75rem 1rem; border-radius: var(--border-radius-md); border: 1px solid var(--border-color);">
                    <span style="display: block; font-size: 0.6rem; font-weight: 800; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 0.25rem; letter-spacing: 0.05em;">Mode de Jeu</span>
                    <span style="color: var(--color-primary-dark); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; font-family: var(--font-family-display);">
                        👥 {{ invitation.mode }}
                    </span>
                </div>
                
                <div style="background: var(--color-bg-light); padding: 0.75rem 1rem; border-radius: var(--border-radius-md); border: 1px solid var(--border-color);">
                    <span style="display: block; font-size: 0.6rem; font-weight: 800; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 0.25rem; letter-spacing: 0.05em;">Difficulté</span>
                    <span :style="getDifficultyColor(invitation.difficulty)" style="font-weight: 800; font-size: 0.85rem; text-transform: uppercase; font-family: var(--font-family-display);">
                        {{ getDifficultyLabel(invitation.difficulty) }}
                    </span>
                </div>

                <div style="background: var(--color-bg-light); padding: 0.75rem 1rem; border-radius: var(--border-radius-md); border: 1px solid var(--border-color);">
                    <span style="display: block; font-size: 0.6rem; font-weight: 800; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 0.25rem; letter-spacing: 0.05em;">Moyen de transport</span>
                    <span style="color: var(--color-primary-dark); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; font-family: var(--font-family-display);">
                        {{ getLocomotionIcon(invitation.locomotion) }} {{ invitation.locomotion }}
                    </span>
                </div>

                <div style="background: var(--color-bg-light); padding: 0.75rem 1rem; border-radius: var(--border-radius-md); border: 1px solid var(--border-color);">
                    <span style="display: block; font-size: 0.6rem; font-weight: 800; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: 0.25rem; letter-spacing: 0.05em;">Temps alloué</span>
                    <span style="color: var(--color-primary-dark); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; font-family: var(--font-family-display);">
                        ⏱️ {{ invitation.duration_minutes }} min
                    </span>
                </div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem; text-align: center;">
            <button
                @click="submit"
                class="premium-btn premium-btn-primary"
                style="width: 100%; py: 1rem; font-size: 1.1rem; font-family: var(--font-family-display); font-weight: 800; text-transform: uppercase; border-radius: var(--border-radius-md); box-shadow: var(--shadow-glow);"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Chargement de l\'équipe...' : '🔥 REJOINDRE L\'ÉQUIPE' }}
            </button>

            <Link
                href="/"
                style="color: var(--color-text-muted); font-weight: 700; text-decoration: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; transition: color var(--transition-fast);"
                class="hover:text-primary"
            >
                Peut-être plus tard
            </Link>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
</style>
