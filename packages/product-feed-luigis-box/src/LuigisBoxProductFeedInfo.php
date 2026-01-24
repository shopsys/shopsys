<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxProductFeedInfo implements FeedInfoInterface
{
    #[Override]
    public function getLabel(): string
    {
        return 'Luigi\'s Box Product';
    }

    #[Override]
    public function getName(): string
    {
        return 'luigis-box-product';
    }

    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
