<template>
    <StudipDialog :height="300" :width="500" :title="$gettext('Teilnehmer manuell eintragen')" confirm-class="accept"
        :close-text="$gettext('Abbrechen')" :confirm-text="$gettext('Eintragen')" :open="open" @update:open="updateOpen"
        @confirm="createEntry">
        <template #dialogContent>
            <div v-if="student" class="student-info">
                <p>
                    <strong>{{ $gettext('Eintrag erstellen für:') }}</strong>
                    {{ student.name }}
                </p>
            </div>
        </template>
    </StudipDialog>
</template>
<script setup>
import StudipDialog from '@/components/studip/StudipDialog.vue';
import { useEntryStore } from '@/store/entries.js'

const props = defineProps({
    open: { type: Boolean, default: false },
    student: { type: Object, default: null },
    sessionId: { type: String, default: '' }
});

const emit = defineEmits(['update:open']);

const entriesStore = useEntryStore()

const updateOpen = (value) => {
    emit('update:open', value);
};

const createEntry = async () => {
    const userId = props.student.id;
    await entriesStore.createSessionEntry(props.sessionId, userId, 'present', '', 'teacher');
    emit('update:open', false);
}
</script>