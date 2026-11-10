<?php
/**
 * CourseDate JSON:API compatibility schema for Stud.IP 5.4.
 *
 * @package StudipAttendance\JsonApi\Schemas
 * @license GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 */

namespace StudipAttendance\JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class CourseDateSchema extends \JsonApi\Schemas\CourseEvent
{
    /**
     * Use CourseDate fields, matching the core CourseEvent schema from 5.5 onward.
     * The 5.4 parent supplies the resource type, ID, links and owner relationship.
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $descriptions = [];
        foreach ($resource->topics as $topic) {
            $descriptions[] = $topic->title . "\n" . $topic->description;
        }

        return [
            'title' => isset($resource->course) ? $resource->course->getFullName() : '',
            'description' => implode("\n\n", $descriptions),
            'start' => date('c', $resource->date),
            'end' => date('c', $resource->end_time),
            'type' => (int) $resource->date_typ,
            'categories' => $GLOBALS['TERMIN_TYP'][$resource->date_typ]['name'] ?? null,
            'location' => $resource->raum ?? '',
            'is-cancelled' => $resource instanceof \CourseExDate,
            'mkdate' => date('c', $resource->mkdate),
            'chdate' => date('c', $resource->chdate),
            'recurrence' => isset($resource->cycle) ? $resource->cycle->toString() : '',
        ];
    }
}
