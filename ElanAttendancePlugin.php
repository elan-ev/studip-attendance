<?php

/**
 * StudipAttendance Plugin
 *
 * @package   StudipAttendance
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

require_once __DIR__ . '/bootstrap.php';

use JsonApi\Contracts\JsonApiPlugin;
use StudipAttendance\JsonApi\Routes;
use StudipAttendance\JsonApi\Schemas;

use StudipAttendance\Helpers\TeacherWidgetHelper;
use StudipAttendance\Models\AttendanceSession;

class ElanAttendancePlugin extends StudIPPlugin implements SystemPlugin, JsonApiPlugin, PortalPlugin
{
    use Routes;
    use Schemas;

    public function __construct()
    {
        global $perm;

        parent::__construct();
        PageLayout::addStylesheet($this->getPluginUrl() . '/dist/attendance.css');

        if ($perm->have_perm('dozent')) {
            $this->updateTeacherWidget();
        }
    }

    private function updateTeacherWidget()
    {
        global $user;

        $now = time();
        $customParameters = Request::getArray('page_info');
        $params = $customParameters['attendance_widget_teacher'] ?? [];

        $sessionId = $params['active-session-id'] ?? null;
        $knownEntries = $params['known-entries'] ?? [];
        $courseDate = $params['course-date'] ?? null;
        $isExpired = $courseDate && $now >= ($courseDate['end_time'] ?? 0);

        $changedEntries = [];

        if ($sessionId) {
            $session = AttendanceSession::find($sessionId);
            if ($session) {
                $changedEntries = TeacherWidgetHelper::getUpdatedEntriesForSession($session, $knownEntries);
            }
        }

        if (!$courseDate) {
            $updateSession = true;
        } elseif ($isExpired) {
            $updateSession = (bool) TeacherWidgetHelper::getNextTerminIdForUser($user);
        } else {
            $updateSession = false;
        }

        \UpdateInformation::setInformation('attendance_widget_teacher', [
            'status' => 'ok',
            'update-next-session' => $updateSession,
            'update-entries' => !empty($changedEntries),
            'updated-entries' => $changedEntries,
        ]);
    }

    public function perform($unconsumedPath)
    {
        parent::perform($unconsumedPath);
    }

    public function getPluginName(): string
    {
        return _('Anwesenheitserfassung');
    }

    public function getInfoTemplate($courseId)
    {
        return null;
    }

    public function getPortalTemplate()
    {
        global $perm, $user;

        $template_path = $this->getPluginPath() . '/templates';
        $template_factory = new Flexi_TemplateFactory($template_path);
        if ($perm->have_perm('dozent')) {
            $this->updateTeacherWidget();
            PageLayout::addScript($this->getPluginUrl() . '/dist/studip-attendance-widget-teacher.js', [
                'type' => 'module',
                'rel' => 'preload',
            ]);
            $template = $template_factory->open('widget_teacher');
            $template->preferredLanguage = str_replace('_', '-', $_SESSION['_language']);
            $template->userId = $user->id;
            return $template;
        }

        PageLayout::addScript($this->getPluginUrl() . '/dist/studip-attendance-widget-student.js', [
            'type' => 'module',
            'rel' => 'preload',
        ]);
        $template = $template_factory->open('widget_student');
        $template->preferredLanguage = str_replace('_', '-', $_SESSION['_language']);
        $template->userId = $user->id;
        return $template;
    }
}
