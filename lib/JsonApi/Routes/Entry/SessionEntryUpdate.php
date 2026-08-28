<?php
/**
 * Entry Update Route Handler
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
use StudipAttendance\Helpers\Utils;
use StudipAttendance\Models\AttendanceEntry;
use StudipAttendance\Models\AttendanceSession;
use StudipAttendance\Models\AttendanceAuditLog;

class SessionEntryUpdate extends JsonApiController
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
     * Update Entry.
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

        $session = AttendanceSession::find($args['session_id']);
        if (!$session) {
            throw new RecordNotFoundException();
        }

        $entry = $session->entries->find($args['id']);
        if (!$entry) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canUpdateEntry($user, $entry->session->seminar_id)) {
            throw new AuthorizationFailedException();
        }

        $source = AttendanceEntry::SOURCE_TEACHER;
        if (Utils::isRoot($user) || Utils::isCourseAdmin($user, $session->seminar_id)) {
            $source = AttendanceEntry::SOURCE_ADMIN;
        }

        $entry = $this->updateEntry($json, $entry, $source);

        $entry->id = '';
        return $this->getContentResponse($entry);
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
        if (!in_array($status, AttendanceEntry::STATUSES)) {
            return 'Invalid value for attribute `status`.';
        }
        $source = self::arrayGet($json, 'data.attributes.source');
        if (!in_array($source, AttendanceEntry::SOURCES)) {
            return 'Invalid value for attribute `source`.';
        }
    }

    /**
     * Extract data and update entry.
     * @param array $json
     * @return AttendanceEntry
     */
    private function updateEntry(array $json, AttendanceEntry $entry, string $source)
    {
        // Required fields.
        $status = self::arrayGet($json, 'data.attributes.status');

        // Optionals.
        $comment = self::arrayGet($json, 'data.attributes.comment');
        $teacherInputReason = self::arrayGet($json, 'data.attributes.teacher-input-reason');
        $late = (int) self::arrayGet($json, 'data.attributes.late', 0);
        $leftEarly = (int) self::arrayGet($json, 'data.attributes.left-early', 0);

        $entry->status = $status;
        $entry->source = $source;

        if (!empty($comment)) {
            $entry->comment = $comment;
        }
        if (!empty($teacherInputReason)) {
            $entry->teacher_input_reason = $teacherInputReason;
        }
        if (!empty($late) && $entry->late != $late) {
            $entry->late = $late;
        }
        if (!empty($leftEarly) && $entry->left_early != $leftEarly) {
            $entry->left_early = $leftEarly;
        }

        $entry->store();

        return $entry;
    }
}
