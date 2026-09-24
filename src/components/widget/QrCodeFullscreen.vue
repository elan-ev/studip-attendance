<template>
    <div 
        v-if="isOpen" 
        ref="fullscreenContainer" 
        class="fullscreen-overlay"
    >
        <button 
            class="close-btn" 
            @click="exitFullscreen"
            title="Vollbild beenden (ESC)"
        >
            ✕
        </button>

        <div class="qr-card-fullscreen">
            <h1 class="title" v-if="title">{{ title }}</h1>
            
            <div class="qr-container">
                <QRCodeVue 
                    v-if="sessionStore.currentToken" 
                    :value="sessionStore.currentToken" 
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

            <div class="token-box">
                <span class="token-code">
                    {{ sessionStore.currentToken || '------' }}
                </span>
                <small class="timer-text">
                    {{ $gettext('Erneuerung in:') }} {{ sessionStore.remainingSeconds }}s
                </small>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import QRCodeVue from 'qrcode.vue'
import { useAttendanceSessionStore } from './../../store/session'

defineProps({
    title: {
        type: String,
        default: ''
    }
})

const sessionStore = useAttendanceSessionStore()
const fullscreenContainer = ref(null)
const isOpen = ref(false)

async function enterFullscreen() {
    isOpen.value = true
    await nextTick()

    if (!fullscreenContainer.value) return

    try {
        if (fullscreenContainer.value.requestFullscreen) {
            await fullscreenContainer.value.requestFullscreen()
        } else if (fullscreenContainer.value.webkitRequestFullscreen) {
            await fullscreenContainer.value.webkitRequestFullscreen()
        }
    } catch (err) {
        console.error('Fehler beim Aktivieren des Vollbildmodus:', err)
        isOpen.value = false
    }
}

async function exitFullscreen() {
    if (document.fullscreenElement || document.webkitFullscreenElement) {
        await document.exitFullscreen()
    } else {
        isOpen.value = false
    }
}

function handleFullscreenChange() {
    const isStillFullscreen = Boolean(
        document.fullscreenElement || document.webkitFullscreenElement
    )
    
    if (!isStillFullscreen) {
        isOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('fullscreenchange', handleFullscreenChange)
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange)
})

onUnmounted(() => {
    document.removeEventListener('fullscreenchange', handleFullscreenChange)
    document.removeEventListener('webkitfullscreenchange', handleFullscreenChange)
})

defineExpose({
    enterFullscreen
})
</script>

<style scoped>
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
    background: #f2f1ed;
    padding: 32px;
    box-sizing: border-box;
}

.close-btn {
    position: absolute;
    top: 24px;
    right: 32px;
    background: transparent;
    border: none;
    font-size: 32px;
    color: #475569;
    cursor: pointer;
    line-height: 1;
}

.close-btn:hover {
    color: #0f172a;
}

.qr-card-fullscreen {
    text-align: center;
    max-width: 600px;
    width: 100%;
}

.title {
    font-size: 28px;
    font-weight: bold;
    color: #1e293b;
    margin-bottom: 24px;
}

.qr-container {
    display: flex;
    justify-content: center;
    margin: 24px 0;
}

.instruction-text {
    font-size: 18px;
    color: #475569;
    margin-top: 16px;
}

.token-code {
    font-size: 48px;
    font-weight: bold;
    letter-spacing: 4px;
    font-family: monospace;
    display: block;
    color: #0f172a;
}

.timer-text {
    font-size: 16px;
    color: #64748b;
    margin-top: 8px;
    display: block;
}

.placeholder {
    width: 420px;
    height: 420px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e2e8f0;
    border-radius: 12px;
}
</style>