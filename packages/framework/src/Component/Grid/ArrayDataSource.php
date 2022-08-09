<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Exception;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Grid\Exception\OrderingNotSupportedException;
use Shopsys\FrameworkBundle\Component\Grid\Exception\PaginationNotSupportedException;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

/**
 * @template T of array<string, mixed>
 * @implements \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface<T>
 */
class ArrayDataSource implements DataSourceInterface
{
    /**
     * @var T[]
     */
    protected $data;

    /**
     * @var string|null
     */
    protected $rowIdSourceColumnName;

    /**
     * @param T[] $data
     * @param string|null $rowIdSourceColumnName
     */
    public function __construct(array $data, ?string $rowIdSourceColumnName = null)
    {
        $this->data = $data;
        $this->rowIdSourceColumnName = $rowIdSourceColumnName;
    }

    /**
     * @return string
     */
    public function getRowIdSourceColumnName(): string
    {
        if ($this->rowIdSourceColumnName === null) {
            throw new RuntimeException(self::class . ' has not set `RowIdSourceColumnName`');
        }

        return $this->rowIdSourceColumnName;
    }

    /**
     * @param int $rowId
     * @return array<string, mixed>
     */
    public function getOneRow(int $rowId): array
    {
        if ($this->rowIdSourceColumnName === null) {
            return $this->data[$rowId];
        }
        foreach ($this->data as $item) {
            if ($item[$this->rowIdSourceColumnName] === $rowId) {
                return $item;
            }
        }

        throw new Exception('Row does not found.');
    }

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string|null $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    public function getPaginatedRows(?int $limit = null, int $page = 1, ?string $orderSourceColumnName = null, ?string $orderDirection = self::ORDER_ASC): PaginationResult
    {
        if ($limit !== null) {
            $message = 'Pagination not supported in ArrayDataSource';
            throw new PaginationNotSupportedException($message);
        }

        if ($orderSourceColumnName !== null) {
            $message = 'Ordering not supported in ArrayDataSource';
            throw new OrderingNotSupportedException($message);
        }

        return new PaginationResult(1, count($this->data), count($this->data), $this->data);
    }

    /**
     * @return int
     */
    public function getTotalRowsCount(): int
    {
        return count($this->data);
    }
}
