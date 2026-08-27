<?php

namespace StudipAttendance\Models;

use SimpleORMap;
use Seminar;

/**
 * Attendance Threshold Model.
 *
 * @package   StudipAttendance\Models
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $id
 * @property string $seminar_id
 * @property int $unexcused_warning_percent
 * @property int $unexcused_critical_percent
 * @property int $absence_days_warning
 * @property int $absence_days_critical
 * @property int $absence_weeks_warning
 * @property int $absence_weeks_critical
 *
 * @property Seminar $course
 */

class AttendanceThreshold extends SimpleORMap
{
    const THRESHOLD_LEVEL_CRITICAL = 'critical';
    const THRESHOLD_LEVEL_WARNING = 'warning';
    const THRESHOLD_LEVEL_NORMAL = 'normal';
    protected static function configure($config = [])
    {
        $config['db_table'] = 'elan_attendance_thresholds';

        $config['belongs_to']['course'] = [
            'class_name'  => Seminar::class,
            'foreign_key' => 'seminar_id',
            'on_delete' => 'delete',
        ];

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }

    public static function findBySeminar_id(string $courseId): self
    {
        return self::findOneBySQL('seminar_id = ?', [$courseId]);
    }
}
