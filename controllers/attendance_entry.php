    <?php

/**
 * AttendanceEntryController
 *
 * Everything about attendance is provided in this controller.
 *
 * @package   StudipAttendance
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

use StudipAttendance\Helpers\SessionHandler;
use StudipAttendance\Models\AttendanceEntry;
use StudipAttendance\Models\AttendanceSession;

class AttendanceEntryController extends PluginController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    public function index_action()
    {
        global $perm;
        $this->set_layout(null);
        if (!$perm->have_perm('dozent')) {
            $this->relocate(\URLHelper::getURL('dispatch.php/start'));
        }
    }

    public function qr_code_action()
    {
        $sessionId = Request::int('sessionid');
        $token = Request::option('token');
        $this->set_layout(null);

        $entrySource = AttendanceEntry::SOURCE_USER_QR;
        $this->messages = $this->perform_entry_record($sessionId, $token, $entrySource);
    }

    public function code_action(int $sessionId, string $token)
    {
        $entrySource = AttendanceEntry::SOURCE_USER_CODE;
        $this->perform_entry_record($sessionId, $token, $entrySource);
    }

    private function perform_entry_record(int $sessionId, string $token, string $source): array
    {
        $recordingTime = time();
        $userId = $GLOBALS['user']->id;

        $messages = [];

        $session = AttendanceSession::find($sessionId);
        if (!$session) {
            $messages['error'] = _('Die gewählte Sitzung wurde nicht gefunden.');
            return $messages;
        }

        $messages['meta'] = [
            'course_name' => $session->course ? $session->course->getFullname('name') : _('Unbekannte Veranstaltung'),
            'session_date' => $session->termin ? $session->termin->date : null,
            'session_enddate' => $session->termin ? $session->termin->end_time : null,
        ];

        $validationStatus = SessionHandler::validateCheckin($sessionId, $userId, $token);

        if ($validationStatus === SessionHandler::VALIDATION_SUCCEED) {
            $entry = AttendanceEntry::findOneBySQL(
                'attendance_session_id = ? AND user_id = ?',
                [$session->id, $userId]
            ) ?? new AttendanceEntry();

            $entry->attendance_session_id = $session->id;
            $entry->user_id = $userId;
            $entry->source = $source;
            $entry->status = AttendanceEntry::STATUS_PRESENT;
            $entry->late = SessionHandler::calculateLatency($session, $recordingTime);
            $entry->store();

            $messages['success'] = _('Ihre Teilnahme an dieser Sitzung wurde erfolgreich erfasst.');
        }
        else {
            $messages['error'] = match ($validationStatus) {
                SessionHandler::VALIDATION_FAILED_SESSION => _('Die Anwesenheitssitzung ist nicht aktiv oder existiert nicht.'),
                SessionHandler::VALIDATION_FAILED_PARTICIPANT => _('Sie sind nicht als Teilnehmer für diesen Kurs eingetragen.'),
                SessionHandler::VALIDATION_FAILED_TIMEFRAME => _('Der Check-In befindet sich außerhalb des zulässigen Zeitfensters.'),
                SessionHandler::VALIDATION_FAILED_TOTP => _('Ungültiger oder abgelaufener QR-Code / Code.'),
                SessionHandler::VALIDATION_FAILED_ENTRY => _('Ihre Anwesenheit wurde für diese Sitzung bereits erfasst.'),
                default => _('Ihre Teilnahme an dieser Sitzung konnte nicht erfasst werden.')
            };

        }

        return $messages;
    }
}
