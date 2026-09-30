<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatagridRowProcessor;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Exception\RowNotFoundInGridByIdException;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

/**
 * Array counterpart of the ORM data source: properties in dot notation are resolved from nested arrays
 * into keys of the row (e.g. $row['product.id']), rows can be ordered by multiple properties and paginated
 */
final class ArrayDatagridDataSource implements DataSourceInterface
{
    /**
     * @var mixed[][]
     */
    private array $rows;

    /**
     * @param mixed[][] $data
     * @param string[] $propertyPaths
     */
    public function __construct(
        array $data,
        private readonly string $rowIdSourceColumnName,
        private readonly DatagridRowProcessor $rowProcessor,
        array $propertyPaths,
    ) {
        $this->rows = array_map(fn (array $row) => $this->resolvePropertyPaths($row, $propertyPaths), array_values($data));
    }

    #[Override]
    public function getRowIdSourceColumnName(): string
    {
        return $this->rowIdSourceColumnName;
    }

    #[Override]
    public function getOneRow(int|string $rowId): array
    {
        foreach ($this->rows as $row) {
            if (($row[$this->rowIdSourceColumnName] ?? null) === $rowId) {
                return $this->rowProcessor->process($row);
            }
        }

        throw new RowNotFoundInGridByIdException(sprintf('Row with id "%s" not found', $rowId));
    }

    #[Override]
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC,
    ): PaginationResult {
        $rows = $this->rows;

        if ($orderSourceColumnName !== null) {
            $rows = $this->orderRows($rows, explode(Datagrid::ORDER_PROPERTIES_SEPARATOR, $orderSourceColumnName), $orderDirection);
        }

        $pageSize = $limit ?? count($rows);
        $pageRows = $limit === null ? $rows : array_slice($rows, ($page - 1) * $limit, $limit);

        foreach ($pageRows as $key => $row) {
            $pageRows[$key] = $this->rowProcessor->process($row, $pageRows);
        }

        return new PaginationResult($page, $pageSize, count($rows), $pageRows);
    }

    #[Override]
    public function getTotalRowsCount(): int
    {
        return count($this->rows);
    }

    /**
     * @param mixed[][] $rows
     * @param string[] $orderProperties
     * @return mixed[][]
     */
    private function orderRows(array $rows, array $orderProperties, string $orderDirection): array
    {
        $directionMultiplier = $orderDirection === self::ORDER_DESC ? -1 : 1;

        usort($rows, static function (array $rowA, array $rowB) use ($orderProperties, $directionMultiplier): int {
            foreach ($orderProperties as $orderProperty) {
                $comparison = ($rowA[$orderProperty] ?? null) <=> ($rowB[$orderProperty] ?? null);

                if ($comparison !== 0) {
                    return $comparison * $directionMultiplier;
                }
            }

            return 0;
        });

        return $rows;
    }

    /**
     * @param mixed[] $row
     * @param string[] $propertyPaths
     * @return mixed[]
     */
    private function resolvePropertyPaths(array $row, array $propertyPaths): array
    {
        foreach ($propertyPaths as $propertyPath) {
            if (array_key_exists($propertyPath, $row)) {
                continue;
            }

            $row[$propertyPath] = $this->resolvePropertyPath($row, $propertyPath);
        }

        return $row;
    }

    /**
     * Walks the nested arrays of the row along the dot notation path, null when any part is missing
     *
     * @param mixed[] $row
     */
    private function resolvePropertyPath(array $row, string $propertyPath): mixed
    {
        $value = $row;

        foreach (explode('.', $propertyPath) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return null;
            }

            $value = $value[$part];
        }

        return $value;
    }
}
