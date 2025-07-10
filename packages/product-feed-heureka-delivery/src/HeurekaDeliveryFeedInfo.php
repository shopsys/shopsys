<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaDeliveryBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class HeurekaDeliveryFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLabel(): string
    {
        return 'Heureka - delivery';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'heureka_delivery';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
