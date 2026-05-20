<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { usePrimeDialogsState, acceptAlert, closeAlert, acceptConfirm, rejectConfirm } from '@/composables/usePrimeDialogs';

const dialogState = usePrimeDialogsState();
</script>

<template>
    <Dialog
        v-model:visible="dialogState.alert.visible"
        :header="dialogState.alert.header"
        :modal="true"
        :closeOnEscape="true"
        :dismissableMask="true"
        class="cp-dialog p-dialog-shadow"
        content-class="bg-[#0d1117] text-white border border-[#d65a31]/20"
        header-class="bg-[#d65a31] text-white border-b border-[#ffffff]/10"
        :closable="false"
    >
        <div class="flex items-start gap-4">
            <i :class="dialogState.alert.icon + ' text-3xl text-[#FFB700]'" aria-hidden="true" />
            <div class="space-y-3">
                <p class="text-sm leading-relaxed text-white/90">{{ dialogState.alert.message }}</p>
            </div>
        </div>
        <template #footer>
            <div class="flex justify-end gap-3 pt-4">
                <Button
                    :label="dialogState.alert.acceptLabel"
                    class="p-button-sm p-button-rounded bg-[#d65a31] border-[#d65a31] hover:bg-[#b84a24]"
                    @click="acceptAlert"
                />
            </div>
        </template>
    </Dialog>

    <Dialog
        v-model:visible="dialogState.confirm.visible"
        :header="dialogState.confirm.header"
        :modal="true"
        :closeOnEscape="true"
        :dismissableMask="true"
        class="cp-dialog p-dialog-shadow"
        content-class="bg-[#0d1117] text-white border border-[#d65a31]/20"
        header-class="bg-[#d65a31] text-white border-b border-[#ffffff]/10"
        @hide="rejectConfirm"
    >
        <div class="flex items-start gap-4">
            <i :class="dialogState.confirm.icon + ' text-3xl text-[#FFB700]'" aria-hidden="true" />
            <div class="space-y-3">
                <p class="text-sm leading-relaxed text-white/90">{{ dialogState.confirm.message }}</p>
            </div>
        </div>
        <template #footer>
            <div class="flex justify-end gap-3 pt-4">
                <Button
                    :label="dialogState.confirm.rejectLabel"
                    class="p-button-sm p-button-text text-white/80 hover:text-white"
                    @click="rejectConfirm"
                />
                <Button
                    :label="dialogState.confirm.acceptLabel"
                    class="p-button-sm p-button-rounded bg-[#FFB700] border-[#FFB700] text-[#2D1B16] hover:bg-[#e5a000]"
                    @click="acceptConfirm"
                />
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
.cp-dialog :global(.p-dialog) {
    min-width: 18rem;
}
.cp-dialog :global(.p-dialog-mask) {
    background: rgba(13, 17, 23, 0.6) !important;
}
.cp-dialog :global(.p-dialog-content) {
    background: #0d1117 !important;
}
.cp-dialog :global(.p-dialog-header) {
    border:none !important;
}
.cp-dialog :global(.p-dialog-footer) {
    border-top: 1px solid rgba(255,255,255,0.08) !important;
}
</style>
