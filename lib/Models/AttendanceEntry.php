<?php

namespace StudipAttendance\Models;

use SimpleORMap;
use User;

/**
 * Attendance Session Entry Model.
 *
 * @package   StudipAttendance\Models
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $id
 * @property int $attendance_session_id
 * @property string $user_id
 * @property string $status
 * @property string $source
 * @property string $comment
 * @property string $teacher_input_reason
 * @property int $late
 * @property int $left_early
 *
 * @property User $user
 * @property AttendanceSession $session
 */

class AttendanceEntry extends SimpleORMap
{
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_EXCUSED = 'excused';

    const STATUSES = [
        self::STATUS_PRESENT,
        self::STATUS_ABSENT,
        self::STATUS_EXCUSED,
    ];

    const SOURCE_USER_QR = 'user_qr';
    const SOURCE_USER_CODE = 'user_code';
    const SOURCE_USER_UI = 'user_ui';
    const SOURCE_TEACHER = 'teacher';
    const SOURCE_ADMIN = 'admin';
    const SOURCE_SYSTEM = 'system';

    const SOURCES = [
        self::SOURCE_USER_QR,
        self::SOURCE_USER_CODE,
        self::SOURCE_USER_UI,
        self::SOURCE_TEACHER,
        self::SOURCE_ADMIN,
        self::SOURCE_SYSTEM,
    ];

    protected static function configure($config = [])
    {
        $config['db_table'] = 'elan_attendance_entries';

        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
            'on_delete' => 'delete',
        ];

        $config['belongs_to']['session'] = [
            'class_name'  => AttendanceSession::class,
            'foreign_key' => 'attendance_session_id',
            'on_delete' => 'delete',
        ];

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }

    public static function userHasEntryIn(int $sessionId, string $userId): bool
    {
        return self::countBySql('attendance_session_id = ? AND user_id = ?', [$sessionId, $userId]) === 1;
    }
}
