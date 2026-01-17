<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\MergadoBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class MergadoFeedInfo implements FeedInfoInterface
{
    #[Override]
    public function getLabel(): string
    {
        return 'Mergado';
    }

    #[Override]
    public function getName(): string
    {
        return 'mergado';
    }

    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
