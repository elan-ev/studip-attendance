<?php
/**
 * Observers class for StudipAttendance.
 *
 * @package   StudipAttendance\Events
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Events;

use CourseDate;
use Seminar;

use StudipAttendance\Helpers\SessionHandler;

class Observers {

    public static function subscribeToCourseDateCreation($event, CourseDate $courseDate): void
    {
        SessionHandler::ensureSessionExistsFrom($courseDate);
    }

    /**
     * This works only in StudIP 6.x
     * @param mixed $event
     * @param CourseDate $courseDate
     * @return void
     */
    public static function subscribeToCourseDateDeletion($event, CourseDate $courseDate): void
    {
        SessionHandler::handleCourseDateDeletion($courseDate->termin_id);
    }

    public static function subscribeToCourseDidChangeSchedule($event, Seminar $course): void
    {
        SessionHandler::handleCourseChangedSchedule($course);
    }
}
