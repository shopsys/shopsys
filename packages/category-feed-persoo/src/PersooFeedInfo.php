<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\PersooBundle;

use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class PersooFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'Persoo Category';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'persoo-category';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
