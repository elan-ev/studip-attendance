<?php

namespace StudipAttendance\Helpers;

/** Registers jobs using the installed plugin path, including symlinked installs. */
class CronjobRegistration
{
    const JOBS = [
        'AttendanceSessionsSync' => ['attendance_sessions_sync.php', [-5, null, null, null]],
        'AttendanceViewPresetsProcess' => ['attendance_view_presets_process.php', [59, 1, null, null]],
        'AttendanceReportProcess' => ['attendance_report_process.php', [59, 1, null, null]],
    ];

    public static function register(): void
    {
        $info = \PluginManager::getInstance()->getPluginInfo('ElanAttendancePlugin');
        // Stud.IP runs migrations before registering a newly uploaded plugin.
        $manifest = parse_ini_file(__DIR__ . '/../../plugin.manifest');
        $pluginPath = $info['path'] ?? $manifest['origin'] . '/' . $manifest['pluginclassname'];
        $directory = rtrim(\Config::get()->PLUGINS_PATH, '/') . '/' . $pluginPath . '/cronjobs';
        $scheduler = \CronjobScheduler::getInstance();

        foreach (self::JOBS as $class => [$filename, $period]) {
            $path = studip_relative_path($directory . '/' . $filename);
            if (!is_file($GLOBALS['STUDIP_BASE_PATH'] . '/' . $path)) {
                throw new \RuntimeException('Attendance cronjob file is missing: ' . $path);
            }

            $task = \CronjobTask::findOneByClass($class);
            if ($task) {
                // Preserve administrator settings, but repair paths after a move.
                if ($task->filename !== $path) {
                    $task->filename = $path;
                    $task->store();
                }
                $taskId = $task->task_id;
            } else {
                // Register an object: registerTask(path) cannot rediscover a class
                // already loaded earlier in the same migration request.
                require_once $directory . '/' . $filename;
                $taskId = $scheduler->registerTask(new $class(), true);
                $task = \CronjobTask::find($taskId);
                $task->filename = $path;
                $task->store();
            }

            if (!\CronjobSchedule::findByTask_id($taskId)) {
                $scheduler->schedulePeriodic($taskId, ...$period);
            }
        }
    }

    public static function unregister(): void
    {
        foreach (array_keys(self::JOBS) as $class) {
            $task = \CronjobTask::findOneByClass($class);
            if ($task) {
                \CronjobScheduler::getInstance()->unregisterTask($task->task_id);
            }
        }
    }
}
