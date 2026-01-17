<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle\Model\FeedItem;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class HeurekaDeliveryFeedItem implements FeedItemInterface
{
    public function __construct(protected readonly int $id, protected readonly int $stockQuantity)
    {
    }

    #[Override]
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
