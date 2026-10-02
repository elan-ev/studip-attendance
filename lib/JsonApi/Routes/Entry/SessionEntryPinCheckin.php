<?php
/**
 * Session-Entry Create Route Handler
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.1.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes\Entry;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use StudipAttendance\JsonApi\Routes\Authority;
use StudipAttendance\Helpers\SessionHandler;
use StudipAttendance\Models\AttendanceEntry;
use StudipAttendance\Models\AttendanceSession;


class SessionEntryPinCheckin extends JsonApiController
{
    use ValidationTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $user = $this->getUser($request);

        $session = AttendanceSession::find($args['id']);
        if (!$session) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canStudentCheckin($user, $session->seminar_id)) {
            throw new AuthorizationFailedException();
        }

        $entry = $this->createSessionEntry($json, $session, $user);

        return $this->getCreatedResponse($entry);
    }

    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data.attributes.pin')) {
            return 'Missing `pin` member of attributes block.';
        }
    }

    private function createSessionEntry(array $json, AttendanceSession $session, $user): AttendanceEntry
    {
        $pin = self::arrayGet($json, 'data.attributes.pin');
        $validationStatus = SessionHandler::validateCheckin((int) $session->id, $user->id, $pin);

        switch ($validationStatus) {
            case SessionHandler::VALIDATION_SUCCEED:
                $entry = new AttendanceEntry();
                $entry->attendance_session_id = $session->id;
                $entry->user_id = $user->id;
                $entry->source = AttendanceEntry::SOURCE_USER_CODE;
                $entry->status = AttendanceEntry::STATUS_PRESENT;
                $entry->late = SessionHandler::calculateLatency($session, time());
                $entry->store();

                return $entry;

            case SessionHandler::VALIDATION_FAILED_SESSION:
                throw new RecordNotFoundException('Attendance session not found or inactive.');

            case SessionHandler::VALIDATION_FAILED_PARTICIPANT:
                throw new AuthorizationFailedException(_('Sie sind kein Teilnehmer dieser Veranstaltung.'));

            case SessionHandler::VALIDATION_FAILED_TIMEFRAME:
                throw new UnprocessableEntityException(_('Der Check-in ist für diesen Termin aktuell nicht möglich.'));

            case SessionHandler::VALIDATION_FAILED_TOTP:
                throw new UnprocessableEntityException(_('Der eingegebene PIN ist ungültig oder abgelaufen.'));

            case SessionHandler::VALIDATION_FAILED_ENTRY:
                $existingEntry = AttendanceEntry::findOneBySQL('session_id = ? AND user_id = ?', [$session->id, $user->id]);
                if ($existingEntry) {
                    return $existingEntry;
                }
                throw new UnprocessableEntityException(_('Sie sind für diesen Termin bereits eingecheckt.'));

            default:
                throw new UnprocessableEntityException(_('Check-in fehlgeschlagen.'));
        }
    }
}