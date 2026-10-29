<?php

/**
 * AttendanceEntryController
 *
 * Everything about attendance is provided in this controller.
 *
 * @package   StudipAttendance
 * @since     0.2.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

class IndexController extends PluginController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    public function index_action()
    {
        global $user, $perm;
        self::addLicenseToHelpbar();
        // PageLayout::disableSidebar();

        $this->preferredLanguage = str_replace('_', '-', $_SESSION['_language']);
        $this->userId = $user->id;
        $this->cid = Context::getId();
        $this->isTeacher = $perm->have_studip_perm('dozent', $this->cid, $this->userId);

        if (Navigation::hasItem('course/elanattendanceplugin')) {
            Navigation::activateItem('course/elanattendanceplugin/index');
            if ($this->isTeacher) {
                PageLayout::addScript($this->plugin->getPluginUrl() . '/dist/studip-attendance-course-teacher.js', [
                    'type' => 'module',
                    'rel' => 'preload',
                ]);
            } else {
                PageLayout::addScript($this->plugin->getPluginUrl() . '/dist/studip-attendance-course-student.js', [
                    'type' => 'module',
                    'rel' => 'preload',
                ]);
            }
        }
    }

    private static function addLicenseToHelpbar()
    {
        $helpbar = Helpbar::get();

        $helpbar->addPlainText('', _('Mit diesem Plugin können Sie...'));

        $helpbar->addPlainText('', _('Dieses Plugin wurde vom elan e.V. entwickelt. Es steht unter der GNU GPLv3 mit Zusatzbedingungen gemäß Abschnitt 7. Der Quellcode ist öffentlich zugänglich.'));

        $helpbar->addLink('GPLv3 License', 'https://www.gnu.org/licenses/gpl-3.0.html', Icon::create('link-extern', Icon::ROLE_INFO_ALT), '_blank');
        $helpbar->addLink('GitHub-Repository', 'https://github.com/elan-ev/studip-attendance', Icon::create('link-extern', Icon::ROLE_INFO_ALT), '_blank');
        $helpbar->addLink('elan e.V.', 'https://elan-ev.de', Icon::create('link-extern', Icon::ROLE_INFO_ALT), '_blank');
    }
}
