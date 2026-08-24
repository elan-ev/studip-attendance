<?php
/**
 * Attendance Threshold JSON API Schema
 *
 * @package   StudipAttendance\JsonApi\Schemas
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 */

namespace StudipAttendance\JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class AttendanceThresholdSchema extends \JsonApi\Schemas\SchemaProvider
{
    /**
     * Type of schema.
     * {@inheritdoc}
     */
    public const TYPE = 'attendance-thresholds';

    /**
     * Resource Type.
     * {@inheritdoc}
     */
    protected string $resourceType = self::TYPE;

    const REL_COURSE = 'course';

    /**
     * {@inheritdoc}
     */
    public function getId($resource): ?string
    {
        return $resource->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'unexcused-warning-percent' => (int) $resource['unexcused_warning_percent'],
            'unexcused-critical-percent' => (int) $resource['unexcused_critical_percent'],
            'absence-days-warning' => (int) $resource['absence_days_warning'],
            'absence-days-critical' => (int) $resource['absence_days_critical'],
            'absence-weeks-warning' => (int) $resource['absence_weeks_warning'],
            'absence-weeks-critical' => (int) $resource['absence_weeks_critical'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        // The method "getRelationshipBuilder" is available from StudIP v6.3.
        if (method_exists($this, 'getRelationshipBuilder')) {
            $builder = $this->getRelationshipBuilder($resource, $context);
        } else { // Otherwise, we use the local builder.
            $builder = new RelationshipBuilder($this, $resource, $context);
        }

        $builder->addRelationship(self::REL_COURSE, 'course');

        return $builder->getRelationships();
    }

    /**
     * @inheritdoc
     */
    public function hasResourceMeta($resource): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceMeta($resource)
    {
        return [];
    }
}
