<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\LuigisBoxBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class LuigisBoxCategoryFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLabel(): string
    {
        return 'Luigi\'s Box Category';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'luigis-box-category';
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
