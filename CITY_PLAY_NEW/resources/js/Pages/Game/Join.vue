<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: Object
});

const form = useForm({});

const submit = () => {
    form.post(route('game.session.join', props.invitation.token));
};

const globeIcon = `
<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="32" cy="32" r="30" fill="url(#globeGradient)" stroke="currentColor" stroke-width="2" />
  <path d="M10 32H54" stroke="white" stroke-opacity="0.8" stroke-width="2" />
  <path d="M32 10C26 18 26 46 32 54" stroke="white" stroke-opacity="0.8" stroke-width="2" />
  <path d="M18 16C24 28 40 28 46 16" stroke="white" stroke-opacity="0.7" stroke-width="2" />
  <path d="M18 48C24 36 40 36 46 48" stroke="white" stroke-opacity="0.7" stroke-width="2" />
  <defs>
    <linearGradient id="globeGradient" x1="8" y1="8" x2="56" y2="56" gradientUnits="userSpaceOnUse">
      <stop stop-color="#E26D37" />
      <stop offset="1" stop-color="#C85C32" />
    </linearGradient>
  </defs>
</svg>
`;

const mapMarkerIcon = `
<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M12 2C8.134 2 5 5.134 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.866-3.134-7-7-7Z" fill="currentColor" />
  <circle cx="12" cy="9" r="2.5" fill="white" />
</svg>
`;

const locomotionIcons = {
    marche: `
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 2a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm1.65 4.53c-.68.16-1.1.7-1.36 1.35l-.4 1.1c-.24.65-.9 1.08-1.57.98-.74-.12-1.3-.76-1.27-1.5v-.02c.04-.84.75-1.5 1.6-1.5h2a1 1 0 0 1 .9 1.57Zm4.3 13.2l-1.67-1.7c-.27-.27-.7-.3-1.02-.08L11 18.6l-1.12-2.23a1.02 1.02 0 0 1 .34-1.32l2.35-1.48a.75.75 0 0 1 1.1.24l.45.85 1.88-.7c.7-.26 1.46.1 1.72.8l1.12 3.03c.26.7-.1 1.47-.8 1.73-.24.09-.5.1-.74.01Z" fill="currentColor" />
      </svg>
    `,
    velo: `
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="6.5" cy="16.5" r="3.5" stroke="currentColor" stroke-width="2" />
        <circle cx="17.5" cy="16.5" r="3.5" stroke="currentColor" stroke-width="2" />
        <path d="M6.5 16.5h4l2-4h3" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        <path d="M14.5 7.5l-2 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        <path d="M17.5 7.5h-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      </svg>
    `,
    moto: `
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="7" cy="17" r="3" stroke="currentColor" stroke-width="2" />
        <circle cx="17" cy="17" r="3" stroke="currentColor" stroke-width="2" />
        <path d="M3 17h4l4-7h4l3 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        <path d="M12 10v-2h2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      </svg>
    `,
    voiture: `
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M4 15V11c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        <path d="M6 15v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        <path d="M18 15v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        <path d="M7 11l1.5-4h7L17 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
      </svg>
    `
};

const getLocomotionIcon = (type) => locomotionIcons[type] || locomotionIcons.marche;

const difficultyMap = {
    facile: {
        label: 'Facile',
        icon: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l2.39 4.84L20 8.18l-3.09 3.01.73 4.25L12 14.77 6.36 15.44l.73-4.25L4 8.18l5.61-.95L12 2Z" fill="currentColor"/></svg>`,
        class: 'difficulty-facile'
    },
    moyen: {
        label: 'Moyen',
        icon: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2a7 7 0 1 0 0 14 7 7 0 0 0 0-14Zm0 2a5 5 0 1 1 0 10 5 5 0 0 1 0-10Z" fill="currentColor"/><path d="M12 7v5l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>`,
        class: 'difficulty-moyen'
    },
    difficile: {
        label: 'Difficile',
        icon: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3l2.18 4.43 4.88.71-3.53 3.44.83 4.84L12 15.77 7.64 16.42l.83-4.84L4.94 8.14l4.88-.71L12 3Z" fill="currentColor"/></svg>`,
        class: 'difficulty-difficile'
    }
};

const getDifficulty = (level) => difficultyMap[level] || difficultyMap.moyen;
</script>

