<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    invitation: Object
});

const form = useForm({});

const submit = () => {
    // Cette route sera créée lors de la mise en place du GameSessionController
    // Pour l'instant on prépare l'UI
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

const getDifficultyColor = (level) => {
    const colors = {
        facile: 'text-green-500',
        moyen: 'text-cityplay-orange',
        difficile: 'text-cityplay-red'
    };
    return colors[level] || 'text-cityplay-orange';
};
</script>

<template>
    <GuestLayout>
        <Head title="Rejoindre l'aventure" />

        <div class="mb-8">
            <h2 class="text-3xl font-black text-cityplay-brown uppercase tracking-tight">L'aventure t'attend !</h2>
            <p class="text-gray-500 font-medium">Tu as été invité à explorer une ville.</p>
        </div>

        <div class="bg-cityplay-lime/10 border-2 border-cityplay-lime/30 rounded-3xl p-6 mb-8 shadow-inner">
            <div class="flex items-start gap-4 mb-6">
                <div class="bg-white p-3 rounded-2xl shadow-sm text-3xl">
                    📍
                </div>
                <div>
                    <h3 class="text-xl font-black text-cityplay-brown uppercase">{{ invitation.city.name }}</h3>
                    <p class="text-cityplay-brown/70 font-medium text-sm leading-tight">{{ invitation.city.description }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white/60 p-3 rounded-2xl border border-white">
                    <span class="block text-xs font-bold text-gray-400 uppercase mb-1">Mode</span>
                    <span class="text-cityplay-brown font-black uppercase">{{ invitation.mode }}</span>
                </div>
                <div class="bg-white/60 p-3 rounded-2xl border border-white">
                    <span class="block text-xs font-bold text-gray-400 uppercase mb-1">Difficulté</span>
                    <span :class="['font-black uppercase', getDifficultyColor(invitation.difficulty)]">
                        {{ invitation.difficulty }}
                    </span>
                </div>
                <div class="bg-white/60 p-3 rounded-2xl border border-white">
                    <span class="block text-xs font-bold text-gray-400 uppercase mb-1">Transport</span>
                    <span class="text-cityplay-brown font-black uppercase">
                        {{ getLocomotionIcon(invitation.locomotion) }} {{ invitation.locomotion }}
                    </span>
                </div>
                <div class="bg-white/60 p-3 rounded-2xl border border-white">
                    <span class="block text-xs font-bold text-gray-400 uppercase mb-1">Temps estimé</span>
                    <span class="text-cityplay-brown font-black uppercase">{{ invitation.duration_minutes }} min</span>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <PrimaryButton
                @click="submit"
                class="w-full justify-center py-4 bg-cityplay-orange hover:bg-cityplay-yellow text-white font-black text-lg rounded-2xl shadow-lg transform transition active:scale-95 border-b-4 border-cityplay-brown/20 uppercase"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                REJOINDRE L'ÉQUIPE
            </PrimaryButton>

            <div class="text-center">
                <Link
                    href="/"
                    class="text-gray-400 font-bold hover:text-cityplay-brown transition-colors text-sm uppercase tracking-widest"
                >
                    Peut-être plus tard
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
