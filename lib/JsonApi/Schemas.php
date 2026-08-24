<?php
/**
 * Schemas trait
 *
 * Registers JSON-API Schemas for StudipAttendance models.
 *
 * @package   StudipAttendance\JsonApi
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi;

trait Schemas
{
    public function registerSchemas(): array
    {
        return [
            \StudipAttendance\Models\AttendanceAuditLog::class => \StudipAttendance\JsonApi\Schemas\AttendanceAuditLogSchema::class,
            \StudipAttendance\Models\AttendanceEntry::class => \StudipAttendance\JsonApi\Schemas\AttendanceEntrySchema::class,
            \StudipAttendance\Models\AttendanceReport::class => \StudipAttendance\JsonApi\Schemas\AttendanceReportSchema::class,
            \StudipAttendance\Models\AttendanceSession::class => \StudipAttendance\JsonApi\Schemas\AttendanceSessionSchema::class,
            \StudipAttendance\Models\AttendanceThreshold::class => \StudipAttendance\JsonApi\Schemas\AttendanceThresholdSchema::class,
            \StudipAttendance\Models\AttendanceViewPreset::class => \StudipAttendance\JsonApi\Schemas\AttendanceViewPresetSchema::class,
        ];
    }
}
