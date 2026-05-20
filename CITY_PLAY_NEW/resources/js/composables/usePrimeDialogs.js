import { reactive } from 'vue';

const state = reactive({
    alert: {
        visible: false,
        header: 'Information',
        message: '',
        icon: 'pi pi-info-circle',
        acceptLabel: 'OK',
        severity: 'info',
    },
    confirm: {
        visible: false,
        header: 'Confirmation',
        message: '',
        icon: 'pi pi-question-circle',
        acceptLabel: 'Oui',
        rejectLabel: 'Annuler',
    },
});

let alertResolver;
let confirmResolver;

const resetAlert = () => {
    state.alert.visible = false;
    state.alert.header = 'Information';
    state.alert.message = '';
    state.alert.icon = 'pi pi-info-circle';
    state.alert.acceptLabel = 'OK';
    state.alert.severity = 'info';
    alertResolver = null;
};

const resetConfirm = () => {
    state.confirm.visible = false;
    state.confirm.header = 'Confirmation';
    state.confirm.message = '';
    state.confirm.icon = 'pi pi-question-circle';
    state.confirm.acceptLabel = 'Oui';
    state.confirm.rejectLabel = 'Annuler';
    confirmResolver = null;
};

export const usePrimeDialogsState = () => state;

export function alertModal(payload) {
    const options = typeof payload === 'string' ? { message: payload } : payload || {};

    return new Promise((resolve) => {
        state.alert.visible = true;
        state.alert.header = options.header || 'Information';
        state.alert.message = options.message || '';
        state.alert.icon = options.icon || 'pi pi-info-circle';
        state.alert.acceptLabel = options.acceptLabel || 'OK';
        state.alert.severity = options.severity || 'info';
        alertResolver = resolve;
    });
}

export function confirmModal(payload) {
    const options = typeof payload === 'string' ? { message: payload } : payload || {};

    return new Promise((resolve) => {
        state.confirm.visible = true;
        state.confirm.header = options.header || 'Confirmation';
        state.confirm.message = options.message || '';
        state.confirm.icon = options.icon || 'pi pi-question-circle';
        state.confirm.acceptLabel = options.acceptLabel || 'Oui';
        state.confirm.rejectLabel = options.rejectLabel || 'Annuler';
        confirmResolver = resolve;
    });
}

export const acceptAlert = () => {
    if (alertResolver) {
        alertResolver(true);
    }
    resetAlert();
};

export const closeAlert = () => {
    if (alertResolver) {
        alertResolver(false);
    }
    resetAlert();
};

export const acceptConfirm = () => {
    if (confirmResolver) {
        confirmResolver(true);
    }
    resetConfirm();
};

export const rejectConfirm = () => {
    if (confirmResolver) {
        confirmResolver(false);
    }
    resetConfirm();
};

export const closeConfirm = rejectConfirm;
