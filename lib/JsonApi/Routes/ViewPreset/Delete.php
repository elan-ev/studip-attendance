<?php
/**
 * View Preset Delete Route Handler
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
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\Models\AttendanceViewPreset;

class Delete extends JsonApiController
{
    /**
     * Delete View Preset.
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

        if (!Authority::canDeleteViewPreset($user)) {
            throw new AuthorizationFailedException();
        }

        $viewPreset = AttendanceViewPreset::find($args['id']);
        if (!$viewPreset) {
            throw new RecordNotFoundException();
        }

        $viewPreset->delete();

        return $this->getCodeResponse(204);
    }
}
