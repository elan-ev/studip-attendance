<?php
/**
 * Audit Log Interface
 *
 * Enables a model to be utilized by audit logging.
 *
 * @package   StudipAttendance\Classes
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Classes;

interface AuditLogInterface
{
    /**
     * Returns payload information to be logged.
     * @return array
     */
    public function getLogPayload(): array;

    /**
     * Callback function to record the log after updating the object.
     * @return void
     */
    public function cbLogAfterUpdate(): void;

    /**
     * Callback function to record the log before object deletion.
     * @return void
     */
    public function cbLogBeforeDelete(): void;
}
