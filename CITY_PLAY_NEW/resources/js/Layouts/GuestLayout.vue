<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-slate-950 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <div class="h-20 w-20 bg-gradient-to-tr from-indigo-600 to-cyan-500 rounded-2xl flex items-center justify-center shadow-lg shadow-cyan-500/20">
                    <span class="text-white font-black text-2xl tracking-tighter">CP</span>
                </div>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-slate-900 px-6 py-8 shadow-2xl border border-slate-800 sm:max-w-md sm:rounded-3xl"
        >
            <slot />
        </div>
    </div>
</template>
