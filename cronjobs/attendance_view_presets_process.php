<?php
require_once __DIR__ . '/../bootstrap.php';
/**
 * View Preset cronjob class for StudipAttendance.
 *
 * @package   StudipAttendance
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

use StudipAttendance\Models\AttendanceViewPreset;

class AttendanceViewPresetsProcess extends CronJob
{
    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return _('Elan Attendance Plugin - View presets calculatation');
    }

    /**
     * @inheritDoc
     */
    public static function getDescription()
    {
        return _('Tries to calculate and process the view presets.');
    }

    /**
     * @inheritDoc
     */
    public function execute($last_result, $parameters = array())
    {
        try {
            AttendanceViewPreset::runQueries();
        } catch (\Throwable $th) {
            throw new Exception("Attendance - View Presets: " . $th->getMessage());
        }
    }
}
