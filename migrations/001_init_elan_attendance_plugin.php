<?php
/**
 * InitElanAttendancePlugin
 *
 * Migration step to creates Attendance plugin DB Tables, cronjobs and configs.
 *
 * @package   StudipAttendance
 * @since     0.1.0
 * @author    Farbod Zamnai <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 */
final class InitElanAttendancePlugin extends Migration
{
    const PLUGIN_CRONJOBS_DIR_PATH = 'public/plugins_packages/elan-ev/ElanAttendancePlugin/cronjobs';
    // TODO: how often should the cronjobs be processed?
    const CRONJOBS = [
        'attendance_sessions_sync.php' => [59,1,null,null],
        'attendance_view_presets_process.php' => [59,1,null,null],
        'attendance_report_process.php' => [59,1,null,null],
    ];

    const PRE_BEGIN_BUFFER_MINUTES = 5;
    const POST_BEGIN_BUFFER_MINUTES = 5;
    const TOTP_TIMEWINDOW_SECONDS = 30;

    public function description()
    {
        return 'Creates database tables for StudipAttendance plugin.';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE IF NOT EXISTS `elan_attendance_sessions` (
                `id`            INT(11) NOT NULL AUTO_INCREMENT,
                `termin_id`     VARCHAR(32) NOT NULL,
                `seminar_id`    VARCHAR(32) NOT NULL,
                `status`        ENUM('draft','active','ended','deleted') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `qr_seed`       VARCHAR(255) DEFAULT NULL,
                `mkdate`        INT(11) UNSIGNED NOT NULL,
                `chdate`        INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_seminar_termin` (`seminar_id`,`termin_id`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `elan_attendance_entries` (
                `id`                        INT(11) NOT NULL AUTO_INCREMENT,
                `attendance_session_id`     INT(11) NOT NULL,
                `user_id`                   VARCHAR(32) NOT NULL,
                `status`                    ENUM('present','absent','excused') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `source`                    ENUM('user_qr','user_code','user_ui','teacher','admin','system') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `comment`                   TEXT DEFAULT NULL,
                `teacher_input_reason`      VARCHAR(64) DEFAULT NULL,
                `late`                      SMALLINT(6) NOT NULL DEFAULT 0,
                `left_early`                SMALLINT(6) NOT NULL DEFAULT 0,
                `mkdate`                    INT(11) UNSIGNED NOT NULL,
                `chdate`                    INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_user_session` (`user_id`,`attendance_session_id`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `elan_attendance_audit_log` (
                `id`            INT(11) NOT NULL AUTO_INCREMENT,
                `object_type`   VARCHAR(64) NOT NULL,
                `object_id`     INT(11) NOT NULL,
                `user_id`       VARCHAR(32) NOT NULL,
                `action`        VARCHAR(64) NOT NULL,
                `payload`       MEDIUMTEXT DEFAULT NULL,
                `comment`       TEXT DEFAULT NULL,
                `mkdate`        INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `elan_attendance_view_presets` (
                `id`                        INT(11) NOT NULL AUTO_INCREMENT,
                `user_id`                   VARCHAR(32) NOT NULL,
                `title`                     VARCHAR(255) NOT NULL,
                `filter_config`             MEDIUMTEXT NOT NULL,
                `cached_result`             MEDIUMTEXT DEFAULT NULL,
                `last_calculated`           INT(11) DEFAULT NULL,
                `last_execution_time_ms`    INT(11) DEFAULT NULL,
                `is_shared`                 TINYINT(1) NOT NULL DEFAULT 0,
                `mkdate`                    INT(11) UNSIGNED NOT NULL,
                `chdate`                    INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `elan_attendance_reports` (
                `id`                        INT(11) NOT NULL AUTO_INCREMENT,
                `title`                     VARCHAR(255) NOT NULL,
                `filter_config`             MEDIUMTEXT NOT NULL,
                `cached_result`             MEDIUMTEXT DEFAULT NULL,
                `last_calculated`           INT(11) DEFAULT NULL,
                `last_execution_time_ms`    INT(11) DEFAULT NULL,
                `mkdate`                    INT(11) UNSIGNED NOT NULL,
                `chdate`                    INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`)
            )"
        );

        $db->exec("CREATE TABLE IF NOT EXISTS `elan_attendance_thresholds` (
                `id`                            INT(11) NOT NULL AUTO_INCREMENT,
                `seminar_id`                    VARCHAR(32) NOT NULL,
                `unexcused_warning_percent`     SMALLINT(6) NOT NULL DEFAULT 0,
                `unexcused_critical_percent`    SMALLINT(6) NOT NULL DEFAULT 0,
                `absence_days_warning`          SMALLINT(6) NOT NULL DEFAULT 0,
                `absence_days_critical`         SMALLINT(6) NOT NULL DEFAULT 0,
                `absence_weeks_warning`         SMALLINT(6) NOT NULL DEFAULT 0,
                `absence_weeks_critical`        SMALLINT(6) NOT NULL DEFAULT 0,
                `mkdate`                        INT(11) UNSIGNED NOT NULL,
                `chdate`                        INT(11) UNSIGNED NOT NULL,

                PRIMARY KEY (`id`)
            )"
        );

        // Add cronjobs.
        $scheduler = CronjobScheduler::getInstance();
        foreach (self::CRONJOBS as $cronjobFilename => $period) {
            $cronjobPath =  self::PLUGIN_CRONJOBS_DIR_PATH . '/' . $cronjobFilename;
            if (file_exists($GLOBALS['STUDIP_BASE_PATH'] . '/' . $cronjobPath)) {
                $task_id = $scheduler->registerTask($cronjobPath, true);

                if ($task_id && !empty($period)) {
                    [$minute, $hour, $day, $month] = $period;
                    $scheduler->schedulePeriodic($task_id, $minute, $hour, $day, $month);
                }
            }
        }

        // Add global configs.
        Config::get()->create('ATTENDANCE_PRE_BEGIN_BUFFER', [
            'value' => self::PRE_BEGIN_BUFFER_MINUTES,
            'type' => 'integer',
            'range' => 'global',
            'section' => 'ElanAttendance',
            'description' => _('Die Vorlaufzeit vor Sitzungsbeginn in Minuten.')
        ]);

        Config::get()->create('ATTENDANCE_POST_END_BUFFER', [
            'value' => self::POST_BEGIN_BUFFER_MINUTES,
            'type' => 'integer',
            'range' => 'global',
            'section' => 'ElanAttendance',
            'description' => _('Die Nachlaufzeit nach Sitzungsende in Minuten.')
        ]);

        Config::get()->create('ATTENDANCE_TOTP_TIMEWINDOW', [
            'value' => self::TOTP_TIMEWINDOW_SECONDS,
            'type' => 'integer',
            'range' => 'global',
            'section' => 'ElanAttendance',
            'description' => _('Das Zeitfenster, in dem der TOTP gültig bleibt, in Sekunden.')
        ]);
    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE IF EXISTS `elan_attendance_sessions`");
        $db->exec("DROP TABLE IF EXISTS `elan_attendance_entries`");
        $db->exec("DROP TABLE IF EXISTS `elan_attendance_audit_log`");
        $db->exec("DROP TABLE IF EXISTS `elan_attendance_view_presets`");
        $db->exec("DROP TABLE IF EXISTS `elan_attendance_reports`");
        $db->exec("DROP TABLE IF EXISTS `elan_attendance_thresholds`");

        // Remove cronjobs.
        $scheduler = CronjobScheduler::getInstance();
        foreach (array_keys(self::CRONJOBS) as $cronjobFilename) {
            $cronjobPath =  self::PLUGIN_CRONJOBS_DIR_PATH . '/' . $cronjobFilename;
            if (file_exists($GLOBALS['STUDIP_BASE_PATH'] . '/' . $cronjobPath)) {
                $task_id = CronjobTask::findOneByFilename($cronjobPath)->task_id;
                $scheduler->unregisterTask($task_id);
            }
        }

        // Remove Configs.
        Config::get()->delete('ATTENDANCE_PRE_BEGIN_BUFFER');
        Config::get()->delete('ATTENDANCE_POST_END_BUFFER');
        Config::get()->delete('ATTENDANCE_TOTP_TIMEWINDOW');
    }
}
