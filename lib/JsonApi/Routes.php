<?php
/**
 * Routes Trait
 *
 * Registers the routes for StudipAttendance plugin
 *
 * @package   StudipAttendance\JsonApi
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi;

use Slim\Routing\RouteCollectorProxy;

trait Routes
{
    public function registerAuthenticatedRoutes(RouteCollectorProxy $group)
    {
        // Audit Logs.
        $group->get('/attendance-audit-logs', \StudipAttendance\JsonApi\Routes\AuditLog\Index::class);
        $group->get('/attendance-audit-logs/{id}', \StudipAttendance\JsonApi\Routes\AuditLog\Show::class);
        $group->post('/attendance-audit-logs', \StudipAttendance\JsonApi\Routes\AuditLog\Create::class);

        // Entries.
        $group->get('/attendance-entries', \StudipAttendance\JsonApi\Routes\Entry\Index::class);
        $group->get('/attendance-entries/{id}', \StudipAttendance\JsonApi\Routes\Entry\Show::class);
        $group->post('/attendance-entries', \StudipAttendance\JsonApi\Routes\Entry\Create::class);
        $group->patch('/attendance-entries/{id}', \StudipAttendance\JsonApi\Routes\Entry\Update::class);
        $group->delete('/attendance-entries/{id}', \StudipAttendance\JsonApi\Routes\Entry\Delete::class);

        // Session - Entries
        $group->get('/attendance-sessions/{id}/entries', \StudipAttendance\JsonApi\Routes\Entry\SessionEntryIndex::class);
        $group->post('/attendance-sessions/{id}/entries', \StudipAttendance\JsonApi\Routes\Entry\SessionEntryCreate::class);
        $group->get('/attendance-sessions/{session_id}/entries/{id}', \StudipAttendance\JsonApi\Routes\Entry\SessionEntryShow::class);

        // Reports.
        $group->get('/attendance-reports', \StudipAttendance\JsonApi\Routes\Report\Index::class);

        // Session.
        $group->get('/attendance-sessions', \StudipAttendance\JsonApi\Routes\Session\Index::class);
        $group->get('/attendance-sessions/{id}', \StudipAttendance\JsonApi\Routes\Session\Show::class);
        $group->post('/attendance-sessions', \StudipAttendance\JsonApi\Routes\Session\Create::class);
        $group->patch('/attendance-sessions/{id}', \StudipAttendance\JsonApi\Routes\Session\Update::class);
        $group->delete('/attendance-sessions/{id}', \StudipAttendance\JsonApi\Routes\Session\Delete::class);

        // Course-Sessions
        $group->get('/course/{id}/attendance-sessions', \StudipAttendance\JsonApi\Routes\Session\CourseIndex::class);

        // Course-Thresholds
        $group->get('/course/{id}/attendance-thresholds', \StudipAttendance\JsonApi\Routes\Threshold\Index::class);
        $group->get('/course/{course_id}/attendance-thresholds/{id}', \StudipAttendance\JsonApi\Routes\Threshold\Show::class);
        $group->post('/course/{id}/attendance-thresholds', \StudipAttendance\JsonApi\Routes\Threshold\Create::class);
        $group->patch('/course/{course_id}/attendance-thresholds/{id}', \StudipAttendance\JsonApi\Routes\Threshold\Update::class);
        $group->delete('/course/{course_id}/attendance-thresholds/{id}', \StudipAttendance\JsonApi\Routes\Threshold\Delete::class);

        // View Presets
        $group->get('/attendance-view-presets', \StudipAttendance\JsonApi\Routes\ViewPreset\Index::class);
        $group->get('/attendance-view-presets/{id}', \StudipAttendance\JsonApi\Routes\ViewPreset\Show::class);
        $group->post('/attendance-view-presets', \StudipAttendance\JsonApi\Routes\ViewPreset\Create::class);
        $group->patch('/attendance-view-presets/{id}', \StudipAttendance\JsonApi\Routes\ViewPreset\Update::class);
        $group->delete('/attendance-view-presets/{id}', \StudipAttendance\JsonApi\Routes\ViewPreset\Delete::class);
    }

    public function registerUnauthenticatedRoutes(RouteCollectorProxy $group)
    {
    }
}
