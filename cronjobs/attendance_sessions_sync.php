<?php
require_once __DIR__ . '/../bootstrap.php';
/**
 * Sessions synchronization cronjob class for StudipAttendance.
 *
 * @package   StudipAttendance
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

use StudipAttendance\Helpers\SessionHandler;

class AttendanceSessionsSync extends CronJob
{
    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return _('Elan Attendance Plugin - Sessions synchronization');
    }

    /**
     * @inheritDoc
     */
    public static function getDescription()
    {
        return _('Tries to discover and synchronize the course dates with attendance sessions.');
    }

    /**
     * @inheritDoc
     */
    public function execute($last_result, $parameters = array())
    {
        try {
            $discoveredCourseDates = SessionHandler::discoverNonRecordedCourseDates();

            if (!empty($discoveredCourseDates)) {
                foreach ($discoveredCourseDates as $cd) {
                    SessionHandler::ensureSessionExistsFrom($cd);
                }
            }
        } catch (\Throwable $th) {
            throw new Exception("Attendance - Sessions: " . $th->getMessage());
        }
    }
}
