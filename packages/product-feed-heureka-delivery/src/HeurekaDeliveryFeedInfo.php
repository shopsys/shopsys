<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class HeurekaDeliveryFeedInfo implements FeedInfoInterface
{
    #[Override]
    public function getLabel(): string
    {
        return 'Heureka - delivery';
    }

    #[Override]
    public function getName(): string
    {
        return 'heureka_delivery';
    }

    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
