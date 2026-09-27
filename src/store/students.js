import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { api } from './api/kitsu-api.js';

export const useStudentStore = defineStore('studentStore', () => {
    const records = ref(new Map());
    const recordsByCourse = ref(new Map());
    const paginationByCourse = ref(new Map());
    const isLoading = ref(false);
    const isLoadingMore = ref(false);
    const errors = ref(false);

    function getPaginationForCourse(courseId) {
        if (!paginationByCourse.value.has(courseId)) {
            paginationByCourse.value.set(courseId, {
                offset: 0,
                limit: 30,
                total: 0,
                hasMore: true,
            });
        }
        return paginationByCourse.value.get(courseId);
    }

    function storeRecord(newRecord, courseId = null) {
        const targetCourseId = courseId || newRecord['course-id'] || newRecord['seminar-id'];
        records.value.set(String(newRecord.id), newRecord);

        if (targetCourseId) {
            if (!recordsByCourse.value.has(targetCourseId)) {
                recordsByCourse.value.set(targetCourseId, []);
            }

            const students = recordsByCourse.value.get(targetCourseId);
            const existingIndex = students.findIndex((m) => String(m.id) === String(newRecord.id));

            if (existingIndex > -1) {
                students[existingIndex] = newRecord;
            } else {
                students.push(newRecord);
            }
        }
    }

    function storeRecords(newRecords, courseId = null) {
        if (!newRecords || newRecords.length === 0) return;

        const targetCourseId = courseId || newRecords[0]['course-id'] || newRecords[0]['seminar-id'];

        newRecords.forEach((rec) => records.value.set(String(rec.id), rec));

        if (targetCourseId) {
            const currentStudents = recordsByCourse.value.get(targetCourseId) || [];
            const updatedStudents = [...currentStudents];

            newRecords.forEach((newRecord) => {
                const existingIndex = updatedStudents.findIndex((m) => String(m.id) === String(newRecord.id));
                if (existingIndex > -1) {
                    updatedStudents[existingIndex] = newRecord;
                } else {
                    updatedStudents.push(newRecord);
                }
            });

            recordsByCourse.value.set(targetCourseId, updatedStudents);
        }
    }

    function clearRecords() {
        records.value = new Map();
        recordsByCourse.value = new Map();
        paginationByCourse.value = new Map();
    }

    const all = computed(() => {
        void records.value.size;
        return [...records.value.values()];
    });

    function byId(id) {
        void records.value.size;
        return records.value.get(String(id));
    }

    function byCourseId(courseId) {
        return recordsByCourse.value.get(courseId) || [];
    }

    async function fetchByCourseId(courseId, { loadMore = false } = {}) {
        const pagination = getPaginationForCourse(courseId);
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
            const { data, meta } = await api.fetch(`courses/${courseId}/members`, {
                params: {
                    'page[offset]': currentOffset,
                    'page[limit]': pagination.limit,
                },
            });

            storeRecords(data, courseId);

            if (meta?.page) {
                const total = meta.page.total ?? 0;
                const offset = meta.page.offset ?? currentOffset;
                const limit = meta.page.limit ?? pagination.limit;
                const hasMore = meta.page.hasMore ?? false;

                paginationByCourse.value.set(courseId, {
                    offset,
                    limit,
                    total,
                    hasMore,
                });
            }
        } catch (err) {
            console.error(`Error while fetching students for course with id: ${courseId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
            isLoadingMore.value = false;
        }
    }

    async function fetchAllByCourseId(courseId) {
    let pagination = getPaginationForCourse(courseId);
    
    await fetchByCourseId(courseId, { loadMore: false });

    let previousOffset = -1;

    while (pagination.hasMore && !errors.value) {
        if (pagination.offset === previousOffset) {
            console.warn(`Fetch stagnation detected for course ${courseId}. Aborting fetchAll.`);
            break;
        }
        previousOffset = pagination.offset;

        await fetchByCourseId(courseId, { loadMore: true });
        pagination = getPaginationForCourse(courseId);
    }
}

    async function fetchById(studentId) {
        isLoading.value = true;
        try {
            const { data } = await api.fetch(`users/${studentId}`);
            storeRecord(data);
        } catch (err) {
            console.error(`Error while fetching student with id: ${studentId}`, err);
            errors.value = err;
        } finally {
            isLoading.value = false;
        }
    }

    return {
        records,
        recordsByCourse,
        paginationByCourse,
        isLoading,
        isLoadingMore,
        errors,
        storeRecord,
        storeRecords,
        clearRecords,
        all,
        byId,
        byCourseId,
        fetchByCourseId,
        fetchAllByCourseId,
        fetchById,
        getPaginationForCourse,
    };
});