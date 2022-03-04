<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\BatchLoad;

use App\Model\Product\Filter\ProductFilterData;

class ProductBatchLoadByEntityData
{
    /**
     * @var string
     */
    private string $id;

    /**
     * @var int
     */
    private int $entityId;

    /**
     * @var string
     */
    private string $entityClass;

    /**
     * @var int
     */
    private int $limit;

    /**
     * @var int
     */
    private int $offset;

    /**
     * @var \App\Model\Product\Filter\ProductFilterData
     */
    private ProductFilterData $productFilterData;

    /**
     * @var string
     */
    private string $orderingModeId;

    /**
     * @var string
     */
    private string $search;

    /**
     * @param string $id
     * @param int $entityId
     * @param string $entityClass
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $search
     */
    public function __construct(
        string $id,
        int $entityId,
        string $entityClass,
        int $limit,
        int $offset,
        string $orderingModeId,
        ProductFilterData $productFilterData,
        string $search
    ) {
        $this->id = $id;
        $this->entityId = $entityId;
        $this->entityClass = $entityClass;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->orderingModeId = $orderingModeId;
        $this->productFilterData = $productFilterData;
        $this->search = $search;
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
     * @return \App\Model\Product\Filter\ProductFilterData
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
