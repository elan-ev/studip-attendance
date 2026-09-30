import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from './api/kitsu-api.js';

export const useEntryStore = defineStore('entryStore', () => {
    const records = ref(new Map());
    const recordsBySession = ref(new Map());
    const paginationBySession = ref(new Map());
    const isLoading = ref(false);
    const isLoadingMore = ref(false);
    const errors = ref(false);

    function getPaginationForSession(sessionId) {
        if (!paginationBySession.value.has(sessionId)) {
            paginationBySession.value.set(sessionId, {
                offset: 0,
                limit: 50,
                total: 0,
                hasMore: true,
            });
        }
        return paginationBySession.value.get(sessionId);
    }

    function storeRecord(newRecord, sessionId = null) {
        const targetSessionId = sessionId || newRecord['attendance_session_id'];
        records.value.set(String(newRecord.id), newRecord);

        if (targetSessionId) {
            if (!recordsBySession.value.has(targetSessionId)) {
                recordsBySession.value.set(targetSessionId, []);
            }

            const entries = recordsBySession.value.get(targetSessionId);
            const existingIndex = entries.findIndex((e) => String(e.id) === String(newRecord.id));

            if (existingIndex > -1) {
                entries[existingIndex] = newRecord;
            } else {
                entries.push(newRecord);
            }
        }
    }

    function storeRecords(newRecords, sessionId = null) {
        if (!newRecords || newRecords.length === 0) return;

        const targetSessionId = sessionId || newRecords[0]['attendance-session-id'] || newRecords[0]['session-id'];

        newRecords.forEach((rec) => records.value.set(String(rec.id), rec));

        if (targetSessionId) {
            const currentEntries = recordsBySession.value.get(targetSessionId) || [];
            const updatedEntries = [...currentEntries];

            newRecords.forEach((newRecord) => {
                const existingIndex = updatedEntries.findIndex((e) => String(e.id) === String(newRecord.id));
                if (existingIndex > -1) {
                    updatedEntries[existingIndex] = newRecord;
                } else {
                    updatedEntries.push(newRecord);
                }
            });

            recordsBySession.value.set(targetSessionId, updatedEntries);
        }
    }

    function clearRecords() {
        records.value = new Map();
        recordsBySession.value = new Map();
        paginationBySession.value = new Map();
    }

    const all = computed(() => {
        void records.value.size;
        return [...records.value.values()];
    });

    function byId(id) {
        void records.value.size;
        return records.value.get(String(id));
    }

    function bySessionId(sessionId) {
        return recordsBySession.value.get(sessionId) || [];
    }

    async function fetchBySessionId(sessionId, { loadMore = false } = {}) {
        const pagination = getPaginationForSession(sessionId);
        if (loadMore && (!pagination.hasMore || isLoadingMore.value)) {
            return;
        }

        if (loadMore) {
            isLoadingMore.value = true;
        } else {
            isLoading.value = true;
        }

        const currentOffset = loadMore ? pagination.offset + pagination.limit : 0;

        try {
            const { data, meta } = await api.fetch(`attendance-sessions/${sessionId}/entries`, {
                params: {
                    'page[offset]': currentOffset,
                    'page[limit]': pagination.limit,
                },
            });

            storeRecords(data, sessionId);

            if (meta?.page) {
                const total = meta.page.total ?? 0;
                const offset = meta.page.offset ?? currentOffset;
                const limit = meta.page.limit ?? pagination.limit;
                const hasMore = meta.page.hasMore ?? false;

                paginationBySession.value.set(sessionId, {
                    offset,
                    limit,
                    total,
                    hasMore,
                });
            }
        } catch (err) {
            console.error(`Error while fetching entries for session with id: ${sessionId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
            isLoadingMore.value = false;
        }
    }

    async function fetchAllBySessionId(sessionId) {
        let pagination = getPaginationForSession(sessionId);

        await fetchBySessionId(sessionId, { loadMore: false });

        let previousOffset = -1;

        while (pagination.hasMore && !errors.value) {
            if (pagination.offset === previousOffset) {
                console.warn(`Fetch stagnation detected for session ${sessionId}. Aborting fetchAll.`);
                break;
            }
            previousOffset = pagination.offset;

            await fetchBySessionId(sessionId, { loadMore: true });
            pagination = getPaginationForSession(sessionId);
        }
    }

    async function updateEntryStatus(entryId, statusData) {
        isLoading.value = true;
        try {
            const { data } = await api.patch(`attendance-entries/${entryId}`, statusData);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while updating entry record with id: ${entryId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    async function createRecord(entryData) {
        isLoading.value = true;
        try {
            const { data } = await api.post('attendance-entries', entryData);
            storeRecord(data);
        } catch (err) {
            console.error('Error while creating attendance entry record', err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        records,
        recordsBySession,
        paginationBySession,
        isLoading,
        isLoadingMore,
        errors,
        storeRecord,
        storeRecords,
        clearRecords,
        all,
        byId,
        bySessionId,
        fetchBySessionId,
        fetchAllBySessionId,
        updateEntryStatus,
        createRecord,
        getPaginationForSession,
    };
});
