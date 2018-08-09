<?php

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class HeurekaDeliveryFeedItem implements FeedItemInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $stockQuantity;

    public function __construct(int $id, int $stockQuantity)
    {
        $this->id = $id;
        $this->stockQuantity = $stockQuantity;
    }

    public function getSeekId(): int
    {
        return $this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStockQuantity(): int
    {
        return $this->stockQuantity;
    }
}
