<?php
/**
 * Threshold Show Route Handler
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
use StudipAttendance\JsonApi\Schemas\AttendanceThresholdSchema;
use StudipAttendance\Models\AttendanceThreshold;

use Seminar;

class Show extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceThresholdSchema::REL_COURSE,
    ];

    /**
     * Show a Threshold.
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

        $threshold = AttendanceThreshold::find($args['id']);
        if (!$threshold) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowThreshold($user, $course->id)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($threshold);
    }
}
