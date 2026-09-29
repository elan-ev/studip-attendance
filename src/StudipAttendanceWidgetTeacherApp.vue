<template>
    <div class="attendance-widget-teacher-wrapper">
        <header>
            <h3>{{ courseName }}</h3>
        </header>
        <div class="kpi-row">
            <KPICard :value="kpiStudentsPresent + ' / ' + students.length" :label="$gettext('Anwesend')" :active="true" />
            <KPICard :value="kpiStudentsAbsent" :label="$gettext('Fehlt')" />
            <KPICard :value="kpiStudentsExcused" :label="$gettext('Entschuldigt')" />
        </div>

        <h4 class="section-title">{{ $gettext('Teilnehmende') }}</h4>

        <div class="student-grid">
            <StudentCard v-for="student in students" :key="student.id" :student="student" />
        </div>

        <div class="action-bar">
            <span class="action-title">{{ $gettext('QR-Code Anzeigen') }}</span>
            <div class="action-buttons">
                <button class="btn-secondary" @click="openQrPiP">{{ $gettext('Picture-in-Picture-Modus') }}</button>
                <button class="btn-primary" @click="openFullscreen">{{ $gettext('Vollbildansicht') }}</button>
            </div>
        </div>

        <QrCodeFullscreen ref="fullscreenRef" :title="courseName" />
    </div>
</template>

<script setup>
import { ref, render, h, getCurrentInstance, onMounted, computed } from 'vue'
import KPICard from './components/widget/KPICard.vue'
import StudentCard from './components/widget/StudentCard.vue'
import QrCodePip from './components/widget/QrCodePip.vue'
import QrCodeFullscreen from './components/widget/QrCodeFullscreen.vue'

import { useAttendanceSessionStore } from './store/session.js'
import { useContextStore } from './store/context.js'
import { useStudentStore } from './store/students.js';
import { useEntryStore } from './store/entries.js'

const sessionStore = useAttendanceSessionStore()
const contextStore = useContextStore();
const studentsStore = useStudentStore();
const entriesStore = useEntryStore();
const currentInstance = getCurrentInstance()

const fullscreenRef = ref(null)

const courseName = computed(() => {
    return contextStore.nextSessionCourse?.name ?? '';
})

const students = computed(() => {
    const seminarId = contextStore.nextSessionCourse?.seminar_id;
    if (!seminarId) {
        return [];
    }

    const sessionId = sessionStore.activeSessionId;

    const rawStudents = studentsStore.byCourseId(seminarId) || [];
    const sessionEntries = sessionId ? (entriesStore.bySessionId(sessionId) || []) : [];

    const entryByUserId = new Map(
        sessionEntries.map(entry => [entry.user_id, entry])
    );

    return rawStudents.map(student => {
        const record = entryByUserId.get(student.user_id);

        return {
            id: student.id,
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
        title: courseName.value
    })

    if (currentInstance?.appContext) {
        vnode.appContext = currentInstance.appContext
    }

    render(vnode, container)

    pipWindow.addEventListener('pagehide', () => {
        render(null, container)
    })
}

onMounted(async () => {
    await contextStore.loadNextSession();
})
</script>

<style scoped>
.attendance-widget-teacher-wrapper {
    background-color: #f0f7ff;
    padding: 16px;
    border-radius: 8px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
    color: #334155;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-secondary,
.btn-primary {
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    border: 1px solid transparent;
}

.btn-secondary {
    background-color: #ffffff;
    color: #1e293b;
    border-color: #cbd5e1;
}

.btn-primary {
    background-color: #1e40af;
    color: #ffffff;
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