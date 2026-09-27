<?php
/**
 * Get all Informations for next Session
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.1.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes\Session;

use JsonApi\NonJsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\Models\AttendanceSession;


class NextSession extends NonJsonApiController
{
    private const DEFAULT_LIMIT = 100;

    /**
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $nextCourseDate = $this->getNextTerminIdForUser($user->id);
        $payload = [];
        if ($nextCourseDate) {
            $session = AttendanceSession::findOneByTermin_id($nextCourseDate->termin_id);
            $course = $session ? $session->course : null;

            $studentsData = $course ? $this->getStudentsForCourse($course, self::DEFAULT_LIMIT) : ['data' => [], 'total' => 0, 'has_more' => false];
            $entriesData = $session ? $this->getEntriesForSession($session, self::DEFAULT_LIMIT) : ['data' => [], 'total' => 0, 'has_more' => false];

            $payload = [
                'course-date' => $nextCourseDate->toArray(),
                'session' => $session ? $session->toArray() : null,
                'course' => $course ? $course->toArray() : null,
                'students' => $studentsData['data'],
                'entries' => $entriesData['data'],
                'meta' => [
                    'limit' => self::DEFAULT_LIMIT,
                    'students' => [
                        'total' => $studentsData['total'],
                        'has_more' => $studentsData['has_more'],
                    ],
                    'entries' => [
                        'total' => $entriesData['total'],
                        'has_more' => $entriesData['has_more'],
                    ],
                ],
            ];
        }

        $response = $response->withHeader('Content-Type', 'application/vnd.api+json');
        $response->getBody()->write((string) json_encode($payload));

        return $response;
    }

    /**
     * Holt die Studierenden (Status 'autor') als User-Array für das Frontend inkl. Meta-Informationen
     */
    private function getStudentsForCourse(\Course $course, int $limit = self::DEFAULT_LIMIT): array
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
                $students[] = $member->toArray();
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
    private function getEntriesForSession(AttendanceSession $session, int $limit = self::DEFAULT_LIMIT): array
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

    private function getNextTerminIdForUser(string $userId, int $windowMinutes = 30): ?\CourseDate
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
}