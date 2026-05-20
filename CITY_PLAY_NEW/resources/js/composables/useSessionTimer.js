import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

/**
 * Compte à rebours de session (respecte les pauses via données serveur).
 *
 * @param {import('vue').Ref|Function} sessionSource - ref ou getter vers session (+ timer)
 * @param {Object} options
 */
export function useSessionTimer(sessionSource, options = {}) {
    const { onExpired, autoExpire = true } = options;

    const remainingSeconds = ref(0);
    const isExpired = ref(false);
    let intervalId = null;
    let expireRequested = false;

    const getSession = () => {
        const src = typeof sessionSource === 'function' ? sessionSource() : sessionSource?.value;
        return src ?? null;
    };

    const computeRemaining = () => {
        const session = getSession();
        const timer = session?.timer;

        if (!timer?.started_at || !timer.available_seconds) {
            remainingSeconds.value = timer?.available_seconds ?? 0;
            isExpired.value = !!timer?.is_expired;
            return;
        }

        if (timer.is_expired) {
            remainingSeconds.value = 0;
            isExpired.value = true;
            return;
        }

        const started = new Date(timer.started_at).getTime();
        const pauseTotal = (timer.total_pause_seconds || 0) * 1000;

        let endMs = Date.now();
        if (timer.status === 'paused' && timer.paused_at) {
            endMs = new Date(timer.paused_at).getTime();
        }

        const elapsedSec = Math.floor((endMs - started) / 1000) - (timer.total_pause_seconds || 0);
        const remaining = Math.max(0, timer.available_seconds - elapsedSec);

        remainingSeconds.value = remaining;
        isExpired.value = remaining <= 0;
    };

    const formatted = computed(() => {
        const total = Math.max(0, remainingSeconds.value);
        const h = Math.floor(total / 3600);
        const m = Math.floor((total % 3600) / 60);
        const s = total % 60;

        if (h > 0) {
            return `${h}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
        return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    });

    const isPaused = computed(() => getSession()?.status === 'paused' || getSession()?.timer?.status === 'paused');
    const isUrgent = computed(() => remainingSeconds.value > 0 && remainingSeconds.value <= 300);
    const isCritical = computed(() => remainingSeconds.value > 0 && remainingSeconds.value <= 60);
    const progressPercent = computed(() => {
        const session = getSession();
        const total = session?.timer?.available_seconds ?? 0;
        if (!total) return 100;
        return Math.round((remainingSeconds.value / total) * 100);
    });

    const requestExpire = async () => {
        if (expireRequested) return;
        const session = getSession();
        if (!session?.id) return;

        expireRequested = true;
        try {
            await axios.post(route('player.game-sessions.expire-time', session.id));
        } catch (e) {
            console.warn('Expire session:', e);
        }

        if (onExpired) {
            onExpired(session);
        } else {
            router.visit(route('player.game-sessions.summary', session.id));
        }
    };

    const tick = () => {
        computeRemaining();
        if (isExpired.value && autoExpire) {
            requestExpire();
        }
    };

    onMounted(() => {
        tick();
        intervalId = setInterval(tick, 1000);
    });

    onUnmounted(() => {
        if (intervalId) clearInterval(intervalId);
    });

    watch(
        () => getSession(),
        () => {
            expireRequested = false;
            tick();
        },
        { deep: true }
    );

    return {
        remainingSeconds,
        formatted,
        isExpired,
        isPaused,
        isUrgent,
        isCritical,
        progressPercent,
        refresh: tick,
    };
}
