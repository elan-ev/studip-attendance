<?php
StudipAutoloader::addAutoloadPath(__DIR__ . '/lib/Helpers', 'StudipAttendance\\Helpers');
StudipAutoloader::addAutoloadPath(__DIR__ . '/lib/JsonApi', 'StudipAttendance\\JsonApi');
StudipAutoloader::addAutoloadPath(__DIR__ . '/lib/Models', 'StudipAttendance\\Models');
StudipAutoloader::addAutoloadPath(__DIR__ . '/lib/Events', 'StudipAttendance\\Events');

// Observers.
NotificationCenter::addObserver(
    'StudipAttendance\Events\Observers',
    'subscribeToCourseDateCreation',
    'CourseDateDidCreate'
);
// This would only work in StudIP 5.x!
NotificationCenter::addObserver(
    'StudipAttendance\Events\Observers',
    'subscribeToCourseDidChangeSchedule',
    'CourseDidChangeSchedule'
);
// This would only work in StudIP 6.x onward.
NotificationCenter::addObserver(
    'StudipAttendance\Events\Observers',
    'subscribeToCourseDateDeletion',
    'CourseDateDidDelete'
);

