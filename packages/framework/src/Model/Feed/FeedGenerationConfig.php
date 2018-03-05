<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

class FeedGenerationConfig
{
    /**
     * @var string|null
     */
    private $feedName;

    /**
     * @var int|null
     */
    private $domainId;

    /**
     * @var int|null
     */
    private $feedItemId;

    /**
     * @param string|null $feedName
     * @param string|null $domainId
     * @param int|null $feedItemId
     */
    public function __construct($feedName, $domainId, $feedItemId = null)
    {
        $this->feedName = $feedName;
        $this->domainId = $domainId;
        $this->feedItemId = $feedItemId;
    }

    /**
     * @return string|null
     */
    public function getFeedName()
    {
        return $this->feedName;
    }

    /**
     * @return int|null
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return int|null
     */
    public function getFeedItemId()
    {
        return $this->feedItemId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedGenerationConfig $feedGenerationConfig
     * @return bool
     */
    public function isSameFeedAndDomain(self $feedGenerationConfig)
    {
        return $this->feedName === $feedGenerationConfig->feedName && $this->domainId === $feedGenerationConfig->domainId;
    }
}
