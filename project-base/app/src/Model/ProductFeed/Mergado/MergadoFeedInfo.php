<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Mergado;

use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;

class MergadoFeedInfo implements FeedInfoInterface
{
    /**
     * @return string
     */
    public function getLabel(): string
    {
        return  'Mergado';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'mergado';
    }

    /**
     * @return string|null
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }
}
