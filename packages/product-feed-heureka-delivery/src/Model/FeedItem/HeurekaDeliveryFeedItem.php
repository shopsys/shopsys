<?php

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class HeurekaDeliveryFeedItem implements FeedItemInterface
{
    /**
     * @param int $id
     * @param int $stockQuantity
     */
    public function __construct(protected readonly int $id, protected readonly int $stockQuantity)
    {
    }

    /**
     * @return int
     */
    public function getSeekId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStockQuantity(): int
    {
        return $this->stockQuantity;
    }
}
