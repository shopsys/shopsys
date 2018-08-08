<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

interface FeedItemInterface
{
    /**
     * Returns an identifier that is used for batch generation.
     * @see \Shopsys\FrameworkBundle\Model\Feed\FeedInterface::getItems()
     */
    public function getSeekId(): int;
}
