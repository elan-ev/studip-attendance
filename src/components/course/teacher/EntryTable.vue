<template>
    <table class="default attendance-course-teacher-entry-table">
        <caption>
            {{ $gettext('Teilnehmende') }}
        </caption>
        <colgroup>
            <col width="40%">
            <col>
            <col width="80">
        </colgroup>
        <thead>
            <tr>
                <th>{{ $gettext('Name') }}</th>
                <th>{{ $gettext('Status') }}</th>
                <th class="actions">{{ $gettext('Aktion') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="student in students" :key="student.id">
                <td>{{ student.name }}</td>
                <td :class="[student.status === 'present' ? 'present' : 'absent']">
                    <span class="chip">
                        {{ getReadableStatus(student.status) }}
                    </span>
                </td>
                <td class="actions">
                    <studip-action-menu :collapse-at="0" :items="[
                        student.entry
                            ? { id: 1, label: $gettext('Kommentar'), icon: 'edit', emit: 'comment' }
                            : { id: 1, label: $gettext('Manuell Eintragen'), icon: 'add', emit: 'add' },
                    ]" :context="student.name" @comment="editComment(student.entry, student.name)"
                        @add="addEntry(student)" />
                </td>
            </tr>
        </tbody>
    </table>
    <studip-dialog :open="showCommentDialog" :title="currentStudentName" :confirmText="$gettext('Speichern')"
        :closeText="$gettext('Abbrechen')" @confirm="updateComment" @update:open="closeCommentDialog">
        <template #dialogContent>
            <form class="default">
                <label>
                    {{ $gettext('Kommentar') }}
                    <textarea v-model="currentEntry.comment"></textarea>
                </label>
            </form>
        </template>
    </studip-dialog>
    <studip-dialog :open="showAddEntryDialog" :height="500" :title="$gettext('Eintrag hinzufügen')"
        :confirmText="$gettext('Hinzufügen')" :closeText="$gettext('Abbrechen')" @confirm="createEntry"
        @update:open="closeAddEntryDialog">
        <template #dialogContent>
            <div class="attendance-dialog-add-entry">
                <img class="attendance-dialog-add-entry-student-avatar" :src="currentAddEntryStudent.avatar" />
                <span class="attendance-dialog-add-entry-student-name">{{ currentAddEntryStudent.name }}</span>
            </div>
            <form class="default">
                <label>
                    {{ $gettext('Grund für manuelle Eingabe') }}
                    <textarea v-model="currentAddEntry.teacher_input_reason"></textarea>
                </label>
                <label>
                    {{ $gettext('Kommentar') }}
                    <textarea v-model="currentAddEntry.comment"></textarea>
                </label>
            </form>
        </template>
    </studip-dialog>
</template>
<script setup>
import { computed, ref } from 'vue';
import StudipActionMenu from '../../studip/StudipActionMenu.vue';
import StudipDialog from '../../studip/StudipDialog.vue';
import { useEntryStore } from '@/store/entries.js';
import { useContextStore } from '@/store/context.js';
import { useStudentStore } from '@/store/students.js';

const entryStore = useEntryStore();
const contextStore = useContextStore();
const studentsStore = useStudentStore();

const props = defineProps({
    sessionId: {
        type: Object,
        required: true
    }
});

const showCommentDialog = ref(false);
const currentEntry = ref(null);
const currentStudentName = ref(null);

const showAddEntryDialog = ref(false);
const currentAddEntryStudent = ref(null);
const currentAddEntry = ref(null);

const sessionEntries = computed(() => {
    return entryStore.bySessionId(props.sessionId);
});

const students = computed(() => {
    const studentsFromStore = studentsStore.byCourseId(contextStore.courseId);

    const entryByUserId = new Map(
        sessionEntries.value.map(entry => [entry.user.data.id, entry])
    );

    return studentsFromStore
        .sort((a, b) => (a['family-name'] || '').localeCompare(b['family-name'] || ''))
        .map(student => {
            const record = entryByUserId.get(student.id);

            return {
                id: student.id,
                name: student['family-name'] + ', ' + student['given-name'],
                avatar: student.meta.avatar.medium,
                status: record ? record.status : 'absent',
                entry: record || null
            }
        })
});

const editComment = (entry, name) => {
    currentStudentName.value = name;
    currentEntry.value = entry;
    showCommentDialog.value = true;
}

const updateComment = () => {
    const entry = currentEntry.value;
    const updateData = {
        id: entry.id,
        type: entry.type,
        comment: entry.comment,
        status: entry.status,
        source: entry.source
    }
    entryStore.updateEntry(updateData)
    closeCommentDialog();
}

const closeCommentDialog = () => {
    currentEntry.value = null;
    currentStudentName.value = null;
    showCommentDialog.value = false;
}

const addEntry = (student) => {
    currentAddEntryStudent.value = student;
    currentAddEntry.value = {
        teacher_input_reason: '',
        comment: ''
    };
    showAddEntryDialog.value = true;
}

const createEntry = () => {
    entryStore.createSessionEntry(props.sessionId, currentAddEntryStudent.value.id, 'present', currentAddEntry.value.comment, currentAddEntry.value.teacher_input_reason, 'teacher');
    closeAddEntryDialog();
}

const closeAddEntryDialog = () => {
    currentAddEntryStudent.value = null;
    showAddEntryDialog.value = false;
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
</script>

<style lang="scss" scoped>
.attendance-course-teacher-entry-table {

    td {
        span.chip {
            border-radius: 16px;
            padding: 4px 8px;
        }
    }

    td.present {
        span.chip {
            background-color: #f0fdf4;
            color: #008236;
        }

    }

    td.absent {
        span.chip {
            background-color: #fef2f2;
            color: #c2070d;
        }
    }
}

.attendance-dialog-add-entry {
    display: flex;
    flex-direction: row;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: solid thin #ccc;
    gap: 16px;

    .attendance-dialog-add-entry-student-avatar {
        width: 32px;
        height: 32px;
        border-radius: 100%;
    }

    .attendance-dialog-add-entry-student-name {
        font-size: 1.25em;
        line-height: 32px;
    }
}
</style>