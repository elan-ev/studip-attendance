<template>
    <article class="checkin-card" v-if="hasNextSession">
        <header class="checkin-header">
            <h2>{{ courseName }}</h2>
        </header>

        <div v-if="hasEntry" class="checkin-body">
            <div class="status-badge success" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div role="status" aria-live="polite" class="status-message success">
                <span class="sr-only">{{ $gettext('Erfolg:') }} </span>
                {{ $gettext('Anwesenheit erfolgreich erfasst') }}
            </div>
            <p class="status-detail" v-if="statusText">
                {{ $gettext('Status:') }} <strong>{{ statusText }}</strong>
            </p>
        </div>

        <div v-else class="checkin-body checkin-body-form">
            <p class="form-instruction">
                {{ $gettext('Bitte geben Sie den 6-stelligen Code aus der Vorlesung ein:') }}
            </p>

            <form @submit.prevent="submitPin" class="totp-input-group">
                <div class="pin-inputs" @paste="handlePaste">
                    <input v-for="(digit, index) in pinDigits" :key="index" :id="`pin-digit-${index}`"
                        v-model="pinDigits[index]" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                        class="pin-box" :disabled="isSubmitting" @input="onDigitInput(index, $event)"
                        @keydown="onDigitKeyDown(index, $event)" />
                </div>

                <div v-if="errorMessage" class="status-message error" role="alert">
                    {{ errorMessage }}
                </div>

                <button type="submit" class="button submit-button" :disabled="!isPinComplete || isSubmitting">
                    {{ isSubmitting ? $gettext('Wird überprüft...') : $gettext('Anwesenheit bestätigen') }}
                </button>
            </form>
        </div>
    </article>

    <article class="checkin-card idle-card" v-else>
        <header class="checkin-header">
            <h1>{{ $gettext('Keine aktuelle Veranstaltung gefunden') }}</h1>
        </header>
        <div class="checkin-body">
            <p class="idle-message">
                {{ $gettext('Innerhalb der nächsten 30 Minuten findet kein Termin für Ihre Veranstaltungen statt.') }}
            </p>
        </div>
    </article>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'

import { useAttendanceSessionStore } from './store/session.js'
import { useContextStore } from './store/context.js'
import { useEntryStore } from './store/entries.js'

const sessionStore = useAttendanceSessionStore()
const contextStore = useContextStore()
const entriesStore = useEntryStore()

const pinDigits = ref(['', '', '', '', '', ''])
const isSubmitting = ref(false)
const errorMessage = ref('')

const hasNextSession = computed(() => {
    return contextStore.nextSessionDate !== null
})

const courseName = computed(() => {
   return contextStore.nextSessionCourse?.name ?? $gettext('Unbekannte Veranstaltung')
});


const userEntry = computed(() => {
    const sessionId = sessionStore.activeSessionId
    const userId = contextStore.userId

    if (!sessionId || !userId) return null

    const entries = entriesStore.bySessionId(sessionId)

    return entries.find(entry =>
        String(entry['attendance-session-id'] ?? entry.attendance_session_id) === String(sessionId) &&
        String(entry['user-id'] ?? entry.user_id) === String(userId)
    ) ?? null
})

const hasEntry = computed(() => !!userEntry.value)

const isPinComplete = computed(() => pinDigits.value.every(digit => digit !== ''))

const statusText = computed(() => {
    const statusMap = {
        present: 'Anwesend',
        excused: 'Entschuldigt',
        absent: 'Abwesend'
    }
    return statusMap[userEntry.value?.status] ?? userEntry.value?.status ?? ''
})

const onDigitInput = (index, event) => {
    const val = event.target.value.replace(/[^0-9]/g, '')
    pinDigits.value[index] = val ? val[0] : ''

    if (val && index < 5) {
        nextTick(() => {
            document.getElementById(`pin-digit-${index + 1}`)?.focus()
        })
    }
}

const onDigitKeyDown = (index, event) => {
    if (event.key === 'Backspace' && !pinDigits.value[index] && index > 0) {
        document.getElementById(`pin-digit-${index - 1}`)?.focus()
    }
}

const handlePaste = (event) => {
    event.preventDefault()
    const pasted = event.clipboardData.getData('text').trim().replace(/[^0-9]/g, '')
    if (pasted) {
        for (let i = 0; i < 6; i++) {
            pinDigits.value[i] = pasted[i] ?? ''
        }
        const focusIndex = Math.min(pasted.length, 5)
        document.getElementById(`pin-digit-${focusIndex}`)?.focus()
    }
}

const submitPin = async () => {
    if (!isPinComplete.value) return

    isSubmitting.value = true
    errorMessage.value = ''

    const code = pinDigits.value.join('')

    try {
        await sessionStore.checkinWithPin(code)
    } catch (err) {
        errorMessage.value = err.message || $gettext('Der Code ist ungültig oder abgelaufen.')
    } finally {
        isSubmitting.value = false
    }
}

onMounted(async () => {
    await contextStore.loadNextSession()
})
</script>

<style scoped>
.checkin-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.checkin-header {
    background: #f8fafc;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    text-align: center;
}

.checkin-header h1 {
    margin: 0 0 0.35rem 0;
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.3;
}

.checkin-header h2 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 500;
    color: #64748b;
}

.checkin-body {
    padding: 1.5rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 64px;
    height: 64px;
    border-radius: 50%;
    margin-bottom: 1rem;
}

.status-badge.success {
    background-color: #dcfce7;
    color: #166534;
}

.status-badge.error {
    background-color: #fee2e2;
    color: #991b1b;
}

.status-badge svg {
    width: 32px;
    height: 32px;
    stroke-width: 2.5;
    stroke: currentColor;
}

.status-message {
    font-size: 1.05rem;
    font-weight: 600;
    margin: 0;
    line-height: 1.4;
}

.status-message.success {
    color: #15803d;
}

.status-message.error {
    color: #b91c1c;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.status-detail {
    margin: 0.75rem 0 0 0;
    font-size: 0.9rem;
    color: #475569;
}

/* Formular Styling */
.checkin-body-form {
    align-items: stretch;
}

.form-instruction {
    margin: 0 0 1rem 0;
    font-size: 0.95rem;
    color: #334155;
    text-align: center;
}

.totp-input-group {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
}

.pin-inputs {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.pin-box {
    width: 2.4rem;
    height: 3rem;
    text-align: center;
    font-size: 1.4rem;
    font-weight: 700;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    background-color: #f8fafc;
    transition: all 0.15s ease;
}

.pin-box:focus {
    border-color: #0284c7;
    background-color: #ffffff;
    outline: none;
    box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
}

.submit-button {
    width: 100%;
    max-width: 280px;
}

.idle-message {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

@media (max-width: 480px) {
    .checkin-header {
        padding: 1rem;
    }

    .checkin-header h1 {
        font-size: 1.05rem;
    }

    .pin-box {
        width: 2.1rem;
        height: 2.7rem;
        font-size: 1.25rem;
    }
}
</style>