<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

interface FeedInfoInterface
{
    /**
     * Returns human readable label to identify this product feed.
     */
    public function getLabel(): string;

    /**
     * Returns unique name to identify this product feed.
     */
    public function getName(): string;

    /**
     * May return additional information about the product feed for the administrator.
     */
    public function getAdditionalInformation(): ?string;
}
