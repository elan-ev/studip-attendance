<?php

/**
 * Helper class for StudipAttendance.
 *
 * @package   StudipAttendance\Helper
 * @since     0.2.0
 * @author    Ron Lucke <lucke@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Helper;

use Helpbar;
use Icon;

class AttributionHelper
{
    public static function addLicenseToHelpbar()
    {
        $helpbar = Helpbar::get();
        
        $helpbar->addPlainText('', _('Mit diesem Plugin können Sie...'));

        $helpbar->addPlainText('', _('Dieses Plugin wurde vom elan e.V. entwickelt. Es steht unter der GNU GPLv3 mit Zusatzbedingungen gemäß Abschnitt 7. Der Quellcode ist öffentlich zugänglich.'));
        
        $helpbar->addLink('GPLv3 License', 'https://www.gnu.org/licenses/gpl-3.0.html', Icon::create('link-extern', Icon::ROLE_INFO_ALT), '_blank');
        $helpbar->addLink('GitHub-Repository', 'https://github.com/elan-ev/studip-attendance', Icon::create('link-extern', Icon::ROLE_INFO_ALT), '_blank');
        $helpbar->addLink('elan e.V.', 'https://elan-ev.de', Icon::create('link-extern', Icon::ROLE_INFO_ALT), '_blank');
    }
}