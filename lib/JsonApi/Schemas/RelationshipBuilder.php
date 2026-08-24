<?php
/**
 * Relationship Builder class
 *
 * This class replicates the Relationship Builder from Core v6.3, allowing us to minimize future compatibility efforts and simplify the migration process.
 *
 * @package   StudipAttendance\JsonApi\Schemas
 * @since     0.1.0
 * @author    Farbod Zamani <zamani@elan-ev.de>
 * @copyright 2026 elan e.V.
 * @license   GPL-3.0 WITH License-Supplement (see LICENSE-SUPPLEMENT.txt)
 * @link      https://elan-ev.de
 * @see       https://gitlab.studip.de/studip/studip/-/work_items/6595
 */

namespace StudipAttendance\JsonApi\Schemas;

use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class RelationshipBuilder
{
    protected array $relationships = [];
    protected SchemaProvider $schema;
    protected $resource;
    protected ContextInterface $context;

    public function __construct(
        SchemaProvider $schema,
        $resource,
        ContextInterface $context
    ) {
        $this->schema = $schema;
        $this->resource = $resource;
        $this->context = $context;
    }

    /**
     * Add a relationship for a SimpleORMap relation.
     *
     * @param string $name      relationship name
     * @param string $relation  SimpleORMap relation to use
     * @param bool $link        true: add relationship link (self link)
     * @param mixed $meta       non-standard meta-information (optional)
     */
    public function addRelationship(string $name, string $relation, bool $link = false, $meta = null): void
    {
        if (!($this->resource instanceof \SimpleORMap)) {
            throw new InternalServerError(__METHOD__ . ' can only be used with resources that are SimpleORMap objects');
        }

        $include = $this->schema->shouldInclude($this->context, $name);
        $options = $this->resource->getRelationOptions($relation);

        if ($include) {
            $related = $this->resource->getValue($relation);
        } else if ($options['type'] === 'belongs_to') {
            $callable = $options['assoc_func_params_func'];
            $related_id = $callable($this->resource);

            if ($related_id) {
                $related = $options['class_name']::build(['id' => $related_id], false);
            } else {
                $related = null;
            }
        } else {
            $related = false;
        }

        $this->relationships[$name] = $this->buildRelationship($related, $link, $meta);
    }

    /**
     * Add a relationship with the given data. Allowed data types are:
     *
     * iterable: always include this data as linkage (to-many relationship)
     * object: always include this data as linkage (to-one relationship)
     * null: empty to-one relationship (no related resource link)
     * false: never include data for this relationship, just related resource link
     * callable: use callback to provide data (as above), but only if include is requested
     *
     * @param string $name      relationship name
     * @param mixed $related    relationship data
     * @param bool $link        true: add relationship link (self link)
     * @param mixed $meta       non-standard meta-information (optional)
     */
    public function addRelationshipData(string $name, $related = false, bool $link = false, $meta = null): void
    {
        $include = $this->schema->shouldInclude($this->context, $name);

        if ($include && $related === false) {
            throw new BadRequestException(sprintf('Include path %s is not allowed.', $name));
        }

        if ($related instanceof \Closure) {
            $related = $include ? $related($this->resource) : false;
        }

        $this->relationships[$name] = $this->buildRelationship($related, $link, $meta);
    }

    /**
     * Checks if data is from a to-many relationship (array or collection).
     *
     * @param mixed $related    relationship data
     */
    protected function is_iterable($related): bool
    {
        return is_array($related) || $related instanceof \SimpleCollection;
    }

    /**
     * Build relationship array from relationship data.
     *
     * @param mixed $related    relationship data
     * @param bool $link        true: add relationship link (self link)
     * @param mixed $meta       non-standard meta-information (optional)
     */
    protected function buildRelationship($related, bool $link = false, $meta = null): array
    {
        $result = [];

        if ($link) {
            $result[SchemaProvider::RELATIONSHIP_LINKS_SELF] = true;
        }
        if ($related === false || $this->is_iterable($related)) {
            $result[SchemaProvider::RELATIONSHIP_LINKS_RELATED] = true;
        } else if (is_object($related)) {
            /** @disregard P1006 Expected type is irrelevant. */
            $result[SchemaProvider::RELATIONSHIP_LINKS][Link::RELATED] = $this->schema->createLinkToResource($related);
        }
        if ($related !== false) {
            $result[SchemaProvider::RELATIONSHIP_DATA] = $related;
        }
        if ($meta) {
            $result[SchemaProvider::RELATIONSHIP_META] = $meta;
        }

        return $result;
    }

    /**
     * @return array list of collected relationships.
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }
}
