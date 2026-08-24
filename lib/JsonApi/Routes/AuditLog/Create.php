<?php
/**
 * Audit Log Create Route Handler
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes\AuditLog;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\JsonApi\Schemas\AttendanceAuditLogSchema;
use StudipAttendance\Models\AttendanceAuditLog;

class Create extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceAuditLogSchema::REL_USER,
    ];

    /**
     * Create Audit Log.
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

        if (!Authority::canCreateAuditLog($user)) {
            throw new AuthorizationFailedException();
        }

        $auditLog = $this->createAuditLog($json);

        return $this->getCreatedResponse($auditLog);
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
        if (!self::arrayHas($json, 'data.attributes.object-type')) {
            return 'Missing `object-type` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.object-id')) {
            return 'Missing `object-id` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.action')) {
            return 'Missing `action` member of attributes block.';
        }
        $action = self::arrayGet($json, 'data.attributes.action');
        if (!in_array($action, AttendanceAuditLog::ACTIONS)) {
            return 'Invalid value for attribute `action`.';
        }

        if (!self::arrayHas($json, 'data.attributes.payload')) {
            return 'Missing `payload` member of attributes block.';
        }
        $payload = self::arrayGet($json, 'data.attributes.payload', []);
        if (empty($payload)) {
            return '`payload` is required.';
        }
    }

    /**
     * Extract data and creates audit log.
     * @param array $json
     * @return AttendanceAuditLog
     */
    private function createAuditLog(array $json)
    {
        $objectType = self::arrayGet($json, 'data.attributes.object-type');
        $objectId = self::arrayGet($json, 'data.attributes.object-id');
        $action = self::arrayGet($json, 'data.attributes.action');
        $payload = self::arrayGet($json, 'data.attributes.payload', []);
        $comment = self::arrayGet($json, 'data.attributes.comment');

        $auditLog = new AttendanceAuditLog();
        $auditLog->object_type = $objectType;
        $auditLog->object_id = $objectId;
        $auditLog->action = $action;
        $auditLog->payload = $payload;
        $auditLog->comment = $comment;
        $auditLog->store();

        return $auditLog;
    }
}
