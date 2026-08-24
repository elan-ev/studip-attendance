<?php
/**
 * Threshold Delete Route Handler
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes\Threshold;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\Models\AttendanceThreshold;

use Seminar;

class Delete extends JsonApiController
{
    /**
     * Delete Threshold.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return Response
     * @throws AuthorizationFailedException
     * @throws RecordNotFoundException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $course = Seminar::getInstance($args['course_id']);
        if (!$course) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDeleteThreshold($user, $course->id)) {
            throw new AuthorizationFailedException();
        }

        $threshold = AttendanceThreshold::find($args['id']);
        if (!$threshold) {
            throw new RecordNotFoundException();
        }

        $threshold->delete();

        return $this->getCodeResponse(204);
    }
}
