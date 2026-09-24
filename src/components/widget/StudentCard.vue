<template>
    <div class="student-card">
        <div class="avatar-wrapper">
            <img :src="student.avatar" :alt="student.name" class="avatar-img" />
        </div>
        <div class="student-name">{{ student.name }}</div>
        
        <div class="badge-wrapper">
            <span class="status-badge" :class="badgeClass">
                {{ statusText }}
            </span>
        </div>

        <button class="action-btn" @click="$emit('action', student)">
            <span class="btn-icon" v-if="student.status === 'present'">🚪</span>
            <span class="btn-icon" v-else-if="student.status === 'absent_unexcused'">+</span>
            <span class="btn-icon" v-else>✏️</span>
            {{ actionLabel }}
        </button>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    student: {
        type: Object,
        required: true
    }
})

defineEmits(['action'])

const badgeClass = computed(() => {
    switch (props.student.status) {
        case 'present': return 'badge--present'
        case 'absent_unexcused': return 'badge--absent'
        case 'absent_excused': return 'badge--excused'
        default: return ''
    }
})

const statusText = computed(() => {
    switch (props.student.status) {
        case 'present': return 'Anwesend'
        case 'absent_unexcused': return 'Fehlt'
        case 'absent_excused': return 'Entschuldigt'
        default: return ''
    }
})

const actionLabel = computed(() => {
    switch (props.student.status) {
        case 'present': return 'Vorzeitig verlassen'
        case 'absent_unexcused': return 'Manuell eintragen'
        default: return 'Status ändern'
    }
})
</script>

<style scoped>
.student-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px 12px 12px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.avatar-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 8px;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-name {
    font-size: 14px;
    font-weight: 500;
    color: #1e293b;
    margin-bottom: 6px;
    text-align: center;
}

.badge-wrapper {
    margin-bottom: 12px;
}

.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.badge--present {
    background-color: #dcfce7;
    color: #166534;
}

.badge--absent {
    background-color: #fecdd3;
    color: #9f1239;
}

.badge--excused {
    background-color: #e0f2fe;
    color: #0369a1;
}

.action-btn {
    width: 100%;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 6px 8px;
    font-size: 11px;
    color: #334155;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.action-btn:hover {
    background-color: #f8fafc;
}
</style>