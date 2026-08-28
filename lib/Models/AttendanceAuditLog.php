<?php

namespace StudipAttendance\Models;

use Exception;
use SimpleORMap;
use JSONArrayObject;
use User;

/**
 * Attendance Audit Log Model.
 *
 * @package   StudipAttendance\Models
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $id
 * @property string $object_type
 * @property int $object_id
 * @property string $user_id
 * @property string $action
 * @property JSONArrayObject $payload
 * @property string $comment
 *
 * @property User $user
 */

class AttendanceAuditLog extends SimpleORMap
{
    const ACTION_MANUAL_OVERRIDE = 'manual_override';
    const ACTION_SYSTEM_CHANGE = 'system_change';

    const ACTION_MANUAL_DELETE = 'manual_delete';
    const ACTION_SYSTEM_DELETE = 'system_delete';

    const ACTIONS = [
        self::ACTION_MANUAL_OVERRIDE,
        self::ACTION_SYSTEM_CHANGE,
    ];

    protected static function configure($config = [])
    {
        $config['db_table'] = 'elan_attendance_audit_log';

        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
            'on_delete' => 'delete',
        ];

        $config['serialized_fields']['payload'] = JSONArrayObject::class;

        $config['registered_callbacks']['before_delete'][] = 'cbEnsureReadOnly';
        $config['registered_callbacks']['before_update'][] = 'cbEnsureReadOnly';

        parent::configure($config);
    }

    public function cbEnsureReadOnly() {
        throw new Exception("Unable to change or delete: Read-Only!");
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }

    public static function write(string $objectType, int $objectId, string $action, array $payload = [], string $comment = null, string $userId = null): bool
    {

        $userId = $userId ?? $GLOBALS['user']->id;
        $data = [
            'object_type' => $objectType,
            'object_id' => $objectId,
            'action' => $action,
            'payload' => $payload,
            'comment' => $comment,
            'user_id' => $userId,
        ];
        $record = AttendanceAuditLog::create($data);
        return !empty($record);
    }

    public static function writeForEntry(int $id, string $action, array $payload = [], string $comment = null, string $userId = null): bool {
        $objectType = AttendanceEntry::class;
        return self::write($objectType, $id, $action, $payload, $comment, $userId);
    }

    public static function writForSession(int $id, string $action, array $payload = [], string $comment = null, string $userId = null): bool {
        $objectType = AttendanceSession::class;
        return self::write($objectType, $id, $action, $payload, $comment, $userId);
    }
}
