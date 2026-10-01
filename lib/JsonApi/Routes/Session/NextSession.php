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

use StudipAttendance\Helpers\TeacherWidgetHelper;
use StudipAttendance\Models\AttendanceSession;
use StudipAttendance\Models\AttendanceEntry;



class NextSession extends NonJsonApiController
{

    /**
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $nextCourseDateForTeacher = TeacherWidgetHelper::getNextTerminIdForTeacher($user->id);
        $nextCourseDateForStudent = TeacherWidgetHelper::getNextTerminIdForStudent($user->id);
        $payload = [];
        if ($nextCourseDateForTeacher) {
            $session = AttendanceSession::findOneByTermin_id($nextCourseDateForTeacher->termin_id);
            $course = $session ? $session->course : null;

            $studentsData = $course ? TeacherWidgetHelper::getStudentsForCourse($course) : ['data' => [], 'total' => 0, 'has_more' => false];
            $entriesData = $session ? TeacherWidgetHelper::getEntriesForSession($session) : ['data' => [], 'total' => 0, 'has_more' => false];

            $payload = [
                'user-status'        => 'teacher',
                'course-date'   => $nextCourseDateForTeacher->toArray(),
                'session'       => $session ? $session->toArray() : null,
                'course'        => $course ? $course->toArray() : null,
                'students'      => $studentsData['data'],
                'entries'       => $entriesData['data'],
                'meta'          => [
                    'limit'         => TeacherWidgetHelper::DEFAULT_LIMIT,
                    'students'      => [
                        'total'         => $studentsData['total'],
                        'has_more'      => $studentsData['has_more'],
                    ],
                    'entries'       => [
                        'total'         => $entriesData['total'],
                        'has_more'      => $entriesData['has_more'],
                    ],
                ],
            ];
        }
        if ($nextCourseDateForStudent) {
            $session = AttendanceSession::findOneByTermin_id($nextCourseDateForStudent->termin_id);
            $course = $session ? $session->course : null;
            $session_id = $session ? $session->id : null;
            $entry = AttendanceEntry::getUserRecordInSession($session_id, $user->id);

            $payload = [
                'user-status'        => 'student',
                'course-date'   => $nextCourseDateForStudent->toArray(),
                'course'        => $course ? $course->toArray() : null,
                'session-id'    => $session_id,
                'entry'         => $entry ? $entry->toArray() : null,
            ];
        }

        $response = $response->withHeader('Content-Type', 'application/vnd.api+json');
        $response->getBody()->write((string) json_encode($payload));

        return $response;
    }
}