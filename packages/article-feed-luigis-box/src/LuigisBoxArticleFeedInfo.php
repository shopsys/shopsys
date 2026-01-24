<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxArticleFeedInfo implements FeedInfoInterface
{
    #[Override]
    public function getLabel(): string
    {
        return 'Luigi\'s Box Article';
    }

    #[Override]
    public function getName(): string
    {
        return 'luigis-box-article';
    }

    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
