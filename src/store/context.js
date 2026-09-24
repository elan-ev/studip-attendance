import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useContextStore = defineStore('contextStore', () => {
    const preferredLanguage = ref('de_DE');

    const languageIsGerman = computed(() => preferredLanguage.value === 'de-DE');

    const langSelector = computed(() => {
        return languageIsGerman.value ? 'de' : 'en';
    });

    function setPreferredLanguage(language) {
        preferredLanguage.value = language;
    }

     return {
        preferredLanguage,
        langSelector,
        setPreferredLanguage,
     }

});