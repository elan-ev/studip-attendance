<template>
    <template v-if="isLoading"></template>
    <template v-else>
        <div v-if="hasNextSession" class="attendance-widget-teacher-wrapper">
            <header class="attendance-widget-teacher-header">
                <h3 class="attendance-widget-teacher-header-title">{{ courseName }}</h3>
                <time class="attendance-widget-teacher-header-subtitle">{{ courseDateText }}</time>
            </header>
            <div class="kpi-row">
                <KPICard :value="kpiStudentsPresent + ' / ' + students.length" :label="$gettext('Anwesend')" />
                <KPICard :value="kpiStudentsAbsent.toString()" :label="$gettext('Fehlt')" />
                <KPICard :value="kpiStudentsExcused.toString()" :label="$gettext('Entschuldigt')" />
            </div>

            <h4 class="section-title">{{ $gettext('Teilnehmende') }}</h4>

            <div class="student-grid">
                <StudentCard v-for="student in students" :key="student.id" :student="student"
                    @action="performStudentAction" />
            </div>

            <div class="action-bar">
                <span class="action-title">{{ $gettext('QR-Code Anzeigen') }}</span>
                <div class="action-buttons">
                    <button class="button" @click="openQrPiP">{{ $gettext('Picture-in-Picture-Modus') }}</button>
                    <button class="button" @click="openFullscreen">{{ $gettext('Vollbildansicht') }}</button>
                </div>
            </div>

            <QrCodeFullscreen ref="fullscreenRef" :title="courseName" :date-range="courseDateText"/>
        </div>
        <article class="attendance-widget-wrapper-idle" v-else>
            <header>
                <h2>{{ $gettext('Keine Aktuelle Veranstaltung gefunden') }}</h2>
            </header>
            <p>
                {{ $gettext('Innerhalb der nächsten 30 Minuten findet kein Termin für Ihre Veranstaltungen statt.') }}
            </p>
        </article>

        <StudentActionAddDialog v-model:open="showStudentActionAddDialog" :student="selectedStudent" :session-id="sessionId" />
        <StudentActionDismissDialog v-model:open="showStudentActionDismissDialog" :student="selectedStudent" :session-id="sessionId" />
        <StudentActionUpdateDialog v-model:open="showStudentActionUpdateDialog" :student="selectedStudent" :session-id="sessionId" />

    </template>
</template>

<script setup>
import { ref, render, h, getCurrentInstance, onMounted, computed, onBeforeUnmount, watch } from 'vue'
import KPICard from './components/widget/KPICard.vue'
import StudentCard from './components/widget/StudentCard.vue'
import StudentActionAddDialog from './components/widget/dialogs/StudentActionAddDialog.vue'
import StudentActionDismissDialog from './components/widget/dialogs/StudentActionDismissDialog.vue'
import StudentActionUpdateDialog from './components/widget/dialogs/StudentActionUpdateDialog.vue'
import QrCodePip from './components/widget/QrCodePip.vue'
import QrCodeFullscreen from './components/widget/QrCodeFullscreen.vue'

import { useAttendanceSessionStore } from './store/session.js'
import { useContextStore } from './store/context.js'
import { useStudentStore } from './store/students.js';
import { useEntryStore } from './store/entries.js'

import { useFormattedDateRange } from '@/composables/useFormattedDateRange.js';

const sessionStore = useAttendanceSessionStore()
const contextStore = useContextStore();
const studentsStore = useStudentStore();
const entriesStore = useEntryStore();
const currentInstance = getCurrentInstance()

const fullscreenRef = ref(null)
const showStudentActionAddDialog = ref(false)
const showStudentActionDismissDialog = ref(false);
const showStudentActionUpdateDialog = ref(false);
const selectedStudent = ref(null);

const courseName = computed(() => {
    return contextStore.nextSessionCourse?.name ?? '';
})

const courseDateText = computed(() => {
    if (!hasNextSession.value) {
        return ''
    }
    const { formattedRange } = useFormattedDateRange(contextStore.nextSessionDate.date, contextStore.nextSessionDate.end_time);

    return formattedRange;
});

const sessionId = computed(() => {
    const sessionId = sessionStore.activeSessionId ?? '';

    return sessionId;
})

const sessionEntries = computed(() => {
    const sessionId = sessionStore.activeSessionId;
    return sessionId ? (entriesStore.bySessionId(sessionId) || []) : [];
})

const hasNextSession = computed(() => {
    return contextStore.nextSessionDate !== null;
});

const isLoading = ref(true);

