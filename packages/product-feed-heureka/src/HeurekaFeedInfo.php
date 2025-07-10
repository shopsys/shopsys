<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class HeurekaFeedInfo implements FeedInfoInterface
{
    /**
     * @return string
     */
    #[Override]
    public function getLabel(): string
    {
        return 'Heureka';
    }

    /**
     * @return string
     */
    #[Override]
    public function getName(): string
    {
        return 'heureka';
    }

    /**
     * @return string|null
     */
    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
