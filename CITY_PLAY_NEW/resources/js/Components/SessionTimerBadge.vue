<script setup>
import { useSessionTimer } from '@/composables/useSessionTimer';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const session = computed(() => page.props.session);

const showTimer = computed(() =>
    session.value?.timer?.started_at
    && ['active', 'paused'].includes(session.value?.status)
);

const { formatted, isPaused, isUrgent, isCritical, isExpired, progressPercent } = useSessionTimer(
    () => session.value,
    { autoExpire: true }
);
</script>

<template>
    <div v-if="showTimer"
         class="flex items-center gap-2 px-3 py-1.5 rounded-full font-mono text-xs font-black tabular-nums transition-all"
         :class="[
             isExpired ? 'bg-red-500/15 border border-red-500/30 text-red-500' :
             isCritical ? 'bg-red-500/10 border border-red-500/25 text-red-400 animate-pulse' :
             isUrgent ? 'bg-amber-500/10 border border-amber-500/25 text-amber-500' :
             'bg-[#d65a31]/10 border border-[#d65a31]/25 text-[#d65a31]'
         ]">
        <svg v-if="isPaused" class="w-3.5 h-3.5 opacity-80" fill="currentColor" viewBox="0 0 24 24">
            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
        </svg>
        <svg v-else class="w-3.5 h-3.5 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>{{ isExpired ? '00:00' : formatted }}</span>
        <span v-if="isPaused" class="text-[9px] uppercase tracking-widest opacity-70">pause</span>
    </div>
</template>
