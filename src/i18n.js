import { createGettext } from 'vue3-gettext';
import translations from './locales/translations.json';

export const gettext = createGettext({
  availableLanguages: {
    en: 'English',
    de: 'Deutsch',
  },
  defaultLanguage: 'de',
  translations: translations,
  silent: true,
});