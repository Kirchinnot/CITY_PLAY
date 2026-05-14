<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border-slate-700 bg-slate-800 text-slate-200 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 placeholder:text-slate-500"
        v-model="model"
        ref="input"
    />
</template>
