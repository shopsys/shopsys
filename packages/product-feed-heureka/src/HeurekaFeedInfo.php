<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class HeurekaFeedInfo implements FeedInfoInterface
{
    #[Override]
    public function getLabel(): string
    {
        return 'Heureka';
    }

    #[Override]
    public function getName(): string
    {
        return 'heureka';
    }

    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
