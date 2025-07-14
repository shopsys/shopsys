<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class ZboziFeedInfo implements FeedInfoInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLabel(): string
    {
        return 'Zboží.cz';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getName(): string
    {
        return 'zbozi';
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
