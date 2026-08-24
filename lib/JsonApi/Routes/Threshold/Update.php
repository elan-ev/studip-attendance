<?php
/**
 * Threshold Update Route Handler
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

class Update extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceThresholdSchema::REL_COURSE,
    ];

    /**
     * Update Threshold.
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

        $course = Seminar::getInstance($args['course_id']);
        if (!$course) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUpdateThreshold($user, $course->id)) {
            throw new AuthorizationFailedException();
        }

        $threshold = AttendanceThreshold::find($args['id']);
        if (!$threshold) {
            throw new RecordNotFoundException();
        }

        $threshold = $this->updateThreshold($json, $threshold);
        $threshold->id = '';
        return $this->getContentResponse($threshold);
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
     * Extract data and update threshold.
     * @param array $json
     * @return AttendanceThreshold
     */
    private function updateThreshold(array $json, AttendanceThreshold $threshold)
    {
        $unexcusedWarningPercent = (int) self::arrayGet($json, 'data.attributes.unexcused-warning-percent');
        $unexcusedCriticalPercent = (int) self::arrayGet($json, 'data.attributes.unexcused-critical-percent');
        $absenceDaysWarning = (int) self::arrayGet($json, 'data.attributes.absence-days-warning');
        $absenceDaysCritical = (int) self::arrayGet($json, 'data.attributes.absence-days-critical');
        $absenceWeeksWarning = (int) self::arrayGet($json, 'data.attributes.absence-weeks-warning');
        $absenceWeeksCritical = (int) self::arrayGet($json, 'data.attributes.absence-weeks-critical');

        if (!is_null($unexcusedWarningPercent)) {
            $threshold->unexcused_warning_percent = $unexcusedWarningPercent;
        }
        if (!is_null($unexcusedCriticalPercent)) {
            $threshold->unexcused_critical_percent = $unexcusedCriticalPercent;
        }
        if (!is_null($absenceDaysWarning)) {
            $threshold->absence_days_warning = $absenceDaysWarning;
        }
        if (!is_null($absenceDaysCritical)) {
            $threshold->absence_days_critical = $absenceDaysCritical;
        }
        if (!is_null($absenceWeeksWarning)) {
            $threshold->absence_weeks_warning = $absenceWeeksWarning;
        }
        if (!is_null($absenceWeeksCritical)) {
            $threshold->absence_weeks_critical = $absenceWeeksCritical;
        }

        $threshold->store();

        return $threshold;
    }
}
