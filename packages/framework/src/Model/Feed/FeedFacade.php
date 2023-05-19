<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToRetrieveMetadata;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class FeedFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedRegistry $feedRegistry
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedExportFactory $feedExportFactory
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedPathProvider $feedPathProvider
     * @param \League\Flysystem\FilesystemOperator $filesystem
     */
    public function __construct(
        protected readonly FeedRegistry $feedRegistry,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly FeedExportFactory $feedExportFactory,
        protected readonly FeedPathProvider $feedPathProvider,
        protected readonly FilesystemOperator $filesystem,
    ) {
    }

    /**
     * @param string $feedName
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     */
    public function generateFeed(string $feedName, DomainConfig $domainConfig): void
    {
        $feedExport = $this->createFeedExport($feedName, $domainConfig);

        while (!$feedExport->isFinished()) {
            $feedExport->generateBatch();
        }
    }

    /**
     * @param string $feedName
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedExport
     */
    public function createFeedExport(string $feedName, DomainConfig $domainConfig, ?int $lastSeekId = null): FeedExport
    {
        /*
         * Product is visible, when it has at least one visible category.
         * Hiding a category therefore could cause change of product's visibility but the visibility recalculation is not invoked immediately,
         * so we need to recalculate product's visibility here in order to get consistent data for feed generation.
         */
        $this->productVisibilityFacade->refreshProductsVisibilityForMarked();

        $feed = $this->feedRegistry->getFeedByName($feedName);

        return $this->feedExportFactory->create($feed, $domainConfig, $lastSeekId);
    }

    /**
     * @param string|null $feedType
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface[]
     */
    public function getFeedsInfo(?string $feedType = null): array
    {
        $feeds = $feedType === null ? $this->feedRegistry->getAllFeeds() : $this->feedRegistry->getFeeds($feedType);

        $feedsInfo = [];

        foreach ($feeds as $feed) {
            $feedsInfo[] = $feed->getInfo();
        }

        return $feedsInfo;
    }

    /**
     * @param string|null $feedType
     * @return string[]
     */
    public function getFeedNames(?string $feedType = null): array
    {
        $feedNames = [];

        foreach ($this->getFeedsInfo($feedType) as $feedInfo) {
            $feedNames[] = $feedInfo->getName();
        }

        return $feedNames;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    public function getFeedUrl(FeedInfoInterface $feedInfo, DomainConfig $domainConfig): string
    {
        return $this->feedPathProvider->getFeedUrl($feedInfo, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    public function getFeedFilepath(FeedInfoInterface $feedInfo, DomainConfig $domainConfig): string
    {
        return $this->feedPathProvider->getFeedFilepath($feedInfo, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface $feedInfo
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return int|null
     */
    public function getFeedTimestamp(FeedInfoInterface $feedInfo, DomainConfig $domainConfig): ?int
    {
        $filePath = $this->feedPathProvider->getFeedFilepath($feedInfo, $domainConfig);

        try {
            return $this->filesystem->lastModified($filePath);
        } catch (UnableToRetrieveMetadata $fileNotFundException) {
            return null;
        }
    }
}
