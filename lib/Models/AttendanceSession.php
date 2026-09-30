<?php

namespace StudipAttendance\Models;

use SimpleORMap;
use CourseDate;
use Course;

use StudipAttendance\Classes\AuditLogTrait;
use StudipAttendance\Classes\AuditLogInterface;

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
 * @property string  $termin_id
 * @property string  $seminar_id
 * @property string  $status
 * @property string  $qr_seed
 * @property boolean $qr_shown
 *
 * @property Course $course
 * @property CourseDate $termin
 */

class AttendanceSession extends SimpleORMap implements AuditLogInterface
{
    use AuditLogTrait;

    const QR_SEED_LENGTH = 6;
    const STATUS_IDLE = 'idle';
    const STATUS_CANCELED = 'canceled';
    const STATUS_DELETED = 'deleted';

    const STATUSES = [
        self::STATUS_IDLE,
        self::STATUS_CANCELED,
        self::STATUS_DELETED,
    ];

    protected static function configure($config = [])
    {
        $config['db_table'] = 'elan_attendance_sessions';

        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'seminar_id',
            'on_delete' => 'delete',
        ];

        $config['belongs_to']['termin'] = [
            'class_name'  => CourseDate::class,
            'foreign_key' => 'termin_id',
            'on_delete' => function (AttendanceSession $session): void {
                $session->status = self::STATUS_DELETED;
                $session->store();
            },
        ];

        $config['has_many']['entries'] = [
            'class_name'        => AttendanceEntry::class,
            'assoc_foreign_key' => 'attendance_session_id',
            // 'on_delete'         => 'delete', //TODO: Should we remove them as well?
        ];

        $config['registered_callbacks']['before_create'][] = 'cbGenerateQrSeed';

        // Using AuditLogTrait methods for the callbacks!
        $config['registered_callbacks']['after_update'][] = 'cbLogAfterUpdate';
        $config['registered_callbacks']['after_delete'][] = 'cbLogBeforeDelete';

        parent::configure($config);
    }

    /**
     * @inheritdoc
     */
    public function getLogPayload(): array
    {
        return $this->toArray('status');
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }

    public static function findBySeminar_id(string $courseId): array
    {
        return self::findBySQL('seminar_id = ?', [$courseId]);
    }

    public static function isRecorded(string $terminId): bool
    {
        return self::countBySql('termin_id = ?', [$terminId]) === 1;
    }

    public static function findOneByTermin_id(string $terminId): self
    {
        return self::findOneBySQL('termin_id = ?', [$terminId]);
    }

    protected function cbGenerateQrSeed(): void
    {
        $bytes = random_bytes(self::QR_SEED_LENGTH);
        $this->qr_seed = bin2hex($bytes);
    }
}
