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
use JsonApi\NonJsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\Models\AttendanceSession;
use StudipAttendance\Helpers\TOTPHandler;

class TOTPGenerate extends NonJsonApiController
{
    /**
     * Generate TOTP for a session.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $session = AttendanceSession::find($args['id']);
        if (!$session) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canGenerateSessionTOTP($user, $session->seminar_id)) {
            throw new AuthorizationFailedException();
        }

        $serverTimestamp = time();
        $token = TOTPHandler::generateTOTP($session->qr_seed, $serverTimestamp);

        $data = [
            'token' => $token,
            'server-timestamp' => $serverTimestamp,
            'time-window' => TOTPHandler::getTimeWindow(),
        ];

        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write((string) json_encode($data));

        return $response;
    }
}
