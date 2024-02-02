<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\LuigisBoxBundle;

use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'LuigisBox Product';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'luigisBox-product';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
