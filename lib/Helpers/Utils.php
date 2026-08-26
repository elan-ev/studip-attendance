<?php
/**
 * Utility Helper Class
 *
 * @package   StudipAttendance\Helpers
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Helpers;

use Exception;
use User;
use StudipAttendance\Models\AttendanceEntry;
use StudipAttendance\Models\AttendanceSession;

class Utils
{
    public static $currentProcesses = [];
    public static function startProcess(int $recordId): void
    {
        $currentProcesses[$recordId]['start'] = time();
    }

    public static function endProcess(int $recordId): void
    {
        $end = time();
        if (
            !isset($currentProcesses[$recordId]['start']) ||
            $currentProcesses[$recordId]['start'] > $end
        ) {
            throw new Exception("Invalid process start timestamp");
        }
        $start = (int) $currentProcesses[$recordId]['start'];
        $duration = $end - $start;

        $currentProcesses[$recordId]['end'] = $end;
        $currentProcesses[$recordId]['duration'] = $duration;
    }

    public static function getEnd(int $recordId): int
    {
        if (!isset($currentProcesses[$recordId]['end'])) {
            throw new Exception("No end timestamp recorded");
        }

        return $currentProcesses[$recordId]['end'];
    }

    public static function getStart(int $recordId): int
    {
        if (!isset($currentProcesses[$recordId]['start'])) {
            throw new Exception("No start timestamp recorded");
        }

        return $currentProcesses[$recordId]['start'];
    }

    public static function getDuration(int $recordId): int
    {
        if (!isset($currentProcesses[$recordId]['duration'])) {
            throw new Exception("No duration timestamp recorded");
        }

        return $currentProcesses[$recordId]['duration'];
    }

    public static function sendFeeback(AttendanceSession $session, AttendanceEntry $entry, string $userId): bool
    {
        // TODO: implement the feedback notification.
        return true;
    }

    

    public static function isTeacher(User $user, string $courseId): bool
    {
        return $GLOBALS['perm']->have_studip_perm('dozent', $courseId, $user->id);
    }

    public static function isTeacherOrAdmin(User $user, string $courseId): bool
    {
        if (self::isRoot($user) || self::isCourseAdmin($user, $courseId)) {
            return true;
        }
        return self::isTeacher($user, $courseId);
    }

    public static function isRoot(User $user): bool
    {
        return $GLOBALS['perm']->have_perm('root', $user->id) || $GLOBALS['perm']->have_perm('admin', $user->id);
    }

    public static function isCourseAdmin(User $user, string $courseId): bool
    {
        return $GLOBALS['perm']->have_studip_perm('admin', $courseId, $user->id);
    }

    public static function isAutor(User $user, string $courseId): bool
    {
        return $GLOBALS['perm']->have_studip_perm('autor', $courseId, $user->id);
    }
}
