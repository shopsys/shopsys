<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Transform;

use Closure;
use LogicException;
use Shopsys\AdministrationBundle\Component\Crud\Helper\CrudEntityIdentifierExtractor;

/**
 * Creates datagrid field transforms that load the data of all rows of the page in one query and memoize it,
 * instead of querying once per rendered row; a row the loader returns nothing for gets null
 */
class BatchLoadedRowTransformFactory
{
    protected const string DEFAULT_ROW_IDENTIFIER = 'id';

    public function __construct(
        protected readonly CrudEntityIdentifierExtractor $crudEntityIdentifierExtractor,
    ) {
    }

    /**
     * @param \Closure(int[]): array<int, mixed> $loadValuesIndexedByRowId Loads the values of the given row identifiers at once, indexed by the row identifier
     * @param string $rowIdentifier The datagrid identifier (see Datagrid::setIdentifier()) the rows are keyed by
     * @return \Closure(mixed, mixed[], mixed[][]): mixed
     */
    public function createFromIndexedLoader(
        Closure $loadValuesIndexedByRowId,
        string $rowIdentifier = self::DEFAULT_ROW_IDENTIFIER,
    ): Closure {
        $valuesIndexedByRowId = null;

        return static function (mixed $value, array $row, array $results) use ($loadValuesIndexedByRowId, $rowIdentifier, &$valuesIndexedByRowId): mixed {
            if (!array_key_exists($rowIdentifier, $row)) {
                throw new LogicException(sprintf(
                    'The datagrid row has no "%s" identifier, pass the identifier set by Datagrid::setIdentifier() to the batch loaded transform.',
                    $rowIdentifier,
                ));
            }

            if ($valuesIndexedByRowId === null) {
                $rowIds = array_map(intval(...), array_column($results, $rowIdentifier));
                $valuesIndexedByRowId = $loadValuesIndexedByRowId($rowIds);
            }

            return $valuesIndexedByRowId[(int)$row[$rowIdentifier]] ?? null;
        };
    }

    /**
     * @template T of object
     * @param \Closure(int[]): T[] $loadEntitiesByIds Loads the entities of the given row identifiers at once
     * @param (\Closure(T): mixed)|null $mapEntityToValue Maps the loaded entity to the field value, the entity itself is the value when omitted
     * @param string $rowIdentifier The datagrid identifier (see Datagrid::setIdentifier()) the rows are keyed by
     * @return \Closure(mixed, mixed[], mixed[][]): mixed
     */
    public function createFromEntitiesLoader(
        Closure $loadEntitiesByIds,
        ?Closure $mapEntityToValue = null,
        string $rowIdentifier = self::DEFAULT_ROW_IDENTIFIER,
    ): Closure {
        return $this->createFromIndexedLoader(function (array $rowIds) use ($loadEntitiesByIds, $mapEntityToValue): array {
            $valuesIndexedByRowId = [];
            $entities = $loadEntitiesByIds($rowIds);

            foreach ($entities as $entity) {
                $entityId = $this->crudEntityIdentifierExtractor->getId($entity);
                $valuesIndexedByRowId[$entityId] = $mapEntityToValue === null ? $entity : $mapEntityToValue($entity);
            }

            return $valuesIndexedByRowId;
        }, $rowIdentifier);
    }
}
