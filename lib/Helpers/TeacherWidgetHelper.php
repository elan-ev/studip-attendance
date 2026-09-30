<?php
/**
 * Helper class for providing everything regarding sessions
 *
 * @package   StudipAttendance\Helpers
 * @since     0.1.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Helpers;

use StudipAttendance\Models\AttendanceSession;

class TeacherWidgetHelper
{
    public const DEFAULT_LIMIT = 200;

    /**
     * Holt die Studierenden (Status 'autor') als User-Array für das Frontend inkl. Meta-Informationen
     */
    public static function getStudentsForCourse(\Course $course, int $limit = self::DEFAULT_LIMIT): array
    {
        $studentMembers = $course->members->findBy('status', 'autor');
        $total = count($studentMembers);

        $students = [];
        $count = 0;
        foreach ($studentMembers as $member) {
            if ($count >= $limit) {
                break;
            }
            if ($member->user) {
                $userData = $member->toArray();
                $userData['formatted_name'] = $member->user->getFullname();
                $userData['avatar'] = \Avatar::getAvatar($member->user_id)->getURL(\Avatar::NORMAL);
                $students[] = $userData;
                $count++;
            }
        }

        return [
            'data' => $students,
            'total' => $total,
            'has_more' => $total > $limit,
        ];
    }

    /**
     * Liest die bereits erfassten Attendance-Entries der Session aus inkl. Meta-Informationen
     */
    public static function getEntriesForSession(AttendanceSession $session, int $limit = self::DEFAULT_LIMIT): array
    {
        if (!$session->entries) {
            return [
                'data' => [],
                'total' => 0,
                'has_more' => false,
            ];
        }

        $allEntries = $session->entries;
        $total = count($allEntries);

        // Schneidet das Array auf das Limit zu
        $entries = array_slice($allEntries->toArray(), 0, $limit);

        return [
            'data' => $entries,
            'total' => $total,
            'has_more' => $total > $limit,
        ];
    }

    public static function getNextTerminIdForUser(string $userId, int $windowMinutes = 30): ?\CourseDate
    {
        $db = \DBManager::get();

        $now = time();
        $maxStartTime = $now + ($windowMinutes * 60);

        $sql = "SELECT t.termin_id
            FROM seminar_user su
            JOIN termine t ON t.range_id = su.Seminar_id
            WHERE su.user_id = :user_id
              AND su.status = 'dozent'
              AND t.end_time >= :now   
              AND t.date <= :max_start_time
            ORDER BY t.date ASC
            LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'now' => $now,
            'max_start_time' => $maxStartTime,
        ]);

        $terminId = $stmt->fetchColumn();

        return \CourseDate::find($terminId ?: null);
    }

    public static function getUpdatedEntriesForSession(AttendanceSession $session, array $knownEntries = []): array
    {
        if (!$session->entries) {
            return [];
        }

        $knownMap = [];
        foreach ($knownEntries as $entry) {
            $knownMap[$entry['id']] = $entry['chdate'] ?? null;
        }

        $updatedEntries = [];

        foreach ($session->entries as $entry) {
            $entryId = $entry->id;
            $currentChdate = $entry->chdate;

            $isNew = !isset($knownMap[$entryId]);
            $isChanged = isset($knownMap[$entryId]) && $knownMap[$entryId] != $currentChdate;

            if ($isNew || $isChanged) {
                $updatedEntries[] = $entry->toArray();
            }
        }

        return $updatedEntries;
    }
}