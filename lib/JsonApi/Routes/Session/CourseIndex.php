<?php
/**
 * Course Session Index Route Handler
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes\Session;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Seminar;
use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\JsonApi\Schemas\AttendanceSessionSchema;
use StudipAttendance\Models\AttendanceSession;

class CourseIndex extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceSessionSchema::REL_TERMIN,
        AttendanceSessionSchema::REL_COURSE,
    ];

    /**
     * Index Course Sessions.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $course = Seminar::getInstance($args['id']);
        if (!$course) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexCourseSession($user, $course->id)) {
            throw new AuthorizationFailedException();
        }

        [$offset, $limit] = $this->getOffsetAndLimit();

        $sessions = AttendanceSession::findBySeminar_id($course->id);
        $total = count($sessions);
        $data = array_slice($sessions, $offset, $limit);

        return $this->getPaginatedContentResponse($data, $total);
    }
}
