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

    public function getFeedUrl(
        FeedInfoInterface $feedInfo,
        DomainConfig $domainConfig,
        ?string $currencyCode = null,
    ): string {
        return $domainConfig->getBaseUrl() . $this->feedUrlPrefix . $this->getFeedFilename($feedInfo, $domainConfig, $currencyCode);
    }

    public function getFeedFilepath(
        FeedInfoInterface $feedInfo,
        DomainConfig $domainConfig,
        ?string $currencyCode = null,
    ): string {
        return $this->feedDir . $this->getFeedFilename($feedInfo, $domainConfig, $currencyCode);
    }

    public function getFeedLocalFilepath(
        FeedInfoInterface $feedInfo,
        DomainConfig $domainConfig,
        ?string $currencyCode = null,
    ): string {
        return $this->projectDir . $this->feedDir . $this->getFeedFilename($feedInfo, $domainConfig, $currencyCode);
    }

    /**
     * The domain default currency keeps the historical filename so the feed URLs registered at aggregators keep working
     */
    protected function getFeedFilename(
        FeedInfoInterface $feedInfo,
        DomainConfig $domainConfig,
        ?string $currencyCode = null,
    ): string {
        $feedHash = $this->setting->get(Setting::FEED_HASH);
        $filename = $feedHash . '_' . $feedInfo->getName() . '_' . $domainConfig->getId();

        if ($currencyCode !== null && $currencyCode !== $domainConfig->getDefaultCurrencyCode()) {
            $filename .= '_' . strtolower($currencyCode);
        }

        return $filename . '.xml';
    }
}
