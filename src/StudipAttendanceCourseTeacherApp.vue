<template>
    <session-table v-if="!selectedSession" :sessions="allSessions" @selectSession="selectSession" />
    <template v-if="selectedSession">
        <div class="attendance-course-teacher-header">
            <button class="button" @click="resetSession">{{ $gettext('Zurück zur Übersicht') }}</button>
            <span class="attendance-course-teacher-title">{{ $gettext('Termin:')}} {{ sessionName }}</span>
        </div>
        <entry-table :session-id="selectedSession"/>
    </template>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import EntryTable from './components/course/teacher/EntryTable.vue';
import SessionTable from './components/course/teacher/SessionTable.vue';
import { useFormattedDateRange } from '@/composables/useFormattedDateRange.js';

import { useAttendanceSessionStore } from './store/session.js';
import { useContextStore } from './store/context.js';
import { useEntryStore } from './store/entries.js';
import { useStudentStore } from './store/students.js';


const contextStore = useContextStore();
const sessionStore = useAttendanceSessionStore();
const entryStore = useEntryStore();
const studentsStore = useStudentStore();

const selectedSession = ref(null);

const sessionName = computed(() => {
    const session = sessionStore.byId(selectedSession.value);

    if (!session) return '';

    const { formattedRange } = useFormattedDateRange(session.termin.data.start, session.termin.data.end);

    return formattedRange;
});

const cid = computed(() => {
    return contextStore.courseId;
});

const allSessions = computed(() => {
    return sessionStore.all;
});

const selectSession = (e) => {
    selectedSession.value = e;
    entryStore.fetchAllBySessionId(e);
}

const resetSession = () => {
    selectedSession.value = null;
    entryStore.clearRecords();
}

onMounted(async () => {
    await sessionStore.fetchByCourse(cid.value, ['termin']);
    await studentsStore.fetchAllByCourseId(cid.value);
});
</script>

<style lang="scss" scoped>
.attendance-course-teacher-header {
    display: flex;
    flex-direction: row-reverse;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;

    .attendance-course-teacher-title {
        font-size: 24px;
        vertical-align: middle;
    }
}
</style>