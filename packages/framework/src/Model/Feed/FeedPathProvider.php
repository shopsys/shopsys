<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class FeedPathProvider
{
    /**
     * @param string $feedUrlPrefix
     * @param string $feedDir
     * @param string $projectDir
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly string $feedUrlPrefix,
        protected readonly string $feedDir,
        protected readonly string $projectDir,
        protected readonly Setting $setting,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    public function getFeedUrl(FeedInfoInterface $feedInfo, DomainConfig $domainConfig)
    {
        return $domainConfig->getUrl() . $this->feedUrlPrefix . $this->getFeedFilename($feedInfo, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    public function getFeedFilepath(FeedInfoInterface $feedInfo, DomainConfig $domainConfig)
    {
        return $this->feedDir . $this->getFeedFilename($feedInfo, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    public function getFeedLocalFilepath(FeedInfoInterface $feedInfo, DomainConfig $domainConfig)
    {
        return $this->projectDir . $this->feedDir . $this->getFeedFilename($feedInfo, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    protected function getFeedFilename(FeedInfoInterface $feedInfo, DomainConfig $domainConfig)
    {
        $feedHash = $this->setting->get(Setting::FEED_HASH);

        return $feedHash . '_' . $feedInfo->getName() . '_' . $domainConfig->getId() . '.xml';
    }
}
