<template>
    <div
        v-if="isOpen"
        ref="fullscreenContainer"
        class="fullscreen-overlay"
        role="dialog"
        aria-modal="true"
        :aria-label="title || $gettext('QR-Code Vollbildanzeige')"
    >
        <button
            class="close-btn"
            type="button"
            :title="$gettext('Vollbild beenden (ESC)')"
            :aria-label="$gettext('Schließen')"
            @click="exitFullscreen"
        >
            ✕
        </button>

        <article class="qr-card-fullscreen">
            <header v-if="title || dateRange" class="card-header">
                <h1 v-if="title" class="title">{{ title }}</h1>
                <time v-if="dateRange" class="time">{{ dateRange }}</time>
            </header>

            <main class="qr-content">
                <div class="qr-container">
                    <QRCodeVue
                        v-if="sessionStore.currentToken"
                        :value="sessionStore.currentTokenURL"
                        :size="420"
                        level="H"
                        render-as="svg"
                    />
                    <div v-else class="placeholder">
                        <span>{{ $gettext('Lade Token...') }}</span>
                    </div>
                </div>

                <p class="instruction-text">
                    {{ $gettext('Scannen Sie diesen Code ein') }}
                </p>
            </main>

            <footer class="token-box">
                <span class="token-code">
                    {{ sessionStore.currentToken || '------' }}
                </span>
                <small class="timer-text">
                    {{ $gettext('Erneuerung in:') }} {{ sessionStore.remainingSeconds }}s
                </small>
            </footer>
        </article>
    </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted } from 'vue';
import QRCodeVue from 'qrcode.vue';
import { useAttendanceSessionStore } from './../../store/session';

defineProps({
    title: {
        type: String,
        default: ''
    },
    dateRange: {
        type: String,
        default: ''
    }
});

const sessionStore = useAttendanceSessionStore();
const fullscreenContainer = ref(null);
const isOpen = ref(false);

async function enterFullscreen() {
    isOpen.value = true;
    await nextTick();

    if (!fullscreenContainer.value) return;

    try {
        if (fullscreenContainer.value.requestFullscreen) {
            await fullscreenContainer.value.requestFullscreen();
        } else if (fullscreenContainer.value.webkitRequestFullscreen) {
            await fullscreenContainer.value.webkitRequestFullscreen();
        }
    } catch (err) {
        console.error('Fehler beim Aktivieren des Vollbildmodus:', err);
        isOpen.value = false;
    }
}

async function exitFullscreen() {
    try {
        if (document.fullscreenElement || document.webkitFullscreenElement) {
            await document.exitFullscreen();
        }
    } catch (err) {
        console.error('Fehler beim Verlassen des Vollbildmodus:', err);
    } finally {
        isOpen.value = false;
    }
}

function handleFullscreenChange() {
    const isStillFullscreen = Boolean(
        document.fullscreenElement || document.webkitFullscreenElement
    );

    if (!isStillFullscreen) {
        isOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
});

onUnmounted(() => {
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
    document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
});

defineExpose({
    enterFullscreen,
    exitFullscreen
});
</script>

<style lang="scss" scoped>
.fullscreen-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #f2f1ed;
    padding: 32px;
    box-sizing: border-box;

    .close-btn {
        position: absolute;
        top: 24px;
        right: 32px;
        background: transparent;
        border: none;
        font-size: 32px;
        color: var(--dark-gray-color-80);
        cursor: pointer;
        line-height: 1;
        transition: color 0.15s ease-in-out;

        &:hover {
            color: var(--text-color);
        }

        &:focus-visible {
            outline: 2px solid  #0284c7;
            border-radius: 4px;
        }
    }

    .qr-card-fullscreen {
        text-align: center;
        max-width: 600px;
        width: 100%;

        .card-header {
            margin-bottom: 24px;

            .title {
                font-size: 28px;
                font-weight: bold;
                color: var(--text-color);
                margin: 0;
                line-height: 1.2;
            }

            .time {
                display: block;
                font-size: 22px;
                color: var(--dark-gray-color-80);
                margin-top: 6px;
            }
        }

        .qr-content {
            .qr-container {
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 24px 0;

                .placeholder {
                    width: 420px;
                    height: 420px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background-color:  #e2e8f0;
                    border-radius: 12px;

                    span {
                        font-size: 18px;
                        color: var(--dark-gray-color-80);
                    }
                }
            }

            .instruction-text {
                font-size: 18px;
                color: var(--dark-gray-color-80);
                margin: 16px 0 0 0;
            }
        }

        .token-box {
            margin-top: 16px;

            .token-code {
                font-size: 48px;
                font-weight: bold;
                letter-spacing: 4px;
                font-family: monospace;
                display: block;
                color: var(--text-color);
            }

            .timer-text {
                font-size: 18px;
                color: var(--dark-gray-color-80);
                margin-top: 8px;
                display: block;
            }
        }
    }
}
</style>