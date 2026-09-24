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

class ElanAttendancePlugin extends StudIPPlugin implements SystemPlugin, JsonApiPlugin, PortalPlugin
{
    use Routes;
    use Schemas;

    public function __construct()
    {
        parent::__construct();
        PageLayout::addStylesheet($this->getPluginUrl() . '/dist/attendance.css');
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
        return $template_factory->open('widget_student');
    }
}
