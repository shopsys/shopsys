<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\LuigisBoxBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxCategoryFeedInfo implements FeedInfoInterface
{
    #[Override]
    public function getLabel(): string
    {
        return 'Luigi\'s Box Category';
    }

    #[Override]
    public function getName(): string
    {
        return 'luigis-box-category';
    }

    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
