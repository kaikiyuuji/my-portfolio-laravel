import { onMounted, onUnmounted, ref } from 'vue';

const STORAGE_KEY = 'admin-arcade-sound';

export function useArcadeSound(rootRef) {
    const soundEnabled = ref(true);
    let audioContext = null;
    let lastHoverTarget = null;
    let lastTypeSoundAt = 0;
    let typeStep = 0;

    const getAudioContext = () => {
        if (typeof window === 'undefined') return null;

        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return null;

        if (!audioContext) {
            audioContext = new AudioContext();
        }

        if (audioContext.state === 'suspended') {
            audioContext.resume();
        }

        return audioContext;
    };

    const tone = (frequency, duration = 0.06, type = 'square', volume = 0.025, delay = 0) => {
        if (!soundEnabled.value) return;

        const context = getAudioContext();
        if (!context) return;

        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const start = context.currentTime + delay;
        const end = start + duration;

        oscillator.type = type;
        oscillator.frequency.setValueAtTime(frequency, start);
        gain.gain.setValueAtTime(volume, start);
        gain.gain.exponentialRampToValueAtTime(0.0001, end);

        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.start(start);
        oscillator.stop(end);
    };

    const playSelect = () => {
        tone(330, 0.045, 'square', 0.02);
        tone(520, 0.055, 'square', 0.018, 0.035);
    };

    const playConfirm = () => {
        tone(440, 0.05, 'square', 0.025);
        tone(660, 0.06, 'square', 0.022, 0.045);
        tone(880, 0.08, 'square', 0.018, 0.095);
    };

    const playDanger = () => {
        tone(220, 0.07, 'sawtooth', 0.025);
        tone(130, 0.09, 'square', 0.022, 0.055);
    };

    const playHover = () => {
        tone(760, 0.025, 'square', 0.008);
    };

    const playType = (key) => {
        const now = performance.now();
        if (now - lastTypeSoundAt < 28) return;

        lastTypeSoundAt = now;

        if (key === 'Backspace' || key === 'Delete') {
            tone(155, 0.035, 'square', 0.011);
            return;
        }

        if (key === 'Enter') {
            tone(280, 0.04, 'square', 0.012);
            tone(420, 0.045, 'square', 0.01, 0.028);
            return;
        }

        if (key === ' ') {
            tone(190, 0.025, 'triangle', 0.009);
            return;
        }

        const notes = [440, 494, 523, 587, 659];
        tone(notes[typeStep % notes.length], 0.022, 'square', 0.008);
        typeStep += 1;
    };

    const interactiveElement = (target) =>
        target?.closest?.('a, button, input, select, textarea, [role="button"]');

    const handleClick = (event) => {
        const target = interactiveElement(event.target);
        if (!target || target.disabled) return;

        if (
            target.matches('[data-sound-toggle]') ||
            target.matches('[type="submit"]') ||
            target.classList.contains('bg-emerald-600')
        ) {
            playConfirm();
            return;
        }

        if (
            target.classList.contains('text-red-600') ||
            target.classList.contains('bg-red-600') ||
            target.classList.contains('text-red-700')
        ) {
            playDanger();
            return;
        }

        playSelect();
    };

    const handlePointerOver = (event) => {
        if (event.pointerType === 'touch') return;

        const target = interactiveElement(event.target);
        if (!target || target === lastHoverTarget || target.disabled) return;

        lastHoverTarget = target;
        playHover();
    };

    const handlePointerOut = (event) => {
        const target = interactiveElement(event.target);
        if (target === lastHoverTarget && !target.contains(event.relatedTarget)) {
            lastHoverTarget = null;
        }
    };

    const handleKeyDown = (event) => {
        const target = event.target;
        const isTypingField = target?.matches?.(
            'input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]):not([type="file"]), textarea, [contenteditable="true"]',
        );

        if (
            !isTypingField ||
            event.ctrlKey ||
            event.metaKey ||
            event.altKey ||
            event.key === 'Tab' ||
            event.key.startsWith('Arrow') ||
            event.key === 'Shift' ||
            event.key === 'CapsLock' ||
            event.key === 'Escape'
        ) {
            return;
        }

        playType(event.key);
    };

    const toggleSound = () => {
        soundEnabled.value = !soundEnabled.value;
        localStorage.setItem(STORAGE_KEY, String(soundEnabled.value));

        if (soundEnabled.value) {
            playConfirm();
        }
    };

    onMounted(() => {
        const storedPreference = localStorage.getItem(STORAGE_KEY);
        soundEnabled.value = storedPreference === null ? true : storedPreference === 'true';

        const root = rootRef.value;
        root?.addEventListener('click', handleClick);
        root?.addEventListener('pointerover', handlePointerOver);
        root?.addEventListener('pointerout', handlePointerOut);
        root?.addEventListener('keydown', handleKeyDown);
    });

    onUnmounted(() => {
        const root = rootRef.value;
        root?.removeEventListener('click', handleClick);
        root?.removeEventListener('pointerover', handlePointerOver);
        root?.removeEventListener('pointerout', handlePointerOut);
        root?.removeEventListener('keydown', handleKeyDown);
        audioContext?.close();
    });

    return {
        soundEnabled,
        toggleSound,
    };
}
