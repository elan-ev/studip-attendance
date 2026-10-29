<template>
    <div class="studip-attendance-index">
        <h2>{{ $gettext('Einträge') }}</h2>
        <ul class="attendance-list" v-if="allEntries.length > 0">
            <li v-for="entry in allEntries" :key="entry.id" class="attendance-item">
                <div class="attendance-info">
                    <h3>{{ getSessionName(entry) }}</h3>
                </div>
                <div class="attendance-status">
                    <span class="status-badge" :class="entry.status">
                        {{ getReadableStatus(entry.status) }}
                    </span>
                </div>
            </li>
        </ul>
        <p v-else class="empty-state">{{ $gettext('Keine Anwesenheitseinträge vorhanden.') }}</p>
    </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useFormattedDateRange } from '@/composables/useFormattedDateRange.js';
import { useContextStore } from './store/context.js';
import { useEntryStore } from './store/entries.js';

const contextStore = useContextStore();
const entryStore = useEntryStore();

const cid = computed(() => {
    return contextStore.courseId;
});

const allEntries = computed(() => {
    return entryStore.all;
});

const getSessionName = (entry) => {
    const start = entry.meta && entry.meta['termin-start'] ? entry.meta['termin-start'] : null;
    const end = entry.meta && entry.meta['termin-end'] ? entry.meta['termin-end'] : null;
    
    if (!start || !end) {
        return 'Termin';
    }

    const { formattedRange } = useFormattedDateRange(start, end);
    return formattedRange;
}

const getReadableStatus = (status) => {
    let statusText = '';
    switch(status) {
        case 'absent':
            statusText = 'Abwesend';
            break;
        case 'present':
            statusText = 'Anwesend';
            break;
        default:
            statusText = status;
    }

    return statusText;
}

onMounted(async () => {
    await entryStore.loadUsersCourseEntries(cid.value);
});
</script>

<style scoped>
.studip-attendance-index {
    margin: 1.5rem 0;
}

.attendance-list {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
    max-width: 48em;
}

.attendance-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 4px;
    border-bottom: 1px solid #ccc;
    &:last-child {
        border: none;
    }
}

.attendance-info h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: normal;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.85rem;
}

.status-badge.present {
    background-color: #f0fdf4;
    color: #008236;
}

.status-badge.absent {
    background-color: #fef2f2;
    color: #c2070d;
}

.empty-state {
    font-style: italic;
    color: #666;
}
</style>