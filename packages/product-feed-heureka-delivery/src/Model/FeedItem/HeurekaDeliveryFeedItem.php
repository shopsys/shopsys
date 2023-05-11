<?php

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class HeurekaDeliveryFeedItem implements FeedItemInterface
{
    protected int $id;

    protected int $stockQuantity;

    /**
     * @param int $id
     * @param int $stockQuantity
     */
    public function __construct(int $id, int $stockQuantity)
    {
        $this->id = $id;
        $this->stockQuantity = $stockQuantity;
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
