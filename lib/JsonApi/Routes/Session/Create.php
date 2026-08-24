<?php
/**
 * Session Create Route Handler
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
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\JsonApi\Schemas\AttendanceSessionSchema;
use StudipAttendance\Models\AttendanceSession;

class Create extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceSessionSchema::REL_TERMIN,
        AttendanceSessionSchema::REL_COURSE,
    ];

    /**
     * Create Session.
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

        $courseId = self::arrayGet($json, 'data.attributes.seminar-id');

        if (!Authority::canCreateSession($user, $courseId)) {
            throw new AuthorizationFailedException();
        }

        $session = $this->createSession($json);

        return $this->getCreatedResponse($session);
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
        if (!self::arrayHas($json, 'data.attributes.termin-id')) {
            return 'Missing `termin-id` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.seminar-id')) {
            return 'Missing `seminar-id` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.status')) {
            return 'Missing `status` member of attributes block.';
        }
        $status = self::arrayGet($json, 'data.attributes.status');
        if (!in_array($status, AttendanceSession::STATUSES)) {
            return 'Invalid value for attribute `status`.';
        }

        if (!self::arrayHas($json, 'data.attributes.qr-seed')) {
            return 'Missing `qr-seed` member of attributes block.';
        }
    }

    /**
     * Extract data and creates contract.
     * @param array $json
     * @return AttendanceSession
     */
    private function createSession(array $json)
    {
        $terminId = self::arrayGet($json, 'data.attributes.termin-id');
        $seminarId = self::arrayGet($json, 'data.attributes.seminar-id');
        $status = self::arrayGet($json, 'data.attributes.status');
        $qrSeed = self::arrayGet($json, 'data.attributes.qr-seed');

        $session = new AttendanceSession();
        $session->termin_id = $terminId;
        $session->seminar_id = $seminarId;
        $session->status = $status;
        $session->qr_seed = $qrSeed;
        $session->store();

        return $session;
    }
}
