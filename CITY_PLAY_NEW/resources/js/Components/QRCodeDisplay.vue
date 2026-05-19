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
    <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 1rem 0; width: 100%;">
        <div style="padding: 0.75rem; background: white; border-radius: var(--border-radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: inline-block;">
            <QrcodeVue 
                :value="url" 
                :size="size" 
                level="M"
                :margin="2"
                style="border-radius: var(--border-radius-sm);"
            />
        </div>
        
        <div style="width: 100%; max-width: 320px;">
            <p style="font-size: 0.65rem; color: var(--color-text-muted); font-weight: 800; text-transform: uppercase; tracking-widest: 0.05em; text-align: center; margin-bottom: 0.5rem;">
                Lien d'invitation direct
            </p>
            <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background: var(--color-bg-light); border: 1px dashed var(--border-color); border-radius: var(--border-radius-sm);">
                <p style="font-size: 0.75rem; font-family: monospace; color: var(--color-text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; margin: 0;">
                    {{ url }}
                </p>
                <button 
                    @click="copyLink"
                    class="premium-btn"
                    style="flex-shrink: 0; padding: 0.4rem 0.8rem; font-size: 0.7rem; font-family: var(--font-family-display); font-weight: 800; border-radius: var(--border-radius-sm); background: var(--color-primary); color: white;"
                >
                    {{ copied ? '✓ Copié !' : 'Copier' }}
                </button>
            </div>
        </div>
    </div>
</template>
