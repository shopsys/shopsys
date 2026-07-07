<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use DateTimeImmutable;

class OrderFilter
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]|null $statuses
     */
    public function __construct(
        protected ?DateTimeImmutable $createdAfter = null,
        protected ?DateTimeImmutable $createdBefore = null,
        protected ?array $statuses = null,
        protected ?string $orderItemsCatnum = null,
        protected ?string $orderItemsProductUuid = null,
        protected ?string $search = null,
    ) {
    }

    public function getCreatedAfter(): ?DateTimeImmutable
    {
        return $this->createdAfter;
    }

    public function getCreatedBefore(): ?DateTimeImmutable
    {
        return $this->createdBefore;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]|null
     */
    public function getStatuses(): ?array
    {
        return $this->statuses;
    }

    public function getOrderItemsCatnum(): ?string
    {
        return $this->orderItemsCatnum;
    }

    public function getOrderItemsProductUuid(): ?string
    {
        return $this->orderItemsProductUuid;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }
}
