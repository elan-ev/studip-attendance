<?php
/**
 * InitElanAttendanceTables
 *
 * Migration step to creates InitAttendanceTables DB Tables.
 *
 * @package   InitElanAttendanceTables
 * @since     0.1.0
 * @author    Farbod Zamnai <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 */
final class InitElanAttendanceTables extends Migration
{
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
                `status`        ENUM('draft','active','closed') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
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

                PRIMARY KEY (`id`)
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
    }
}
