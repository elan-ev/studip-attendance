<template>
    <div class="qr-card" style="width: 100%; max-width: 320px; padding: 16px; box-sizing: border-box; text-align: center;">
        <div class="card-top-bar" v-if="title" style="font-weight: bold; margin-bottom: 12px; font-size: 14px; color: #1e293b;">
            {{ title }}
        </div>
        
        <div class="qr-container" style="display: flex; justify-content: center; margin: 12px 0;">
            <QRCodeVue 
                v-if="sessionStore.currentToken" 
                :value="sessionStore.currentTokenURL" 
                :size="200" 
                level="H" 
                render-as="svg" 
            />
            <div v-else style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center; background: #e2e8f0; border-radius: 8px;">
                <span style="font-size: 12px; color: #64748b;">{{ $gettext('Lade Token...') }}</span>
            </div>
        </div>

        <p class="instruction-text" style="font-size: 12px; margin-top: 8px; color: #475569;">
            {{ $gettext('Scannen Sie diesen Code ein') }}
        </p>

        <div class="token-box" style="margin-top: 8px;">
            <span class="token-code" style="font-size: 24px; font-weight: bold; letter-spacing: 2px; font-family: monospace; display: block; color: #0f172a;">
                {{ sessionStore.currentToken || '------' }}
            </span>
            <small style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">
                {{ $gettext('Erneuerung in:') }} {{ sessionStore.remainingSeconds }}s
            </small>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import QRCodeVue from 'qrcode.vue'
import { useAttendanceSessionStore } from './../../store/session'

defineProps({
    title: {
        type: String,
        default: ''
    }
})

const sessionStore = useAttendanceSessionStore()

</script>