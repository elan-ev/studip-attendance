<template>
    <article class="qr-card">
        <header v-if="title || dateRange" class="card-header">
            <h3 v-if="title" class="card-title">{{ title }}</h3>
            <time v-if="dateRange" class="card-date">{{ dateRange }}</time>
        </header>

        <main class="qr-content">
            <div class="qr-container">
                <QRCodeVue
                    v-if="sessionStore.currentToken"
                    :value="sessionStore.currentTokenURL"
                    :size="200"
                    level="H"
                    render-as="svg"
                />
                <div v-else class="qr-placeholder">
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
            <small class="token-timer">
                {{ $gettext('Erneuerung in:') }} {{ sessionStore.remainingSeconds }}s
            </small>
        </footer>
    </article>
</template>

<script setup>
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
</script>

<style lang="scss" scoped>
.qr-card {
    width: 100%;
    max-width: 400px;
    padding: 16px;
    box-sizing: border-box;
    text-align: center;
    background-color: var(--white, #ffffff);
    border-radius: 8px;

    .card-header {
        margin-bottom: 12px;

        .card-title {
            font-size: 18px;
            font-weight: bold;
            color: var(--text-color);
            margin: 0;
            line-height: 1.3;
        }

        .card-date {
            display: block;
            font-size: 14px;
            color: var(--dark-gray-color-80);
            margin-top: 2px;
        }
    }

    .qr-content {
        .qr-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 12px 0;

            .qr-placeholder {
                width: 200px;
                height: 200px;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #e2e8f0;
                border-radius: 8px;

                span {
                    font-size: 14px;
                    color: var(--dark-gray-color-80);
                }
            }
        }

        .instruction-text {
            font-size: 14px;
            margin: 8px 0 0 0;
            color: var(--dark-gray-color-80);
        }
    }

    .token-box {
        margin-top: 8px;

        .token-code {
            display: block;
            font-family: monospace;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
            color: var(--text-color);
        }

        .token-timer {
            display: block;
            font-size: 14px;
            color: var(--dark-gray-color-80);
            margin-top: 4px;
        }
    }
}
</style>