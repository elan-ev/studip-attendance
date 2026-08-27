<?php
/**
 * Authority
 *
 * Main class to handle action validity checks.
 *
 * @package   StudipAttendance\JsonApi\Routes
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Routes;

use User;

use StudipAttendance\Helpers\Utils;

class Authority
{
    ###############################################
    #####     Public Route perm checkers     ######
    ###############################################

    public static function canCreateSession(User $user, string $courseId): bool
    {
        return Utils::isTeacher($user, $courseId);
    }

    public static function canUpdateSession(User $user, string $courseId): bool
    {
        return Utils::isTeacher($user, $courseId);
    }

    public static function canDeleteSession(User $user, string $courseId): bool
    {
        return Utils::isRoot($user) || Utils::isCourseAdmin($user, $courseId);
    }

    public static function canIndexSession(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canGenerateSessionTOTP(User $user, string $courseId): bool
    {
        return Utils::isTeacher($user, $courseId);
    }

    public static function canIndexCourseSession(User $user, string $courseId): bool
    {
        return Utils::isTeacher($user, $courseId);
    }

    public static function canShowSession(User $user, string $courseId): bool
    {
        return Utils::isTeacher($user, $courseId);
    }

    public static function canCreateAuditLog(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canIndexAuditLog(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canShowAuditLog(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canCreateEntry(User $user, string $courseId): bool
    {
        return Utils::isAutor($user, $courseId);
    }

    public static function canDeleteEntry(User $user, string $courseId): bool
    {
        return Utils::isTeacher($user, $courseId);
    }

    public static function canUpdateEntry(User $user, string $courseId): bool
    {
        return Utils::isTeacher($user, $courseId);
    }

    public static function canIndexEntry(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canIndexSessionEntry(User $user, string $courseId): bool
    {
        return Utils::isTeacher($user, $courseId);
    }

    public static function canShowEntry(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canShowSessionEntry(User $user, string $courseId): bool
    {
        return Utils::isTeacher($user, $courseId);
    }

    public static function canCreateReport(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canDeleteReport(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canUpdateReport(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canShowReport(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canIndexReport(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canCreateThreshold(User $user, string $courseId): bool
    {
        return Utils::isTeacher( $user, $courseId);
    }

    public static function canDeleteThreshold(User $user, string $courseId): bool
    {
        return Utils::isTeacher( $user, $courseId);
    }

    public static function canUpdateThreshold(User $user, string $courseId): bool
    {
        return Utils::isTeacher( $user, $courseId);
    }

    public static function canShowThreshold(User $user, string $courseId): bool
    {
        return Utils::isTeacher( $user, $courseId);
    }

    public static function canIndexThreshold(User $user): bool
    {
        return Utils::isRoot( $user);
    }

    public static function canCreateViewPreset(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canDeleteViewPreset(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canUpdateViewPreset(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canShowViewPreset(User $user): bool
    {
        return Utils::isRoot($user);
    }

    public static function canIndexViewPreset(User $user): bool
    {
        return Utils::isRoot($user);
    }
}
