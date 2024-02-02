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
        return 'LuigisBox Article';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'luigisBox-article';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
