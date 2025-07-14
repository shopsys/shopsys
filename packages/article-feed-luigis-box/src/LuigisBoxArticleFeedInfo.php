<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxArticleFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLabel(): string
    {
        return 'Luigi\'s Box Article';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'luigis-box-article';
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
