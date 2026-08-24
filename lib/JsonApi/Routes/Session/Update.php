<?php
/**
 * Session Update Route Handler
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
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\JsonApi\Schemas\AttendanceSessionSchema;
use StudipAttendance\Models\AttendanceSession;

class Update extends JsonApiController
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
     * Update Session.
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

        $session = AttendanceSession::find($args['id']);
        if (!$session) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUpdateSession($user, $session->seminar_id)) {
            throw new AuthorizationFailedException();
        }

        $session = $this->updateSession($json, $session);
        $session->id = '';
        return $this->getContentResponse($session);
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
     * Extract data and update session.
     * @param array $json
     * @return AttendanceSession
     */
    private function updateSession(array $json, AttendanceSession $session)
    {
        $status = self::arrayGet($json, 'data.attributes.status');
        $qrSeed = self::arrayGet($json, 'data.attributes.qr-seed');

        $session->status = $status;
        $session->qr_seed = $qrSeed;
        $session->store();

        return $session;
    }
}
