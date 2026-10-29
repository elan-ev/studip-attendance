import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { useContextStore } from './store/context';
import App from './StudipAttendanceCourseStudentApp.vue';
import { gettext } from './i18n.js';

const el = document.getElementById('studip-attendance-course-student-app');

if (el) {
    const app = createApp(App);

    const pinia = createPinia();
    app.use(pinia);

    const contextStore = useContextStore();
    const preferredLanguage = el?.dataset?.preferredLanguage || null;
    if (preferredLanguage) {
        contextStore.setPreferredLanguage(preferredLanguage);
    }

    const userId = el?.dataset?.userId || null;
    if (userId) {
        contextStore.setUserId(userId);
    }

    const courseId = el?.dataset?.courseId || null;
    if (userId) {
        contextStore.setCourseId(courseId);
    }

    app.use(gettext);

    app.mount('#studip-attendance-course-student-app');
}