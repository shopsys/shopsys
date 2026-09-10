<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use DateTimeImmutable;

class OrderItemsFilter
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]|null $orderStatuses
     * @param string[]|null $excludeProductTypes
     */
    public function __construct(
        protected ?string $orderUuid = null,
        protected ?DateTimeImmutable $orderCreatedAfter = null,
        protected ?array $orderStatuses = null,
        protected ?string $catnum = null,
        protected ?string $productUuid = null,
        protected ?string $type = null,
        protected ?array $excludeProductTypes = null,
    ) {
    }

    public function getOrderUuid(): ?string
    {
        return $this->orderUuid;
    }

    public function getOrderCreatedAfter(): ?DateTimeImmutable
    {
        return $this->orderCreatedAfter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]|null
     */
    public function getOrderStatuses(): ?array
    {
        return $this->orderStatuses;
    }

    public function getCatnum(): ?string
    {
        return $this->catnum;
    }

    public function getProductUuid(): ?string
    {
        return $this->productUuid;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string[]|null
     */
    public function getExcludeProductTypes(): ?array
    {
        return $this->excludeProductTypes;
    }
}
