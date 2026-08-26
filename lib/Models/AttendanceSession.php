<?php

namespace StudipAttendance\Models;

use SimpleORMap;
use CourseDate;
use Seminar;

/**
 * Attendance Session Model.
 *
 * @package   StudipAttendance\Models
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $id
 * @property string $termin_id
 * @property string $seminar_id
 * @property string $status
 * @property string $qr_seed
 *
 * @property Seminar $course
 * @property CourseDate $termin
 */

class AttendanceSession extends SimpleORMap
{
    const QR_SEED_LENGTH = 5;
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_ENDED = 'ended';
    const STATUS_DELETED = 'deleted';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_ENDED,
        self::STATUS_DELETED,
    ];

    protected static function configure($config = [])
    {
        $config['db_table'] = 'elan_attendance_sessions';

        $config['belongs_to']['course'] = [
            'class_name'  => Seminar::class,
            'foreign_key' => 'seminar_id',
            'on_delete' => 'delete',
        ];

        $config['belongs_to']['termin'] = [
            'class_name'  => CourseDate::class,
            'foreign_key' => 'termin_id',
            'on_delete' => function (AttendanceSession $session): void {
                $session->status = self::STATUS_DELETED;
                $session->store(); //TODO: Check if the store works properly here?
            },
        ];

        $config['has_many']['entries'] = [
            'class_name'        => AttendanceEntry::class,
            'assoc_foreign_key' => 'attendance_session_id',
            'on_delete'         => 'delete', //TODO: Should we remove them as well?
        ];

        $config['registered_callbacks']['after_create'][] = 'cbGenerateQrSeed';

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }

    public static function getAllCourseSessions(string $courseId): array
    {
        return self::findBySQL('seminar_id = ?', [$courseId]);
    }

    public static function isRecorded(string $terminId): bool
    {
        return self::countBySql('termin_id = ?', [$terminId]) === 1;
    }

    protected function cbGenerateQrSeed(): void
    {
        $bytes = random_bytes(self::QR_SEED_LENGTH);
        $this->qr_seed = $this->id . bin2hex($bytes);
    }
}