<template>
    <GuestLayout>
        <Head title="CityPlay - Rejoindre l'aventure" />

        <section class="join-hero">
            <div class="join-badge" aria-hidden="true" v-html="globeIcon"></div>
            <h2 class="join-title">L'aventure t'attend !</h2>
            <p class="join-subtitle">Tu as été invité à explorer une ville béninoise à travers des énigmes, défis et secrets locaux.</p>
        </section>

        <article class="premium-card join-card">
            <div class="join-card-header">
                <div class="join-card-icon" aria-hidden="true" v-html="mapMarkerIcon"></div>
                <div>
                    <span class="join-overline">Destination</span>
                    <h3 class="join-city-name">{{ invitation.city.name }}</h3>
                    <p class="join-city-description">{{ invitation.city.description }}</p>
                </div>
            </div>

            <div class="join-grid">
                <section class="join-meta-card">
                    <span class="join-meta-label">Mode de jeu</span>
                    <div class="join-meta-value">
                        <span class="join-data-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 11a3 3 0 1 0-6 0 3 3 0 0 0 6 0Zm-4.5 8c-3.038 0-5.5-1.462-5.5-3.25 0-1.152.85-2.165 2.22-2.7C8.5 13.35 10.15 13 12 13s3.5.35 4.78.8c1.37.54 2.22 1.55 2.22 2.7 0 1.788-2.462 3.25-5.5 3.25Z" fill="currentColor"/></svg>
                        </span>
                        {{ invitation.mode }}
                    </div>
                </section>

                <section class="join-meta-card">
                    <span class="join-meta-label">Difficulté</span>
                    <div class="join-meta-value" :class="getDifficulty(invitation.difficulty).class">
                        <span class="join-data-icon" aria-hidden="true" v-html="getDifficulty(invitation.difficulty).icon"></span>
                        {{ getDifficulty(invitation.difficulty).label }}
                    </div>
                </section>

                <section class="join-meta-card">
                    <span class="join-meta-label">Moyen de transport</span>
                    <div class="join-meta-value">
                        <span class="join-data-icon" aria-hidden="true" v-html="getLocomotionIcon(invitation.locomotion)"></span>
                        {{ invitation.locomotion }}
                    </div>
                </section>

                <section class="join-meta-card">
                    <span class="join-meta-label">Temps alloué</span>
                    <div class="join-meta-value">
                        <span class="join-data-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"/><path d="M12 6v6l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        {{ invitation.duration_minutes }} min
                    </div>
                </section>
            </div>
        </article>

        <div class="join-actions">
            <button
                @click="submit"
                class="premium-btn premium-btn-primary join-submit"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Chargement de l\'équipe...' : 'Rejoindre l\'équipe' }}
            </button>

            <Link href="/" class="join-link">Peut-être plus tard</Link>
        </div>
    </GuestLayout>
</template>

<style scoped>
.join-hero {
    text-align: center;
    margin: 1.5rem auto 1.5rem;
    max-width: 720px;
    padding: 0 1rem;
}

.join-badge {
    width: 72px;
    height: 72px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    border-radius: 50%;
    background: radial-gradient(circle at top, rgba(226,109,55,0.22), rgba(200,92,50,0.1));
    border: 1px solid rgba(200,92,50,0.16);
    box-shadow: 0 18px 42px -18px rgba(200,92,50,0.55);
}

.join-badge svg {
    width: 36px;
    height: 36px;
    color: white;
}

.join-title {
    font-family: var(--font-family-display);
    font-size: 2rem;
    font-weight: 800;
    color: var(--color-primary-dark);
    text-transform: uppercase;
    margin: 0;
    letter-spacing: -0.04em;
}

.join-subtitle {
    max-width: 560px;
    margin: 0.9rem auto 0;
    color: var(--color-text-muted);
    font-size: 0.95rem;
    line-height: 1.75;
    font-weight: 500;
}

.join-card {
    width: 100%;
    margin: 0 auto 1.5rem;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-xl);
    padding: 1.6rem;
    background: var(--color-surface-light);
}

.join-card-header {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--border-color);
}

.join-card-icon {
    width: 56px;
    min-width: 56px;
    height: 56px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    background: rgba(200,92,50,0.14);
    color: var(--color-primary-dark);
    box-shadow: var(--shadow-sm);
}

.join-card-icon svg {
    width: 26px;
    height: 26px;
}

.join-overline {
    display: block;
    margin-bottom: 0.45rem;
    font-size: 0.7rem;
    font-weight: 800;
    color: var(--color-text-muted);
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.join-city-name {
    font-family: var(--font-family-display);
    font-size: 1.55rem;
    font-weight: 800;
    color: var(--color-primary-dark);
    margin: 0;
    text-transform: uppercase;
}

.join-city-description {
    margin: 0.75rem 0 0;
    color: var(--color-text-muted);
    line-height: 1.75;
    font-size: 0.95rem;
    font-weight: 500;
}

.join-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.join-meta-card {
    padding: 1.15rem 1.25rem;
    border-radius: var(--border-radius-md);
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0.75rem;
}

.join-meta-label {
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--color-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.14em;
}

.join-meta-value {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    font-weight: 800;
    color: var(--color-primary-dark);
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.join-data-icon {
    width: 1.3rem;
    height: 1.3rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary);
}

.join-data-icon svg {
    width: 100%;
    height: 100%;
}

.difficulty-facile {
    color: var(--color-success);
}

.difficulty-moyen {
    color: var(--color-primary-light);
}

.difficulty-difficile {
    color: var(--color-primary-dark);
}

.join-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.95rem;
    padding: 0 1rem 2rem;
}

.join-submit {
    width: 100%;
    max-width: 420px;
    padding: 1rem 1.25rem;
    font-size: 1rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.join-link {
    color: var(--color-text-muted);
    font-weight: 700;
    text-decoration: none;
    font-size: 0.88rem;
    letter-spacing: 0.12em;
    transition: color var(--transition-fast);
}

.join-link:hover {
    color: var(--color-primary-dark);
}

@media (min-width: 640px) {
    .join-card-header {
        flex-direction: row;
        align-items: center;
    }

    .join-card {
        padding: 1.9rem;
    }

    .join-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .join-card {
        padding: 2rem 2.2rem;
    }

    .join-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .join-hero {
        margin-top: 2.25rem;
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}
</style>
