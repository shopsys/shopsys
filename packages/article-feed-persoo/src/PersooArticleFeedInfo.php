<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\PersooBundle;

use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class PersooArticleFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'Persoo Article';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'persoo-article';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
