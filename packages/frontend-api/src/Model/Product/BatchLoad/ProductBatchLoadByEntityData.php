<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductBatchLoadByEntityData
{
    /**
     * @param string $id
     * @param int $entityId
     * @param string $entityClass
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $search
     */
    public function __construct(
        protected readonly string $id,
        protected readonly int $entityId,
        protected readonly string $entityClass,
        protected readonly int $limit,
        protected readonly int $offset,
        protected readonly string $orderingModeId,
        protected readonly ProductFilterData $productFilterData,
        protected readonly string $search = '',
    ) {
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function getProductFilterData(): ProductFilterData
    {
        return $this->productFilterData;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return string
     */
    public function getOrderingModeId(): string
    {
        return $this->orderingModeId;
    }

    /**
     * @return string
     */
    public function getSearch(): string
    {
        return $this->search;
    }
}
