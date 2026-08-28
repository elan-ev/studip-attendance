<?php
/**
 * Helper class for providing everything regarding sessions
 *
 * @package   StudipAttendance\Helpers
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Helpers;

use CourseDate;
use CourseExDate;
use Config;
use Seminar;
use StudipAttendance\Models\AttendanceAuditLog;
use StudipAttendance\Models\AttendanceEntry;
use StudipAttendance\Models\AttendanceSession;

class SessionHandler
{
    const DEFAULT_PRE_BEGIN_BUFFER_MINUTES = 5;
    const DEFAULT_POST_END_BUFFER_MINUTES = 5;

    const VALIDATION_SUCCEED = 1;
    const VALIDATION_FAILED_SESSION = 2;
    const VALIDATION_FAILED_PARTICIPANT = 3;
    const VALIDATION_FAILED_TIMEFRAME = 4;
    const VALIDATION_FAILED_TOTP = 5;
    const VALIDATION_FAILED_ENTRY = 6;

    public static function discoverNonRecordedCourseDates(): array
    {
        $params = [];
        $wheres = [];

        $excludedTerminIds = \SimpleCollection::createFromArray(AttendanceSession::getAll())->pluck('termin_id');
        if (!empty($excludedTerminIds)) {
            $wheres[] = 'termin_id NOT IN ( :excluded_termin_ids )';
            $params['excluded_termin_ids'] = $excludedTerminIds;
        }
        // TODO: check whether we should apply any time span?
        $wheres[] = 'date > :today_midnight';
        $params['today_midnight'] = (new \DateTimeImmutable('today'))->getTimestamp();

        $sql = implode(' AND ', $wheres);
        return CourseDate::findBySQL($sql, $params);
    }

    public static function determineStatus(CourseDate $courseDate): string
    {
        $now = time();

        if ((int) $courseDate->date > $now) {
            return AttendanceSession::STATUS_DRAFT;
        }

        if ($now >= (int) $courseDate->date && $now <= (int) $courseDate->end_time) {
            return AttendanceSession::STATUS_ACTIVE;
        }

        return AttendanceSession::STATUS_ENDED;
    }

    public static function ensureSessionExistsFrom(CourseDate $courseDate): void
    {
        if (!AttendanceSession::isRecorded($courseDate->termin_id)) {
            $session = new AttendanceSession();
            $session->termin_id = $courseDate->termin_id;
            $session->seminar_id = $courseDate->range_id;
            $session->status = self::determineStatus($courseDate);
            $session->store();
        }
    }

    public static function handleCourseDateDeletion(string $terminId): void
    {
        if (AttendanceSession::isRecorded($terminId)) {
            $session = AttendanceSession::findOneByTermin_id($terminId);
            $session->status = AttendanceSession::STATUS_DELETED;
            $session->store();
        }
    }

    public static function handleCourseChangedSchedule(Seminar $course): void
    {
        // We don't rely on the existing class offered by SeminarDB or Seminar or singleDate,
        // because they are deprecated and will be removed in StudIP 6.x.
        // In fact, this "CourseChangedSchedule" event will also be out in 6.x.
        // We extract the dates the right way using only the course id!

        $cancelledDates = CourseExDate::findBySQL('range_id = ?', [$course->getId()]);
        if (!empty($cancelledDates)) {
            foreach ($cancelledDates as $cDate) {
                self::handleCourseDateDeletion($cDate->termin_id);
            }
        }

        $currentDates = CourseDate::findBySQL('range_id = ?', [$course->getId()]);
        if (!empty($currentDates)) {
            foreach ($currentDates as $date) {
                self::ensureSessionExistsFrom($date);
            }
        }
    }

    public static function validateCheckin(int $sessionId, string $userId, int $token): int
    {
        $session = AttendanceSession::find($sessionId);
        if (!$session || $session->status !== AttendanceSession::STATUS_ACTIVE) {
            return self::VALIDATION_FAILED_SESSION;
        }

        if (!self::isUserParticipant($session->course->id, $userId)) {
            return self::VALIDATION_FAILED_PARTICIPANT;
        }

        if (!self::isValidTimeFrame($session->termin)) {
            return self::VALIDATION_FAILED_TIMEFRAME;
        }

        if (!TOTPHandler::verifyTOTP($token, $session->qr_seed)) {
            return self::VALIDATION_FAILED_TOTP;
        }

        if (AttendanceEntry::userHasEntryIn($sessionId, $userId)) {
            return self::VALIDATION_FAILED_ENTRY;
        }

        return self::VALIDATION_SUCCEED;
    }

    public static function calculateLatency(AttendanceSession $session, int $recordingTime): int
    {
        if ($recordingTime > (int) $session->termin->date) {
            return $recordingTime - (int) $session->termin->date;
        }

        return 0;
    }

    private static function isUserParticipant(string $courseId, string $userId): bool
    {
        return $GLOBALS['perm']->have_studip_perm('autor', $courseId, $userId);
    }

    private static function isValidTimeFrame(CourseDate $courseDate): bool
    {
        $now = time();
        $startWithBuffer = (int) $courseDate->date - self::getPreBeginBuffer();
        $endWithBuffer = (int) $courseDate->end_time - self::getPostEndBuffer();
        return $now >= $startWithBuffer && $now <= $endWithBuffer;
    }

    private static function getPreBeginBuffer(bool $seconds = true): int
    {
        $preBeginBufferMinutes = (int) Config::get()->ATTENDANCE_PRE_BEGIN_BUFFER ?? self::DEFAULT_PRE_BEGIN_BUFFER_MINUTES;

        return $seconds ? $preBeginBufferMinutes * 60 : $preBeginBufferMinutes;
    }

    private static function getPostEndBuffer(bool $seconds = true): int
    {
        $preBeginBufferMinutes = (int) Config::get()->ATTENDANCE_POST_END_BUFFER ?? self::DEFAULT_POST_END_BUFFER_MINUTES;

        return $seconds ? $preBeginBufferMinutes * 60 : $preBeginBufferMinutes;
    }
}
