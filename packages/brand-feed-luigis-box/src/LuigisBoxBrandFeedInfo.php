<?php

declare(strict_types=1);

namespace Shopsys\BrandFeed\LuigisBoxBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxBrandFeedInfo implements FeedInfoInterface
{
    #[Override]
    public function getLabel(): string
    {
        return 'Luigi\'s Box Brand';
    }

    #[Override]
    public function getName(): string
    {
        return 'luigis-box-brand';
    }

    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
