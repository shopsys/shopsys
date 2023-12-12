<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\PersooBundle;

use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class PersooFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return 'Persoo Product';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'persoo-product';
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
