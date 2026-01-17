<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\BatchLoad;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductBatchLoadByEntityData
{
    public function __construct(
        protected readonly string $id,
        protected readonly object $entity,
        protected readonly int $limit,
        protected readonly int $offset,
        protected readonly string $orderingModeId,
        protected readonly ProductFilterData $productFilterData,
        protected readonly string $search = '',
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @template T of object
     * @param class-string<T>|null $entityClassName
     * @return T
     */
    public function getEntity(?string $entityClassName = null): object
    {
        return $this->entity;
    }

    public function getProductFilterData(): ProductFilterData
    {
        return $this->productFilterData;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getOrderingModeId(): string
    {
        return $this->orderingModeId;
    }

    public function getSearch(): string
    {
        return $this->search;
    }
}
