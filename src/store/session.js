import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from './api/kitsu-api.js';

import { useTotp } from '../composables/useTotp.js';

export const useAttendanceSessionStore = defineStore('sessionStore', () => {
    const records = ref(new Map());
    const isLoading = ref(false);
    const errors = ref(false);
    const activeSessionId = ref(null);

    const totpCalculator = useTotp();

    function setActiveSessionId(id) {
        activeSessionId.value = String(id);
    }

    function storeRecord(newRecord) {
        records.value.set(String(newRecord.id), newRecord);
    }

    function clearRecords() {
        records.value = new Map();
    }

    function clearErrors() {
        errors.value = false;
    }

    const all = computed(() => {
        void records.value.size;
        return [...records.value.values()];
    });

    function byId(id) {
        void records.value.size;
        return records.value.get(String(id));
    }

    async function fetchAll(includePaths = []) {
        clearErrors();
        isLoading.value = true;
        try {
            const config = prepareRequestConfig(includePaths);
            const { data } = await api.fetch('attendance-sessions', config);
            if (data) {
                clearRecords();
                data.forEach((form) => {
                    storeRecord(form);
                });
            }
        } catch (err) {
            console.error('Error while fetching sessions', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function fetchById(id, includePaths = []) {
        clearErrors();
        isLoading.value = true;
        try {
            const config = prepareRequestConfig(includePaths);
            const { data } = await api.fetch(`attendance-sessions/${id}`, config);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while fetching session by id: ${id}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    function prepareRequestConfig(includePaths = []) {
        const config = {};
        if (includePaths.length > 0) {
            config.params = {
                include: includePaths.join(','),
            };
        }
        return config;
    }

    async function generateTOTP(id) {
        clearErrors();
        isLoading.value = true;
        await fetchById(id);
        try {
            const { data } = await api.fetch(`attendance-sessions/${id}/totp`);
            const record = byId(id);
            if (record) {
                record.seed = data.seed;
                record['server-timestamp'] = data['server-timestamp'];
                record['time-window'] = data['time-window'];

                activeSessionId.value = String(id);

                totpCalculator.start(record, (newToken) => {
                    record.token = newToken;
                    record.timeRemaining = totpCalculator.timeRemaining.value;
                });
            }
        } catch (err) {
            console.error(`Error while generating TOTP for session with id: ${id}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    function stopTOTP(id = null) {
        const targetId = id ? String(id) : activeSessionId.value;

        if (targetId) {
            const record = byId(targetId);
            if (record) {
                delete record.token;
                delete record.seed;
                delete record['server-timestamp'];
                delete record['time-window'];
                delete record.timeRemaining;
            }
        }

        if (!id || String(id) === activeSessionId.value) {
            totpCalculator.stop();
            activeSessionId.value = null;
        }
    }

    const activeRecord = computed(() => {
        if (!activeSessionId.value) return null;
        return byId(activeSessionId.value) || null;
    });

    const currentToken = computed(() => {
        return totpCalculator.currentToken.value || '';
    });

    const currentTokenURL = computed(() => {
        return STUDIP.URLHelper.getURL('plugins.php/ElanAttendancePlugin/attendance_entry/qr_code') + `?token=${currentToken.value}&sessionid=${activeSessionId.value}`;
    });

    const remainingSeconds = computed(() => {
        return totpCalculator.timeRemaining.value;
    });

    return {
        records,
        isLoading,
        errors,
        activeSessionId,
        clearRecords,
        storeRecord,
        all,
        byId,
        fetchAll,
        fetchById,
        generateTOTP,
        stopTOTP,
        activeRecord,
        currentToken,
        currentTokenURL,
        remainingSeconds,
        setActiveSessionId,
    };
});
