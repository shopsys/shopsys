<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\MergadoBundle;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class MergadoFeedInfo implements FeedInfoInterface
{
    /**
     * @return string
     */
    #[Override]
    public function getLabel(): string
    {
        return 'Mergado';
    }

    /**
     * @return string
     */
    #[Override]
    public function getName(): string
    {
        return 'mergado';
    }

    /**
     * @return string|null
     */
    #[Override]
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
