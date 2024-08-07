<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use DateTime;

class OrderFilter
{
    /**
     * @param \DateTime|null $createdAfter
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]|null $statuses
     * @param string|null $orderItemsCatnum
     * @param string|null $orderItemsProductUuid
     */
    public function __construct(
        protected ?DateTime $createdAfter = null,
        protected ?array $statuses = null,
        protected ?string $orderItemsCatnum = null,
        protected ?string $orderItemsProductUuid = null,
    ) {
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAfter(): ?DateTime
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
