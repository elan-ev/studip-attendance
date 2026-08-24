<?php
/**
 * Session-Entry Index Route Handler
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
use StudipAttendance\Models\AttendanceSession;

class SessionEntryIndex extends JsonApiController
{
    /**
     * @inheritDoc
     */
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceEntrySchema::REL_USER,
        AttendanceEntrySchema::REL_SESSION,
    ];

    /**
     * Index Entries.
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

        if (!Authority::canIndexSessionEntry($user, $session->seminar_id)) {
            throw new AuthorizationFailedException();
        }

        [$offset, $limit] = $this->getOffsetAndLimit();

        $entries = AttendanceEntry::getAll();
        $total = count($entries);
        $data = array_slice($entries, $offset, $limit);

        return $this->getPaginatedContentResponse($data, $total);
    }
}
