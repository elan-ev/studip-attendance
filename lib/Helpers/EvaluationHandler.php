<?php
/**
 * Evaluation Handler Class
 *
 * @package   StudipAttendance\Helpers
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\Helpers;

use CourseDate;
use SimpleCollection;
use User;

use StudipAttendance\Models\AttendanceEntry;
use StudipAttendance\Models\AttendanceSession;
use StudipAttendance\Models\AttendanceThreshold;


class EvaluationHandler
{
    private string $courseId;
    private array $dates = [];
    private SimpleCollection $datesCollection;
    private array $sessions = [];
    private SimpleCollection $sessionsCollection;
    private array $recordedTerminIds = [];

    public function __construct(string $courseId) {
        $this->courseId = $courseId;
        $this->dates = CourseDate::findBySeminar_id($courseId);
        $this->datesCollection = SimpleCollection::createFromArray($this->dates);
        $this->sessions = AttendanceSession::findBySeminar_id($courseId);
        $this->sessionsCollection = SimpleCollection::createFromArray($this->sessions);
        $this->recordedTerminIds = $this->sessionsCollection->pluck('termin_id');
    }

    public function getUserAttendanceData(string $userId): ?array
    {
        if (!$this->isCourseMember($userId)) {
            return null;
        }

        $presents = [];
        $unexcusedAbsents = [];
        $excusedAbsents = [];

        $noSessions = [];
        $future = [];
        $cancelled = [];
        $active = null; // TODO: decide whether there is a need for this!?

        if (!empty($this->dates)) {
            foreach($this->dates as $cd) {
                $terminId = $cd->termin_id;

                // If the session is not recorded, it means no attendance was expected!
                if (!in_array($terminId, $this->recordedTerminIds)) {
                    $noSessions[] = $terminId;
                    continue;
                }

                $session = $this->sessionsCollection->findOneBy('termin_id', $terminId);

                if ($session->status !== AttendanceSession::STATUS_DRAFT) {
                    $future[] = $terminId;
                    continue;
                }

                if ($session->status !== AttendanceSession::STATUS_ACTIVE) {
                    $active = $terminId;
                    continue;
                }

                if ($session->status !== AttendanceSession::STATUS_DELETED) {
                    $cancelled[] = $terminId;
                    continue;
                }

                $entry = AttendanceEntry::getUserRecordInSession($session->id, $userId);

                if (empty($entry)) {
                    $unexcusedAbsents[] = $terminId;
                    continue;
                }

                switch ($entry->status) {
                    case AttendanceEntry::STATUS_PRESENT:
                        $presents[] = $terminId;
                        break;
                    case AttendanceEntry::STATUS_EXCUSED:
                        $excusedAbsents[] = $terminId;
                        break;
                    case AttendanceEntry::STATUS_ABSENT:
                    default:
                        $unexcusedAbsents[] = $terminId;
                        break;
                }
            }
        }

        $cancelledSessions = $this->sessionsCollection->findBy('status', AttendanceSession::STATUS_DELETED)->pluck('termin_id');

        $cancelled = array_unique(array_merge($cancelled, $cancelledSessions));

        // We deduct no sessions from total, as there was no attendance expected.
        $total = count($this->dates) - count($noSessions);

        return [
            'total' => $total,
            'presents' => count($presents),
            'unexcused_absents' => count($unexcusedAbsents),
            'excused_absents' => count($excusedAbsents),
            'future' => count($future),
            'cancelled' => count($cancelled),
            'termin_ids' => [
                'presents' => $presents,
                'unexcused_absents' => $unexcusedAbsents,
                'excused_absents' => $excusedAbsents,
                'future' => $future,
                'cancelled' => $cancelled,
            ],
        ];
    }

    public function evaluateUserAttendance(string $userId): ?array
    {
        $data = $this->getUserAttendanceData($userId);

        $threshold = AttendanceThreshold::findBySeminar_id($this->courseId);

        // As long as we don't have any threshold record, we return null (no valid result)!
        if (empty($threshold)) {
            return null;
        }

        $absentDaysLevel = AttendanceThreshold::THRESHOLD_LEVEL_NORMAL;
        $unexcusedAbsentLevel = AttendanceThreshold::THRESHOLD_LEVEL_NORMAL;

        if ($data['total'] > 0) {

            $totalAbsents = $data['unexcused_absents'] + $data['excused_absents'];
            $absentPer = (int) floor(($totalAbsents / $data['total']) * 100);

            if (
                (int) $threshold->absence_days_critical > 0 &&
                (int) $threshold->absence_days_critical <= $absentPer
            ) {
                $absentDaysLevel = AttendanceThreshold::THRESHOLD_LEVEL_CRITICAL;
            } else if (
                (int) $threshold->absence_days_warning > 0 &&
                (int) $threshold->absence_days_warning <= $absentPer
            ) {
                $absentDaysLevel = AttendanceThreshold::THRESHOLD_LEVEL_WARNING;
            }

            $unexcusedPer = (int) floor(($data['unexcused_absents'] / $data['total']) * 100);

            if (
                (int) $threshold->unexcused_critical_percent > 0 &&
                (int) $threshold->unexcused_critical_percent <= $unexcusedPer
            ) {
                $unexcusedAbsentLevel = AttendanceThreshold::THRESHOLD_LEVEL_CRITICAL;
            } else if (
                (int) $threshold->unexcused_warning_percent > 0 &&
                (int) $threshold->unexcused_warning_percent <= $unexcusedPer
            ) {
                $unexcusedAbsentLevel = AttendanceThreshold::THRESHOLD_LEVEL_WARNING;
            }

            // TODO: what should we do with weeks?

        }


        return [
            'absent_days' => $absentDaysLevel,
            'unexcused' => $unexcusedAbsentLevel,
        ];
    }

    private function isCourseMember(string $userId): bool
    {
        return Utils::isAutor(User::find($userId), $this->courseId);
    }
}
