<?php

require_once __DIR__ . '/../lib/Helpers/CronjobRegistration.php';

final class RepairAttendanceCronjobs extends Migration
{
    public function description()
    {
        return 'Register missing attendance cronjobs and repair their installation paths.';
    }

    public function up()
    {
        \StudipAttendance\Helpers\CronjobRegistration::register();
    }

    public function down()
    {
        // These jobs belong to migration 001 and must survive this rollback.
    }
}