const students = computed(() => {
    const seminarId = contextStore.nextSessionCourse?.seminar_id;
    if (!seminarId) {
        return [];
    }

    const rawStudents = studentsStore.byCourseId(seminarId) || [];

    const entryByUserId = new Map(
        sessionEntries.value.map(entry => [entry.user_id, entry])
    );

    return rawStudents.map(student => {
        const record = entryByUserId.get(student.user_id);

        return {
            id: student.user_id,
            name: `${student.vorname || ''} ${student.nachname || ''}`.trim() || 'Unbekannt',
            avatar: student.avatar,
            status: record ? record.status : 'absent',
            entry: record || null
        };
    });
});

const kpiStudentsPresent = computed(() => {
    const present = students.value.filter((student) => student.status === 'present').length;

    return present;
});

const kpiStudentsAbsent = computed(() => {
    const absent = students.value.filter((student) => student.status === 'absent').length;

    return absent;
});

const kpiStudentsExcused = computed(() => {
    const excused = students.value.filter((student) => student.status === 'excused').length;

    return excused;
});


async function openFullscreen() {
    await sessionStore.generateTOTP(sessionStore.activeSessionId)
    fullscreenRef.value?.enterFullscreen()
}

async function openQrPiP() {
    if (!('documentPictureInPicture' in window)) {
        alert('Document Picture-in-Picture wird von diesem Browser leider nicht unterstützt.')
        return
    }

    await sessionStore.generateTOTP(sessionStore.activeSessionId)

    const pipWindow = await window.documentPictureInPicture.requestWindow({
        width: 360,
        height: 480,
    })

    document.querySelectorAll('link[rel="stylesheet"], style').forEach((node) => {
        pipWindow.document.head.appendChild(node.cloneNode(true))
    })

    pipWindow.document.body.style.margin = '0'
    pipWindow.document.body.style.display = 'flex'
    pipWindow.document.body.style.justifyContent = 'center'
    pipWindow.document.body.style.alignItems = 'center'
    pipWindow.document.body.style.background = '#f2f1ed'

    const container = pipWindow.document.createElement('div')
    container.id = 'pip-vue-root'
    pipWindow.document.body.appendChild(container)

    const vnode = h(QrCodePip, {
        title: courseName.value,
        dateRange: courseDateText.value
    })

    if (currentInstance?.appContext) {
        vnode.appContext = currentInstance.appContext
    }

    render(vnode, container)

    pipWindow.addEventListener('pagehide', () => {
        render(null, container)
    })
}

const dialogStateMap = {
    absent: showStudentActionAddDialog,
    present: showStudentActionDismissDialog,
    excused: showStudentActionUpdateDialog,
};

const performStudentAction = (participant) => {
    const dialogRef = dialogStateMap[participant.status];
    
    if (dialogRef) {
        selectedStudent.value = participant;
        dialogRef.value = true;
    }
}

onMounted(async () => {
    await contextStore.loadNextSession();
    isLoading.value = false;
    STUDIP.JSUpdater.register('attendance_widget_teacher', function (data) {
        if (data['update-next-session']) {
            contextStore.loadNextSession();
        }
        if (data['update-entries']) {
            const newEntries = data['updated-entries'];
            newEntries.forEach((e) => {
                entriesStore.storeRecord(e);
            });
        }
    }, function () {
        return {
            'course-date': contextStore.nextSessionDate,
            'known-entries': sessionEntries.value.map(e => ({
                id: e.id,
                chdate: e.chdate
            })),
            'active-session-id': sessionStore.activeSessionId,
        }
    });
});

onBeforeUnmount(() => {
    STUDIP.JSUpdater.unregister('attendance_widget_teacher');
});

watch(
    [showStudentActionAddDialog, showStudentActionDismissDialog, showStudentActionUpdateDialog],
    ([addOpen, dismissOpen, updateOpen]) => {
        const isAnyDialogOpen = addOpen || dismissOpen || updateOpen;
        
        if (!isAnyDialogOpen) {
            selectedStudent.value = null;
        }
    }
);
</script>

<style lang="scss" scoped>
.attendance-widget-wrapper-idle {
    padding: 0 1rem;
}

.attendance-widget-teacher-wrapper {
    background-color: var(--dark-gray-color-5);
    padding: 16px;

    .attendance-widget-teacher-header {
        margin-bottom: 1rem;
    
        .attendance-widget-teacher-header-title {
            margin: 0 0 0.5rem 0;
        }
    }
}

.info-banner {
    background-color: #dbeafe;
    color: #1e40af;
    padding: 10px 14px;
    border-radius: 6px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
}

.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    padding-top: 16px;
    border-top: solid thin #ccc;
}

.action-title {
    font-size: 14px;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.kpi-row {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 12px;
}

.student-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 12px;
    max-height: calc(2 * 190px + 12px);
    overflow-y: auto;
    padding-right: 16px;
}
</style>