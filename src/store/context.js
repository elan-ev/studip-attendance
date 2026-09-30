import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { api } from './api/kitsu-api.js';
import { useStudentStore } from './students.js';
import { useEntryStore } from './entries.js';
import { useAttendanceSessionStore } from './session.js';

export const useContextStore = defineStore('contextStore', () => {
    const isLoading = ref(false);
    const errors = ref(false);
    const nextSessionCourse = ref(null);
    const preferredLanguage = ref('de_DE');
    const nextSessionDate = ref(null);

    const languageIsGerman = computed(() => preferredLanguage.value === 'de-DE');

    const langSelector = computed(() => {
        return languageIsGerman.value ? 'de' : 'en';
    });

    function setPreferredLanguage(language) {
        preferredLanguage.value = language;
    }

    function clearErrors() {
        errors.value = false;
    }

    async function loadNextSession() {
        if (isLoading.value) {
            return;
        }
        clearErrors();
        isLoading.value = true;

        const studentsStore = useStudentStore();
        const entriesStore = useEntryStore();
        const sessionStore = useAttendanceSessionStore();

        try {
            const data = await api.fetch(`/users/me/next-attendance-session`);
            nextSessionDate.value = data['course-date'] ?? null;
            if (data.course) {
                nextSessionCourse.value = data.course;

                if (data.students && Array.isArray(data.students)) {
                    studentsStore.storeRecords(data.students, data.course.seminar_id);
                }
            }
            if (data.session) {
                sessionStore.storeRecord(data.session);
                sessionStore.setActiveSessionId(data.session.id);

                if (data.entries && Array.isArray(data.entries)) {
                    entriesStore.storeRecords(data.entries, data.session.id);
                }
            }

            
        } catch (err) {
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        preferredLanguage,
        isLoading,
        errors,
        nextSessionCourse,
        nextSessionDate,
        langSelector,
        setPreferredLanguage,
        loadNextSession,
    };
});
