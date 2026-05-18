<script setup>
import { ref } from 'vue';
import QrcodeVue from 'qrcode.vue';

const props = defineProps({
    url: {
        type: String,
        required: true
    },
    size: {
        type: Number,
        default: 200
    }
});

const copied = ref(false);

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(props.url);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);
    }
};
</script>

<template>
    <div class="flex flex-col items-center gap-4 p-6 bg-white rounded-3xl">
        <div class="p-4 bg-white rounded-2xl shadow-inner">
            <QrcodeVue 
                :value="url" 
                :size="size" 
                level="M"
                :margin="2"
                class="rounded-lg"
            />
        </div>
        
        <div class="w-full max-w-xs">
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest text-center mb-2">
                Lien d'invitation
            </p>
            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                <p class="text-sm text-cityplay-brown font-mono truncate flex-1">
                    {{ url }}
                </p>
                <button 
                    @click="copyLink"
                    class="flex-shrink-0 px-3 py-1 bg-cityplay-orange text-white text-xs font-bold rounded-lg hover:bg-cityplay-yellow transition-colors"
                >
                    {{ copied ? '✓ Copié !' : 'Copier' }}
                </button>
            </div>
        </div>
    </div>
</template>
