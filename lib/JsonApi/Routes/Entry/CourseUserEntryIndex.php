<?php
/**
 * CourseUserEntryIndex Route Handler
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.2.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes\Entry;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use Seminar;
use StudipAttendance\Models\AttendanceEntry;
use StudipAttendance\Models\AttendanceSession;

class CourseUserEntryIndex extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [];

    /**
     * Index Entries.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $course = Seminar::getInstance($args['course_id']);
        if (!$course) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexCourseUserEntry($user, $course->id)) {
            throw new AuthorizationFailedException();
        }

        [$offset, $limit] = $this->getOffsetAndLimit();

        $sessions = AttendanceSession::findBySeminar_id($course->id);

        $entries = [];

        foreach ($sessions as $session) {
            if (!$session->termin) { continue; }
            $record = AttendanceEntry::getUserRecordInSession($session->id, $user->id);

            if ($record === null) {
                $record = new AttendanceEntry();
                $record->id = 'absent-in-' .$session->id;
                $record->attendance_session_id = $session->id;
                $record->user_id = $user->id;
                $record->status = 'absent';
            }
            $entries[] = $record;
        }
        $total = count($entries);
        $data = array_slice($entries, $offset, $limit);

        return $this->getPaginatedContentResponse($data, $total);
    }
}
