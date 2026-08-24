<?php
/**
 * Entry Show Route Handler
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes\Entry;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\JsonApi\Schemas\AttendanceEntrySchema;
use StudipAttendance\Models\AttendanceEntry;

class Show extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceEntrySchema::REL_USER,
        AttendanceEntrySchema::REL_SESSION,
    ];

    /**
     * Show a Entry.
     * @param Request $request
     * @param Response $response
     * @param mixed $args
     * @return void
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);

        $entry = AttendanceEntry::find($args['id']);
        if (!$entry) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowEntry($user)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($entry);
    }
}
