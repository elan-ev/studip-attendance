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

        <button v-if="showActionButton" class="action-btn" @click="$emit('action', student)">
            <span class="btn-icon" v-if="student.status === 'present'">🚪</span>
            <span class="btn-icon" v-else-if="student.status === 'absent'">+</span>
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
        case 'absent': return 'badge--absent'
        case 'excused': return 'badge--excused'
        default: return ''
    }
})

const statusText = computed(() => {
    switch (props.student.status) {
        case 'present': return 'Anwesend'
        case 'absent': return 'Fehlt'
        case 'excused': return 'Entschuldigt'
        default: return ''
    }
})

const showActionButton = computed(() => {
    // enhance to present and excused if actions are needed
    return props.student.status === 'absent';
});

const actionLabel = computed(() => {
    switch (props.student.status) {
        case 'present': return 'Vorzeitig verlassen'
        case 'absent': return 'Manuell eintragen'
        case 'excused': return 'Status ändern'
        default: return ''
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