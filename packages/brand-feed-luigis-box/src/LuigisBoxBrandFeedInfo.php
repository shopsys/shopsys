<?php

declare(strict_types=1);

namespace Shopsys\BrandFeed\LuigisBoxBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxBrandFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLabel(): string
    {
        return 'Luigi\'s Box Brand';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'luigis-box-brand';
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
