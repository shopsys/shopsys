<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use DateTime;

class OrderItemsFilter
{
    /**
     * @param string|null $orderUuid
     * @param \DateTime|null $orderCreatedAfter
     * @param int|null $orderStatus
     * @param string|null $catnum
     * @param string|null $productUuid
     * @param string|null $type
     */
    public function __construct(
        protected ?string $orderUuid = null,
        protected ?DateTime $orderCreatedAfter = null,
        protected ?int $orderStatus = null,
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
     * @return int|null
     */
    public function getOrderStatus(): ?int
    {
        return $this->orderStatus;
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
