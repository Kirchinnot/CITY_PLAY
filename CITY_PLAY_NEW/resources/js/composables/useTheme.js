import { ref, watch, onMounted } from 'vue';

const STORAGE_KEY = 'cityplay_theme';

// État global partagé entre tous les composants
const isDark = ref(true);

const applyTheme = (dark) => {
    const html = document.documentElement;
    if (dark) {
        html.classList.add('theme-dark');
        html.classList.remove('theme-light');
    } else {
        html.classList.add('theme-light');
        html.classList.remove('theme-dark');
    }
};

const initTheme = () => {
    const saved = localStorage.getItem(STORAGE_KEY);
    // Par défaut dark si rien n'est enregistré
    isDark.value = saved !== null ? saved === 'dark' : true;
    applyTheme(isDark.value);
};

const toggleTheme = () => {
    isDark.value = !isDark.value;
    localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light');
    applyTheme(isDark.value);
};

export function useTheme() {
    onMounted(() => { initTheme(); });
    watch(isDark, (val) => { applyTheme(val); });
    return { isDark, toggleTheme, initTheme };
}
