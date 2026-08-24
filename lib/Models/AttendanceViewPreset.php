<?php

namespace StudipAttendance\Models;

use SimpleORMap;
use JSONArrayObject;
use User;

/**
 * Attendance View Preset Model.
 *
 * @package   StudipAttendance\Models
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 *
 * @property int $id
 * @property string $user_id
 * @property string $title
 * @property JSONArrayObject $filter_config
 * @property JSONArrayObject $cached_result
 * @property int $last_calculated
 * @property int $last_execution_time_ms
 * @property bool $is_shared
 *
 * @property User $user
 */

class AttendanceViewPreset extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'elan_attendance_view_presets';

        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
            'on_delete' => 'delete',
        ];

        $config['serialized_fields']['filter_config'] = JSONArrayObject::class;
        $config['serialized_fields']['cached_result'] = JSONArrayObject::class;

        parent::configure($config);
    }

    public static function getAll(): array
    {
        return self::findBySQL('1');
    }
}
