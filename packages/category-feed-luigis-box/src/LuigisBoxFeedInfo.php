<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\LuigisBoxBundle;

use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'Luigi\'s Box Category';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'luigis-box-category';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
