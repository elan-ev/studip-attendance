<template>
    <table class="default">
        <caption>
            {{ $gettext('Termine') }}
        </caption>
        <colgroup>
            <col width="50%">
            <col width="50%">
        </colgroup>
        <thead>
            <tr>
                <th>{{ $gettext('Termin') }}</th>
                <th>{{ $gettext('Kategorie') }}</th>
            </tr>
        </thead>
        <tbody>
            <template v-for="session in sessions" :key="session.id">
                <tr v-if="session.termin.data">
                    <td><button class="as-link" @click="select(session.id)">{{ getDateText(session) }}</button></td>
                    <td>{{ session.termin.data.categories }}</td>
                </tr>
            </template>
        </tbody>
    </table>
</template>
<script setup>
import { useFormattedDateRange } from '@/composables/useFormattedDateRange.js';
const emit = defineEmits('selectSession');

const props = defineProps({
    sessions: {
        type: Array,
        required: true
    }
});

const getDateText = (session) => {
    const date = session.termin.data;
    if (!date) return '';

    const { formattedRange } = useFormattedDateRange(date.start, date.end);

    return formattedRange;
};

const select = (id) => {
    emit('selectSession', id);
}

</script>