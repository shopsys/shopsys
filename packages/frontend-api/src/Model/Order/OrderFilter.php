<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use DateTimeImmutable;

class OrderFilter
{
    /**
     * @param \DateTimeImmutable|null $createdAfter
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]|null $statuses
     * @param string|null $orderItemsCatnum
     * @param string|null $orderItemsProductUuid
     */
    public function __construct(
        protected ?DateTimeImmutable $createdAfter = null,
        protected ?array $statuses = null,
        protected ?string $orderItemsCatnum = null,
        protected ?string $orderItemsProductUuid = null,
    ) {
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAfter(): ?DateTimeImmutable
    {
        return $this->createdAfter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]|null
     */
    public function getStatuses(): ?array
    {
        return $this->statuses;
    }

    /**
     * @return string|null
     */
    public function getOrderItemsCatnum(): ?string
    {
        return $this->orderItemsCatnum;
    }

    /**
     * @return string|null
     */
    public function getOrderItemsProductUuid(): ?string
    {
        return $this->orderItemsProductUuid;
    }
}
