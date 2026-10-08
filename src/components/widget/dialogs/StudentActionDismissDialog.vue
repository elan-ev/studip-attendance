<template>
    <StudipDialog
        :height="600"
        :width="500"
        :title="$gettext('Vorzeitig verlassen')"
        confirm-class="accept"
        :close-text="$gettext('Abbrechen')"
        :confirm-text="$gettext('Speichern')"
        :open="open"
        @update:open="updateOpen"
        @confirm="updateEntry"
    >
     <template #dialogContent></template>
     </StudipDialog>
</template>
<script setup>
import StudipDialog from '@/components/studip/StudipDialog.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    student: { type: Object, default: null },
    sessionId: { type: String, default: '' }
});

const emit = defineEmits(['update:open']);
const updateOpen = (value) => {
    emit('update:open', value);
};

const updateEntry = () => {
    emit('update:open', false);
}
</script>