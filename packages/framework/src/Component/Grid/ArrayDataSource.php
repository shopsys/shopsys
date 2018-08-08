<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class ArrayDataSource implements DataSourceInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $rowIdSourceColumnName;

    public function __construct(array $data, string $rowIdSourceColumnName = null)
    {
        $this->data = $data;
        $this->rowIdSourceColumnName = $rowIdSourceColumnName;
    }

    public function getRowIdSourceColumnName(): string
    {
        return $this->rowIdSourceColumnName;
    }

    /**
     * @return mixed
     */
    public function getOneRow(string $rowId)
    {
        if ($this->rowIdSourceColumnName === null) {
            return $this->data[$rowId];
        } else {
            foreach ($this->data as $item) {
                if ($item[$this->rowIdSourceColumnName] === $rowId) {
                    return $item;
                }
            }
        }
    }

    /**
     * @param null $limit
     * @param null $orderSourceColumnName
     */
    public function getPaginatedRows($limit = null, int $page = 1, $orderSourceColumnName = null, string $orderDirection = self::ORDER_ASC): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
    {
        if ($limit !== null) {
            $message = 'Pagination not supported in ArrayDataSource';
            throw new \Shopsys\FrameworkBundle\Component\Grid\Exception\PaginationNotSupportedException($message);
        }

        if ($orderSourceColumnName !== null) {
            $message = 'Ordering not supported in ArrayDataSource';
            throw new \Shopsys\FrameworkBundle\Component\Grid\Exception\OrderingNotSupportedException($message);
        }

        return new PaginationResult(1, count($this->data), count($this->data), $this->data);
    }

    public function getTotalRowsCount(): int
    {
        return count($this->data);
    }
}
