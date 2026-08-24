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

class ElanAttendancePlugin extends StudIPPlugin implements SystemPlugin, JsonApiPlugin
{
    use Routes;
    use Schemas;

    public function __construct()
    {
        parent::__construct();
    }

    public function perform($unconsumedPath)
    {
        parent::perform($unconsumedPath);
    }

    public function getPluginName(): string
    {
        return _('ElanAttendancePlugin');
    }

    public function getInfoTemplate($courseId)
    {
        return null;
    }
}
