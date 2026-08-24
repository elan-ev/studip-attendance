<?php

namespace StudipAttendance\Models;

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

    const ACTIONS = [
        self::ACTION_MANUAL_OVERRIDE,
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

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }
}
