<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle;

use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxArticleFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'Luigi\'s Box Article';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'luigis-box-article';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
