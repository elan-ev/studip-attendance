<?php
/**
 * View Preset Create Route Handler
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes\ViewPreset;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\JsonApi\Schemas\AttendanceViewPresetSchema;
use StudipAttendance\Models\AttendanceViewPreset;

use User;

class Create extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceViewPresetSchema::REL_USER,
    ];

    /**
     * Create View Preset.
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

        if (!Authority::canCreateViewPreset($user)) {
            throw new AuthorizationFailedException();
        }

        $viewPreset = $this->createViewPreset($json, $user);

        return $this->getCreatedResponse($viewPreset);
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
        if (!self::arrayHas($json, 'data.attributes.title')) {
            return 'Missing `title` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.filter-config')) {
            return 'Missing `filter-config` member of attributes block.';
        }
        $filterConfig = self::arrayGet($json, 'data.attributes.filter-config', []);
        if (empty($filterConfig)) {
            return '`filter-config` is required.';
        }
    }

    /**
     * Extract data and creates view preset.
     * @param array $json
     * @return AttendanceViewPreset
     */
    private function createViewPreset(array $json, User $requester)
    {
        $title = self::arrayGet($json, 'data.attributes.title');
        $filterConfig = self::arrayGet($json, 'data.attributes.filter-config');
        $isShared = (bool) self::arrayGet($json, 'data.attributes.is-shared', false);

        sort($filterConfig);

        // Here we must calculate.
        $beforeExecutionTime = time();
        sleep(10);
        // TODO: calculation
        $result = [];
        $afterExecutionTime = time();

        $viewPreset = new AttendanceViewPreset();
        $viewPreset->title = $title;
        $viewPreset->user_id = $requester->id;
        $viewPreset->filter_config = $filterConfig;
        $viewPreset->cached_result = $result;
        $viewPreset->last_calculated = time();
        $viewPreset->last_execution_time_ms = $afterExecutionTime - $beforeExecutionTime;
        $viewPreset->is_shared = $isShared;

        $viewPreset->store();

        return $viewPreset;
    }
}
