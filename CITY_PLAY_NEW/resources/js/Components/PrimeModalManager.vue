<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { usePrimeDialogsState, acceptAlert, acceptConfirm, rejectConfirm } from '@/composables/usePrimeDialogs';

const dialogState = usePrimeDialogsState();
</script>

<template>
  <Dialog
    v-model:visible="dialogState.alert.visible"
    :modal="true"
    :closable="false"
    :dismissableMask="true"
    class="cp-dialog-mystery p-dialog-shadow"
  >
    <div class="flex flex-col gap-6 items-center sm:flex-row sm:items-start p-6">
      <div class="flex-shrink-0 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-b from-[#FFF0C9] to-[#FFCF7A] shadow-[0_0_25px_rgba(255,207,122,0.5)] border-2 border-[#D2AC67]">
        <i :class="dialogState.alert.icon + ' text-3xl text-[#5C4033]'" aria-hidden="true" />
      </div>
      <div class="space-y-4 text-center sm:text-left">
        <h3 class="text-xl font-extrabold text-[#2D1B16] tracking-tight uppercase [text-shadow:_1px_1px_1px_rgba(255,255,255,0.7)]">{{ dialogState.alert.header }}</h3>
        <p class="text-base leading-relaxed text-[#5C4033] font-medium">{{ dialogState.alert.message }}</p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-center sm:justify-end p-5 bg-[#fbf3e7] border-t border-[#D2AC67]/30 rounded-b-2xl">
        <Button
          :label="dialogState.alert.acceptLabel"
          class="btn-mystery-primary w-full sm:w-auto"
          @click="acceptAlert"
        />
      </div>
    </template>
  </Dialog>

  <Dialog
    v-model:visible="dialogState.confirm.visible"
    :modal="true"
    :closable="true"
    :dismissableMask="true"
    class="cp-dialog-mystery p-dialog-shadow"
    @hide="rejectConfirm"
  >
    <div class="flex flex-col gap-6 items-center sm:flex-row sm:items-start p-6">
      <div class="flex-shrink-0 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-b from-[#FFF0C9] to-[#FFCF7A] shadow-[0_0_25px_rgba(255,207,122,0.5)] border-2 border-[#D2AC67]">
        <i :class="dialogState.confirm.icon + ' text-3xl text-[#5C4033]'" aria-hidden="true" />
      </div>
      <div class="space-y-4 text-center sm:text-left">
        <h3 class="text-xl font-extrabold text-[#2D1B16] tracking-tight uppercase [text-shadow:_1px_1px_1px_rgba(255,255,255,0.7)]">{{ dialogState.confirm.header }}</h3>
        <p class="text-base leading-relaxed text-[#5C4033] font-medium">{{ dialogState.confirm.message }}</p>
      </div>
    </div>

    <template #footer>
      <div class="flex flex-col-reverse gap-3 pt-5 sm:flex-row sm:justify-end sm:gap-4 p-5 bg-[#fbf3e7] border-t border-[#D2AC67]/30 rounded-b-2xl">
        <Button
          :label="dialogState.confirm.rejectLabel"
          class="btn-mystery-secondary w-full sm:w-auto"
          @click="rejectConfirm"
        />
        <Button
          :label="dialogState.confirm.acceptLabel"
          class="btn-mystery-primary w-full sm:w-auto"
          @click="acceptConfirm"
        />
      </div>
    </template>
  </Dialog>
</template>

<style scoped>
/* --- Styles globaux du modal pour PrimeVue --- */

/* Applique les styles de base au panneau de dialogue PrimeVue */
.cp-dialog-mystery :global(.p-dialog) {
  background: #FFF8EE; /* Fond parchemin clair */
  border-radius: 1.5rem !important;
  border: 4px solid #D2AC67 !important; /* Bordure laiton/or vieilli */
  box-shadow: 0 10px 30px rgba(92, 64, 51, 0.4) !important; /* Ombre douce et lourde */
  overflow: hidden;
  position: relative;
  width: min(90vw, 32rem);
  min-width: 19rem;
}

/* Cache le header PrimeVue par défaut, on le reconstruit dans le template */
.cp-dialog-mystery :global(.p-dialog-header) {
  display: none !important;
}

/* Stylise le contenu principal du modal */
.cp-dialog-mystery :global(.p-dialog-content) {
  padding: 0 !important;
  background: #FFF8EE !important;
  position: relative;
  border-radius: 1.25rem 1.25rem 0 0 !important;
}

/* Stylise le footer PrimeVue */
.cp-dialog-mystery :global(.p-dialog-footer) {
  padding: 0 !important;
  background: #FFF8EE !important;
  border-top: none !important;
}

/* --- Masque d'arrière-plan (Overlay) --- */
:global(.p-dialog-mask) {
  background-color: rgba(13, 17, 23, 0.8) !important; /* Fond sombre et opaque */
}

/* --- Boutons personnalisés "Mystère" --- */

/* Bouton principal (Confirmer, Accepter) */
.btn-mystery-primary {
  background: linear-gradient(180deg, #E0531C 0%, #FFB700 100%) !important;
  color: white !important;
  border: 2px solid #5C4033 !important;
  border-radius: 1rem !important;
  font-weight: 800 !important;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  padding: 0.8rem 1.8rem !important;
  font-size: 1rem;
  transition: all 0.2s ease;
  box-shadow: 0 4px 10px rgba(224, 83, 28, 0.4) !important;
}

.btn-mystery-primary:hover {
  background: linear-gradient(180deg, #FFB700 0%, #E0531C 100%) !important;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(224, 83, 28, 0.6) !important;
}

/* Bouton secondaire (Annuler, Refuser) */
.btn-mystery-secondary {
  background: transparent !important;
  color: #5C4033 !important;
  border: 2px solid #D2AC67 !important;
  border-radius: 1rem !important;
  font-weight: 700 !important;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.8rem 1.8rem !important;
  font-size: 1rem;
  transition: all 0.2s ease;
  box-shadow: 0 2px 5px rgba(210, 172, 103, 0.2) !important;
}

.btn-mystery-secondary:hover {
  background: rgba(210, 172, 103, 0.15) !important;
  color: #E0531C !important;
  transform: translateY(-1px);
}
</style>