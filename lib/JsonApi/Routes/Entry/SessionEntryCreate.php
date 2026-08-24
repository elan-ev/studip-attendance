<?php
/**
 * Session-Entry Create Route Handler
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
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\JsonApi\Schemas\AttendanceEntrySchema;
use StudipAttendance\Models\AttendanceEntry;
use StudipAttendance\Models\AttendanceSession;

class SessionEntryCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    protected $allowedIncludePaths = [
        AttendanceEntrySchema::REL_USER,
        AttendanceEntrySchema::REL_SESSION,
    ];

    /**
     * Create Entry.
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

        if (!Authority::canCreateEntry($user, $session->seminar_id)) {
            throw new AuthorizationFailedException();
        }

        $entry = $this->createSessionEntry($json, $session);

        return $this->getCreatedResponse($entry);
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
        if (!self::arrayHas($json, 'data.attributes.user-id')) {
            return 'Missing `user-id` member of attributes block.';
        }
        if (!self::arrayHas($json, 'data.attributes.status')) {
            return 'Missing `status` member of attributes block.';
        }
        $status = self::arrayGet($json, 'data.attributes.status');
        if (!in_array($status, AttendanceEntry::STATUSES)) {
            return 'Invalid value for attribute `status`.';
        }
        if (!self::arrayHas($json, 'data.attributes.source')) {
            return 'Missing `source` member of attributes block.';
        }
        $source = self::arrayGet($json, 'data.attributes.source');
        if (!in_array($source, AttendanceEntry::SOURCES)) {
            return 'Invalid value for attribute `source`.';
        }
    }

    /**
     * Extract data and creates session entry.
     * @param array $json
     * @return AttendanceEntry
     */
    private function createSessionEntry(array $json, AttendanceSession $session)
    {
        // Required fields.
        $userId = self::arrayGet($json, 'data.attributes.user-id');
        $status = self::arrayGet($json, 'data.attributes.status');
        $source = self::arrayGet($json, 'data.attributes.source');

        // Optionals
        $comment = self::arrayGet($json, 'data.attributes.comment');
        $teacherInputReason = self::arrayGet($json, 'data.attributes.teacher-input-reason');
        $late = self::arrayGet($json, 'data.attributes.late', 0);
        $leftEarly = self::arrayGet($json, 'data.attributes.left-early', 0);

        $entry = new AttendanceEntry();
        $entry->attendance_session_id = (int) $session->id;
        $entry->user_id = $userId;
        $entry->status = $status;
        $entry->source = $source;
        $entry->comment = $comment;
        $entry->teacher_input_reason = $teacherInputReason;
        $entry->late = $late;
        $entry->left_early = $leftEarly;
        $entry->store();

        return $entry;
    }
}
