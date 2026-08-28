<?php
require_once __DIR__ . '/../bootstrap.php';
/**
 * Report cronjob class for StudipAttendance.
 *
 * @package   StudipAttendance
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

use StudipAttendance\Models\AttendanceReport;

class AttendanceReportProcess extends CronJob
{
    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return _('Elan Attendance Plugin - Calculating Reports');
    }

    /**
     * @inheritDoc
     */
    public static function getDescription()
    {
        return _('Elan Attendance Plugin - Calculates Report in the background for performance reasons...');
    }

    /**
     * @inheritDoc
     */
    public function execute($last_result, $parameters = array())
    {
        // Reports are hardcoded systematic queries to be ran, they should be placed in the model and processed here.
        try {
            AttendanceReport::runQueries();
        } catch (\Throwable $th) {
            throw new Exception("Attendance - Report: " . $th->getMessage());
        }
    }
}
