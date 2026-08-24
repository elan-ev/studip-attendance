<?php
/**
 * Attendance Entry JSON API Schema
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
use StudipAttendance\Models\AttendanceEntry;

class AttendanceEntrySchema extends \JsonApi\Schemas\SchemaProvider
{
    /**
     * Type of schema.
     * {@inheritdoc}
     */
    public const TYPE = 'attendance-entries';

    /**
     * Resource Type.
     * {@inheritdoc}
     */
    protected string $resourceType = self::TYPE;

    const REL_USER = 'user';
    const REL_SESSION = 'session';

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
            'status'  => $resource['status'],
            'source' => $resource['source'],
            'comment' => $resource['comment'],
            'teacher_input_reason' => $resource['teacher_input_reason'],
            'late' => (int) $resource['late'],
            'left-early' => (int) $resource['left_early'],
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

        $builder->addRelationship(self::REL_USER, 'user');
        $builder->addRelationship(self::REL_SESSION, 'session');

        return $builder->getRelationships();
    }

    /**
     * @inheritdoc
     */
    public function hasResourceMeta($resource): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceMeta($resource)
    {
        return [
            'statuses' => AttendanceEntry::STATUSES,
            'sources' => AttendanceEntry::SOURCES,
        ];
    }
}
