<?php

declare(strict_types=1);

namespace Shopsys\BrandFeed\LuigisBoxBundle;

use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxBrandFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'Luigi\'s Box Brand';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'luigis-box-brand';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
