<?php
/**
 * Threshold Create Route Handler
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
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\JsonApi\Schemas\AttendanceThresholdSchema;
use StudipAttendance\Models\AttendanceThreshold;

use Seminar;

class Create extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceThresholdSchema::REL_COURSE,
    ];

    /**
     * Create Threshold.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return Response
     * @throws AuthorizationFailedException
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $user = $this->getUser($request);

        $course = Seminar::getInstance($args['id']);
        if (!$course) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canCreateThreshold($user, $course->id)) {
            throw new AuthorizationFailedException();
        }

        $threshold = $this->createThreshold($json, $course->id);

        return $this->getCreatedResponse($threshold);
    }

    /**
     * @inheritDoc
     */
    protected function validateResourceDocument($json, $data)
    {
        // Higher level validation.
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (!self::arrayHas($json, 'data.attributes')) {
            return 'Missing `attributes` member of data block.';
        }

        // Attributes existence validation.
        if (!self::arrayHas($json, 'data.attributes.unexcused-warning-percent')) {
            return 'Missing `unexcused-warning-percent` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.unexcused-critical-percent')) {
            return 'Missing `unexcused-critical-percent` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.absence-days-warning')) {
            return 'Missing `absence-days-warning` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.absence-days-critical')) {
            return 'Missing `absence-days-critical` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.absence-weeks-warning')) {
            return 'Missing `absence-weeks-warning` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.absence-weeks-critical')) {
            return 'Missing `absence-weeks-critical` member of attributes block.';
        }
    }

    /**
     * Extract data and creates threshold.
     * @param array $json
     * @return AttendanceThreshold
     */
    private function createThreshold(array $json, string $courseId)
    {
        $unexcusedWarningPercent = (int) self::arrayGet($json, 'data.attributes.unexcused-warning-percent', 0);
        $unexcusedCriticalPercent = (int) self::arrayGet($json, 'data.attributes.unexcused-critical-percent', 0);
        $absenceDaysWarning = (int) self::arrayGet($json, 'data.attributes.absence-days-warning', 0);
        $absenceDaysCritical = (int) self::arrayGet($json, 'data.attributes.absence-days-critical', 0);
        $absenceWeeksWarning = (int) self::arrayGet($json, 'data.attributes.absence-weeks-warning', 0);
        $absenceWeeksCritical = (int) self::arrayGet($json, 'data.attributes.absence-weeks-critical', 0);

        $threshold = new AttendanceThreshold();
        $threshold->seminar_id = $courseId;
        $threshold->unexcused_warning_percent = $unexcusedWarningPercent;
        $threshold->unexcused_critical_percent = $unexcusedCriticalPercent;
        $threshold->absence_days_warning = $absenceDaysWarning;
        $threshold->absence_days_critical = $absenceDaysCritical;
        $threshold->absence_weeks_warning = $absenceWeeksWarning;
        $threshold->absence_weeks_critical = $absenceWeeksCritical;

        $threshold->store();

        return $threshold;
    }
}
