<?php

namespace Shopsys\ProductFeed\HeurekaBundle;

use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class HeurekaFeedInfo implements FeedInfoInterface
{
    public function getLabel(): string
    {
        return 'Heureka';
    }

    public function getName(): string
    {
        return 'heureka';
    }

    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
