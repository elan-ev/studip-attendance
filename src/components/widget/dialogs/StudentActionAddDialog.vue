<template>
    <StudipDialog :height="500" :title="$gettext('Teilnehmer manuell eintragen')" confirm-class="accept"
        :close-text="$gettext('Abbrechen')" :confirm-text="$gettext('Eintragen')" :open="open" @update:open="updateOpen"
        @confirm="createEntry">
        <template #dialogContent>
            <template v-if="student">
                <div class="attendance-dialog-add-entry">
                    <img class="attendance-dialog-add-entry-student-avatar" :src="student.avatar" />
                    <span class="attendance-dialog-add-entry-student-name">{{ student.name }}</span>
                </div>
                <form class="default">
                    <label>
                        {{ $gettext('Grund für manuelle Eingabe') }}
                        <textarea v-model="reason"></textarea>
                    </label>
                    <label>
                        {{ $gettext('Kommentar') }}
                        <textarea v-model="comment"></textarea>
                    </label>
                </form>
            </template>
        </template>
    </StudipDialog>
</template>
<script setup>
import { ref } from 'vue';
import StudipDialog from '@/components/studip/StudipDialog.vue';
import { useEntryStore } from '@/store/entries.js'

const props = defineProps({
    open: { type: Boolean, default: false },
    student: { type: Object, default: null },
    sessionId: { type: String, default: '' }
});

const reason = ref('');
const comment = ref('');

const emit = defineEmits(['update:open']);

const entriesStore = useEntryStore()

const updateOpen = (value) => {
    emit('update:open', value);
};

const createEntry = async () => {
    const userId = props.student.id;
    await entriesStore.createSessionEntry(props.sessionId, userId, 'present', comment.value, reason.value, 'teacher');
    emit('update:open', false);
}
</script>

<style lang="scss" scoped>

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