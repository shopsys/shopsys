<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use DateTime;

class OrderFilter
{
    /**
     * @param \DateTime|null $createdAfter
     * @param int|null $status
     */
    public function __construct(
        protected ?DateTime $createdAfter = null,
        protected ?int $status = null,
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
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }
}
