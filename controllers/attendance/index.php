<?php

/**
 * AttendanceController
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
use StudipAttendance\Helpers\Utils;
use StudipAttendance\Models\AttendanceEntry;
use StudipAttendance\Models\AttendanceSession;

class AttendanceController extends PluginController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        // TODO: to be implemented.
    }

    public function index_action()
    {
        // TODO: to be implemented.
    }

    public function qr_code_action(int $sessionId, string $token)
    {
        $entrySource = AttendanceEntry::SOURCE_USER_QR;
        $this->perform_entry_record($sessionId, $token, $entrySource);
    }

    public function code_action(int $sessionId, string $token)
    {
        $entrySource = AttendanceEntry::SOURCE_USER_CODE;
        $this->perform_entry_record($sessionId, $token, $entrySource);
    }

    // TODO: recheck and polish the implementation here.
    private function perform_entry_record(int $sessionId, string $token, string $source): void
    {
        $recordingTime = time();

        $userId = $GLOBALS['user']->id;
        $validationStatus = SessionHandler::validateCheckin($sessionId, $userId, $token);
        if ($validationStatus === SessionHandler::VALIDATION_SUCCEED) {
            $session = AttendanceSession::find($sessionId);
            $entry = new AttendanceEntry();
            $entry->attendance_session_id = $session->id;
            $entry->user_id = $userId;
            $entry->source = $source;
            $entry->status = AttendanceEntry::STATUS_PRESENT;
            $entry->late = SessionHandler::calculateLatency($session, $recordingTime);
            $entry->store();

            // Feedback
            Utils::sendFeeback($session, $entry, $userId);
            PageLayout::postSuccess(_('Ihre Teilnahme an dieser Sitzung wurde erfolgreich erfasst.'));
            $this->redirect($this->action_url('index'));
        }

        $errorMessage = _('Ihre Teilnahme an dieser Sitzung konnte nicht erfasst werden.');
        if ($validationStatus === SessionHandler::VALIDATION_FAILED_TOTP) {
            $errorMessage = _('Ungültiger Token!');
        }

        PageLayout::postError(_($errorMessage));
    }
}
