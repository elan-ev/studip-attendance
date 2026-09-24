import { ref, onUnmounted, getCurrentInstance } from 'vue';
import * as OTPAuth from 'otpauth';

export function useTotp() {
    let timer = null;
    const currentToken = ref('');
    const timeRemaining = ref(0);

    function start(record, onTokenUpdate) {
        stop();

        if (!record || !record.seed || !record['server-timestamp'] || !record['time-window']) {
            console.warn('[useTotp] Unvollständiger Record übergeben:', record);
            return;
        }

        const seed = String(record.seed);
        const timeWindow = Number(record['time-window']);
        const serverTimestamp = Number(record['server-timestamp']);

        const timeOffset = serverTimestamp - Math.floor(Date.now() / 1000);

        const secret = OTPAuth.Secret.fromUTF8(seed);

        const totp = new OTPAuth.TOTP({
            algorithm: 'SHA1',
            digits: 6,
            period: timeWindow,
            secret: secret,
        });

        function update() {
            const nowInSeconds = Math.floor(Date.now() / 1000) + timeOffset;
            const remaining = timeWindow - (nowInSeconds % timeWindow);

            timeRemaining.value = remaining;

            // otpauth erwartet Millisekunden
            const generatedToken = totp.generate({ timestamp: nowInSeconds * 1000 });

            if (currentToken.value !== generatedToken) {
                currentToken.value = generatedToken;
                if (typeof onTokenUpdate === 'function') {
                    onTokenUpdate(generatedToken);
                }
            }
        }

        update();
        timer = setInterval(update, 1000);
    }

    function stop() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
        currentToken.value = '';
        timeRemaining.value = 0;
    }

    // Lifecycle-Hook nur registrieren, wenn der Aufruf im Kontext einer Komponente erfolgt
    if (getCurrentInstance()) {
        onUnmounted(() => {
            stop();
        });
    }

    return {
        currentToken,
        timeRemaining,
        start,
        stop,
    };
}
