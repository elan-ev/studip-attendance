<?php
StudipAutoloader::addAutoloadPath(__DIR__ . '/lib/Helpers', 'StudipAttendance\\Helpers');
StudipAutoloader::addAutoloadPath(__DIR__ . '/lib/JsonApi', 'StudipAttendance\\JsonApi');
StudipAutoloader::addAutoloadPath(__DIR__ . '/lib/Models', 'StudipAttendance\\Models');
StudipAutoloader::addAutoloadPath(__DIR__ . '/lib/Events', 'StudipAttendance\\Events');

// Observers.
NotificationCenter::addObserver('StudipAttendance\Events\Observers', 'subscribeToCourseDateCreation', 'CourseDateDidCreate');

// TODO: find out if we are dealing with "Ex-CourseDates" as well? WTF :D
// NotificationCenter::addObserver('StudipAttendance\Events\Observers', 'subscribeToCourseDateCreation', 'CourseExDateDidCreate');
