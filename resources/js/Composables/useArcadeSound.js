import { onMounted, onUnmounted, ref } from 'vue';

export function useArcadeSound(rootRef, options = {}) {
    const storageKey = options.storageKey ?? 'admin-arcade-sound';
    const soundEnabled = ref(true);
    let audioContext = null;
    let lastHoverTarget = null;
    let lastTypeSoundAt = 0;
    let lastPressSoundAt = 0;
    let lastPressTarget = null;
    let typeStep = 0;
    const fallbackAudios = new Set();
    const isTouchDevice =
        typeof window !== 'undefined' &&
        (navigator.maxTouchPoints > 0 || window.matchMedia?.('(pointer: coarse)').matches);
    const mobileVolumeMultiplier = isTouchDevice ? 1.6 : 1;

    const getAudioContext = () => {
        if (typeof window === 'undefined') return null;

        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return null;

        if (!audioContext) {
            try {
                audioContext = new AudioContext({
                    latencyHint: 'interactive',
                });
            } catch {
                audioContext = new AudioContext();
            }
        }

        return audioContext;
    };

    const unlockAudio = async () => {
        const context = getAudioContext();
        if (!context) return null;

        if (context.state !== 'running') {
            try {
                await context.resume();
            } catch {
                return null;
            }
        }

        return context;
    };

    const playWebTone = async (frequency, duration, type, volume, delay) => {
        const context = await unlockAudio();
        if (!context) return;

        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const start = context.currentTime + delay;
        const end = start + duration;

        oscillator.type = type;
        oscillator.frequency.setValueAtTime(frequency, start);
        gain.gain.setValueAtTime(volume * mobileVolumeMultiplier, start);
        gain.gain.exponentialRampToValueAtTime(0.0001, end);

        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.start(start);
        oscillator.stop(end);
    };

    const writeAscii = (view, offset, value) => {
        for (let index = 0; index < value.length; index += 1) {
            view.setUint8(offset + index, value.charCodeAt(index));
        }
    };

    const createToneWav = (frequency, duration, type, volume, delay) => {
        const sampleRate = 22050;
        const delaySamples = Math.floor(sampleRate * delay);
        const toneSamples = Math.max(1, Math.floor(sampleRate * duration));
        const totalSamples = delaySamples + toneSamples;
        const buffer = new ArrayBuffer(44 + totalSamples * 2);
        const view = new DataView(buffer);

        writeAscii(view, 0, 'RIFF');
        view.setUint32(4, 36 + totalSamples * 2, true);
        writeAscii(view, 8, 'WAVE');
        writeAscii(view, 12, 'fmt ');
        view.setUint32(16, 16, true);
        view.setUint16(20, 1, true);
        view.setUint16(22, 1, true);
        view.setUint32(24, sampleRate, true);
        view.setUint32(28, sampleRate * 2, true);
        view.setUint16(32, 2, true);
        view.setUint16(34, 16, true);
        writeAscii(view, 36, 'data');
        view.setUint32(40, totalSamples * 2, true);

        const amplitude = Math.min(0.32, volume * mobileVolumeMultiplier * 8);

        for (let index = 0; index < totalSamples; index += 1) {
            let sample = 0;

            if (index >= delaySamples) {
                const toneIndex = index - delaySamples;
                const phase = (toneIndex * frequency) / sampleRate;
                const progress = toneIndex / toneSamples;
                const envelope = Math.pow(1 - progress, 1.8);

                if (type === 'sawtooth') {
                    sample = 2 * (phase - Math.floor(phase + 0.5));
                } else if (type === 'triangle') {
                    sample = 2 * Math.abs(2 * (phase - Math.floor(phase + 0.5))) - 1;
                } else {
                    sample = Math.sin(phase * Math.PI * 2) >= 0 ? 1 : -1;
                }

                sample *= amplitude * envelope;
            }

            view.setInt16(44 + index * 2, Math.round(sample * 32767), true);
        }

        return new Blob([buffer], { type: 'audio/wav' });
    };

    const playFallbackTone = (frequency, duration, type, volume, delay) => {
        const url = URL.createObjectURL(createToneWav(frequency, duration, type, volume, delay));
        const audio = new Audio(url);

        audio.preload = 'auto';
        audio.playsInline = true;
        audio.setAttribute('playsinline', '');
        audio.setAttribute('webkit-playsinline', '');
        fallbackAudios.add(audio);

        const cleanup = () => {
            fallbackAudios.delete(audio);
            URL.revokeObjectURL(url);
        };

        audio.addEventListener('ended', cleanup, { once: true });
        audio.addEventListener('error', cleanup, { once: true });

        const playback = audio.play();
        playback?.catch(() => {
            cleanup();
            playWebTone(frequency, duration, type, volume, delay);
        });
    };

    const primeMobileAudio = () => {
        if (!isTouchDevice) return;

        try {
            if ('audioSession' in navigator) {
                navigator.audioSession.type = 'playback';
            }
        } catch {
            // Experimental API; safely ignored where unavailable.
        }

        const context = getAudioContext();
        if (!context) return;

        try {
            const buffer = context.createBuffer(1, 1, context.sampleRate);
            const source = context.createBufferSource();
            source.buffer = buffer;
            source.connect(context.destination);
            source.start(0);
            context.resume();
        } catch {
            // The WAV fallback below still has a chance to play.
        }
    };

    const tone = (frequency, duration = 0.06, type = 'square', volume = 0.025, delay = 0) => {
        if (!soundEnabled.value) return;

        if (isTouchDevice) {
            playFallbackTone(frequency, duration, type, volume, delay);
            return;
        }

        playWebTone(frequency, duration, type, volume, delay);
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

    const playForTarget = (target) => {
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

    const handlePress = (event) => {
        const target = interactiveElement(event.target);
        if (!target || target.disabled) return;

        const now = performance.now();
        if (target === lastPressTarget && now - lastPressSoundAt < 250) return;

        lastPressTarget = target;
        lastPressSoundAt = now;

        // pointerdown/touchstart happens before click/navigation, which gives
        // mobile Safari enough time to unlock and start its AudioContext.
        primeMobileAudio();
        unlockAudio();
        playForTarget(target);
    };

    const handleClick = (event) => {
        const target = interactiveElement(event.target);
        if (!target || target.disabled) return;

        if (target === lastPressTarget && performance.now() - lastPressSoundAt < 650) {
            return;
        }

        playForTarget(target);
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
        localStorage.setItem(storageKey, String(soundEnabled.value));

        if (soundEnabled.value) {
            playConfirm();
        }
    };

    onMounted(() => {
        const storedPreference = localStorage.getItem(storageKey);
        soundEnabled.value = storedPreference === null ? true : storedPreference === 'true';

        const root = rootRef.value;
        if ('PointerEvent' in window) {
            root?.addEventListener('pointerdown', handlePress, true);
        } else {
            root?.addEventListener('touchstart', handlePress, { passive: true, capture: true });
        }
        root?.addEventListener('click', handleClick);
        root?.addEventListener('pointerover', handlePointerOver);
        root?.addEventListener('pointerout', handlePointerOut);
        root?.addEventListener('keydown', handleKeyDown);
    });

    onUnmounted(() => {
        const root = rootRef.value;
        if ('PointerEvent' in window) {
            root?.removeEventListener('pointerdown', handlePress, true);
        } else {
            root?.removeEventListener('touchstart', handlePress, true);
        }
        root?.removeEventListener('click', handleClick);
        root?.removeEventListener('pointerover', handlePointerOver);
        root?.removeEventListener('pointerout', handlePointerOut);
        root?.removeEventListener('keydown', handleKeyDown);
        fallbackAudios.forEach((audio) => {
            audio.pause();
            audio.removeAttribute('src');
        });
        fallbackAudios.clear();
        audioContext?.close();
    });

    return {
        soundEnabled,
        toggleSound,
    };
}
