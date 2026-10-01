import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { useContextStore } from './store/context';
import App from './StudipAttendanceWidgetStudentApp.vue';
import { gettext } from './i18n.js';

const el = document.getElementById('studip-attendance-widget-student-app');

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

    app.use(gettext);

    app.mount('#studip-attendance-widget-student-app');
}