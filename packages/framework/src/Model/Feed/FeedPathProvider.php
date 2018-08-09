<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class FeedPathProvider
{
    /**
     * @var string
     */
    protected $feedUrlPrefix;

    /**
     * @var string
     */
    protected $feedDir;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    public function __construct(string $feedUrlPrefix, string $feedDir, Setting $setting)
    {
        $this->feedUrlPrefix = $feedUrlPrefix;
        $this->feedDir = $feedDir;
        $this->setting = $setting;
    }

    public function getFeedUrl(FeedInfoInterface $feedInfo, DomainConfig $domainConfig)
    {
        return $domainConfig->getUrl() . $this->feedUrlPrefix . $this->getFeedFilename($feedInfo, $domainConfig);
    }

    public function getFeedFilepath(FeedInfoInterface $feedInfo, DomainConfig $domainConfig)
    {
        return $this->feedDir . $this->getFeedFilename($feedInfo, $domainConfig);
    }

    protected function getFeedFilename(FeedInfoInterface $feedInfo, DomainConfig $domainConfig)
    {
        $feedHash = $this->setting->get(Setting::FEED_HASH);

        return $feedHash . '_' . $feedInfo->getName() . '_' . $domainConfig->getId() . '.xml';
    }
}
