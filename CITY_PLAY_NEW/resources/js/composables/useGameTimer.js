import { ref, computed, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { alertModal } from '@/composables/usePrimeDialogs';

/**
 * Chronomètre de session — aligné sur gameState.timer (source serveur).
 */
export function useGameTimer(gameState, options = {}) {
    const {
        syncIntervalMs = 30000,
        onExpired = null,
    } = options;

    const remainingSeconds = ref(0);
    const warningLevel = ref('ok');
    const serverTimer = ref(null);

    let tickInterval = null;
    let syncInterval = null;
    let expiryHandled = false;

    const applyTimer = (timer) => {
        if (!timer) {
            remainingSeconds.value = 0;
            warningLevel.value = 'ok';
            return;
        }

        serverTimer.value = timer;

        if (timer.remaining_seconds != null) {
            remainingSeconds.value = timer.remaining_seconds;
            warningLevel.value = timer.warning_level ?? 'ok';
            return;
        }

        calculateClientSide(timer);
    };

    const calculateClientSide = (timer) => {
        if (!timer?.started_at) {
            remainingSeconds.value = 0;
            return;
        }

        const startedAt = new Date(timer.started_at).getTime();
        const nowMs = timer.paused_at
            ? new Date(timer.paused_at).getTime()
            : Date.now();
        const totalPauseMs = (timer.total_pause_seconds || 0) * 1000;
        const elapsedMs = nowMs - startedAt - totalPauseMs;
        const availableMs = (timer.available_minutes || 0) * 60 * 1000;

        remainingSeconds.value = Math.max(0, Math.floor((availableMs - elapsedMs) / 1000));

        if (remainingSeconds.value <= 0) {
            warningLevel.value = 'expired';
        } else if (remainingSeconds.value <= 300) {
            warningLevel.value = 'critical';
        } else if (remainingSeconds.value <= 900) {
            warningLevel.value = 'warning';
        } else {
            warningLevel.value = 'ok';
        }
    };

    const tick = () => {
        const gs = gameState.value;
        if (!gs?.timer || gs.status === 'paused' || ['completed', 'abandoned'].includes(gs.status)) {
            applyTimer(gs?.timer);
            return;
        }

        if (serverTimer.value?.remaining_seconds != null) {
            const next = Math.max(0, remainingSeconds.value - 1);
            remainingSeconds.value = next;
            if (next <= 0) {
                warningLevel.value = 'expired';
            } else if (next <= 300) {
                warningLevel.value = 'critical';
            } else if (next <= 900) {
                warningLevel.value = 'warning';
            }
        } else {
            calculateClientSide(gs.timer);
        }

        if (remainingSeconds.value <= 0 && !expiryHandled && gs.status === 'active') {
            handleExpiry();
        }
    };

    const syncWithServer = async () => {
        const gs = gameState.value;
        if (!gs?.id || ['completed', 'abandoned', 'pending'].includes(gs.status)) {
            return;
        }

        try {
            const { data } = await axios.post(route('player.game-sessions.sync', gs.id));
            applyTimer(data.timer);

            if (data.redirect) {
                handleExpiry(data.redirect);
                return;
            }

            if (data.status && data.status !== gs.status) {
                router.reload({ preserveScroll: true });
            }
        } catch (e) {
            console.warn('Timer sync failed', e);
        }
    };

    const handleExpiry = async (redirectUrl = null) => {
        if (expiryHandled) return;
        expiryHandled = true;

        if (typeof onExpired === 'function') {
            onExpired(redirectUrl);
            return;
        }

        await alertModal({
            header: 'Temps écoulé',
            message: '⏱️ Temps de jeu écoulé ! Direction le bilan…',
            icon: 'pi pi-clock',
            acceptLabel: 'Voir le bilan',
        });

        const gs = gameState.value;
        if (redirectUrl) {
            router.visit(redirectUrl);
        } else if (gs?.id) {
            router.visit(route('player.game-sessions.summary', gs.id));
        }
    };

    const formatTime = (seconds) => {
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = seconds % 60;
        return h > 0
            ? `${h}h ${m.toString().padStart(2, '0')}m`
            : `${m}:${s.toString().padStart(2, '0')}`;
    };

    const timerColorClass = computed(() => {
        if (warningLevel.value === 'critical' || warningLevel.value === 'expired') {
            return 'text-red-500 animate-pulse';
        }
        if (warningLevel.value === 'warning') {
            return 'text-amber-500';
        }
        return 'text-[#d65a31]';
    });

    const timeAlertMessage = computed(() => {
        if (warningLevel.value === 'critical') {
            return 'Plus que 5 minutes !';
        }
        if (warningLevel.value === 'warning') {
            return 'Moins de 15 minutes restantes';
        }
        return null;
    });

    const start = () => {
        applyTimer(gameState.value?.timer);
        tickInterval = setInterval(tick, 1000);
        syncInterval = setInterval(syncWithServer, syncIntervalMs);
        syncWithServer();
    };

    const stop = () => {
        if (tickInterval) clearInterval(tickInterval);
        if (syncInterval) clearInterval(syncInterval);
    };

    onUnmounted(stop);

    return {
        remainingSeconds,
        warningLevel,
        timerColorClass,
        timeAlertMessage,
        formatTime,
        applyTimer,
        syncWithServer,
        start,
        stop,
    };
}
