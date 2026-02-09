<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class FeedPathProvider
{
    public function __construct(
        protected readonly string $feedUrlPrefix,
        protected readonly string $feedDir,
        protected readonly string $projectDir,
        protected readonly Setting $setting,
    ) {
    }

    public function getFeedUrl(FeedInfoInterface $feedInfo, DomainConfig $domainConfig): string
    {
        return $domainConfig->getBaseUrl() . $this->feedUrlPrefix . $this->getFeedFilename($feedInfo, $domainConfig);
    }

    public function getFeedFilepath(FeedInfoInterface $feedInfo, DomainConfig $domainConfig): string
    {
        return $this->feedDir . $this->getFeedFilename($feedInfo, $domainConfig);
    }

    public function getFeedLocalFilepath(FeedInfoInterface $feedInfo, DomainConfig $domainConfig): string
    {
        return $this->projectDir . $this->feedDir . $this->getFeedFilename($feedInfo, $domainConfig);
    }

    protected function getFeedFilename(FeedInfoInterface $feedInfo, DomainConfig $domainConfig): string
    {
        $feedHash = $this->setting->get(Setting::FEED_HASH);

        return $feedHash . '_' . $feedInfo->getName() . '_' . $domainConfig->getId() . '.xml';
    }
}
