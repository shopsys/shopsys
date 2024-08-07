<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use DateTime;

class OrderItemsFilter
{
    /**
     * @param string|null $orderUuid
     * @param \DateTime|null $orderCreatedAfter
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]|null $orderStatuses
     * @param string|null $catnum
     * @param string|null $productUuid
     * @param string|null $type
     */
    public function __construct(
        protected ?string $orderUuid = null,
        protected ?DateTime $orderCreatedAfter = null,
        protected ?array $orderStatuses = null,
        protected ?string $catnum = null,
        protected ?string $productUuid = null,
        protected ?string $type = null,
    ) {
    }

    /**
     * @return string|null
     */
    public function getOrderUuid(): ?string
    {
        return $this->orderUuid;
    }

    /**
     * @return \DateTime|null
     */
    public function getOrderCreatedAfter(): ?DateTime
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

    /**
     * @return string|null
     */
    public function getCatnum(): ?string
    {
        return $this->catnum;
    }

    /**
     * @return string|null
     */
    public function getProductUuid(): ?string
    {
        return $this->productUuid;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}
