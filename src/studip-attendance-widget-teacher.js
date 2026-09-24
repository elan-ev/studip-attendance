import { createApp } from 'vue';
import { createPinia } from 'pinia';
// import { useContextStore } from './store/context';
import App from './StudipAttendanceWidgetTeacherApp.vue';
// import { gettext } from './i18n.js';
import { createGettext } from 'vue3-gettext';
import translations from './locales/translations.json';

const el = document.getElementById('studip-attendance-widget-teacher-app');

if (el) {
    const app = createApp(App);

    const pinia = createPinia();
    app.use(pinia);

    // const contextStore = useContextStore();
    const preferredLanguage = el?.dataset?.preferredLanguage || null;
    if (preferredLanguage) {
        // contextStore.setPreferredLanguage(preferredLanguage);
    }

    const userId = el?.dataset?.userId || null;
    if (userId) {
        // contextStore.setUserId(userId);
    }
    const gettext = createGettext({
        availableLanguages: {
            en: 'English',
            de: 'Deutsch',
        },
        defaultLanguage: 'de',
        translations: translations,
        silent: true,
    });
    app.use(gettext);

    app.mount('#studip-attendance-widget-teacher-app');
}