<?php
/**
 * Audit Log Model trait
 *
 * Contains callback methods for those models that need to write audit log.
 *
 * @package   StudipAttendance\Classes
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Classes;

use StudipAttendance\Models\AttendanceAuditLog;

use User;

trait AuditLogTrait
{
    public function cbLogAfterUpdate(): void
    {
        $user = User::findCurrent();
        $action = $user->id === 'cli' ? AttendanceAuditLog::ACTION_SYSTEM_CHANGE : AttendanceAuditLog::ACTION_MANUAL_OVERRIDE;
        $payload = $this->getLogPayload();
        AttendanceAuditLog::write(
            static::class,
            $this->id,
            $action,
            $payload,
            '[INFO]: model has been updated.',
            $user->id
        );
    }

    public function cbLogBeforeDelete(): void
    {
        $user = User::findCurrent();
        $action = $user->id === 'cli' ? AttendanceAuditLog::ACTION_SYSTEM_DELETE : AttendanceAuditLog::ACTION_MANUAL_DELETE;
        $payload = $this->getLogPayload();
        AttendanceAuditLog::write(
            static::class,
            $this->id,
            $action,
            $payload,
            '[INFO]: model will delete.',
            $user->id
        );
    }
}
