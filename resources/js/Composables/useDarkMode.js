import { ref, watchEffect } from 'vue';

const STORAGE_KEY = 'portfolio-theme';
const isDark = ref(false);
let initialized = false;

function applyTheme(dark) {
    const root = document.documentElement;
    if (dark) {
        root.classList.add('dark');
    } else {
        root.classList.remove('dark');
    }
}

function initialize() {
    if (initialized || typeof window === 'undefined') return;
    initialized = true;

    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'dark') {
        isDark.value = true;
    } else if (stored === 'light') {
        isDark.value = false;
    } else {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    watchEffect(() => {
        applyTheme(isDark.value);
        if (initialized) {
            localStorage.setItem(STORAGE_KEY, isDark.value ? 'dark' : 'light');
        }
    });
}

export function useDarkMode() {
    initialize();

    function toggle() {
        isDark.value = !isDark.value;
    }

    return { isDark, toggle };
}
