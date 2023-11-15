<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;
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
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedModuleRepository $feedModuleRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly FeedRegistry $feedRegistry,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly FeedExportFactory $feedExportFactory,
        protected readonly FeedPathProvider $feedPathProvider,
        protected readonly FilesystemOperator $filesystem,
        protected readonly FeedModuleRepository $feedModuleRepository,
        protected readonly EntityManagerInterface $em,
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

        $feedConfig = $this->feedRegistry->getFeedConfigByName($feedName);

        return $this->feedExportFactory->create($feedConfig->getFeed(), $domainConfig, $lastSeekId);
    }

    /**
     * @param bool $onlyForCurrentTime
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface[]
     */
    public function getFeedsInfo(bool $onlyForCurrentTime = false): array
    {
        $feedConfigs = $onlyForCurrentTime ? $this->feedRegistry->getAllFeedConfigs() : $this->feedRegistry->getFeedConfigsForCurrentTime();

        $feedsInfo = [];

        foreach ($feedConfigs as $feedConfig) {
            $feedsInfo[] = $feedConfig->getFeed()->getInfo();
        }

        return $feedsInfo;
    }

    /**
     * @param bool $onlyForCurrentTime
     * @return string[]
     */
    public function getFeedNames(bool $onlyForCurrentTime = false): array
    {
        $feedNames = [];

        foreach ($this->getFeedsInfo($onlyForCurrentTime) as $feedInfo) {
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

    public function scheduleFeedsForCurrentTime(): void
    {
        $feedConfigsToSchedule = $this->feedRegistry->getFeedConfigsForCurrentTime();

        $this->markFeedConfigsForScheduling($feedConfigsToSchedule);
    }

    public function scheduleAllFeeds(): void
    {
        $feedConfigsToSchedule = $this->feedRegistry->getAllFeedConfigs();

        $this->markFeedConfigsForScheduling($feedConfigsToSchedule);
    }

    /**
     * @param string $name
     */
    public function scheduleFeedByName(string $name): void
    {
        $feedConfigsToSchedule = [$this->feedRegistry->getFeedConfigByName($name)];

        $this->markFeedConfigsForScheduling($feedConfigsToSchedule);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedModule $feedModule
     */
    public function markFeedModuleAsUnscheduled(FeedModule $feedModule): void
    {
        $feedModule->unschedule();
        $this->em->flush();
    }

    /**
     * @param array $feedConfigsToSchedule
     */
    protected function markFeedConfigsForScheduling(array $feedConfigsToSchedule): void
    {
        foreach ($feedConfigsToSchedule as $feedConfig) {
            $feedModules = $this->feedModuleRepository->getFeedModulesByConfigIndexedByDomainId($feedConfig);

            foreach ($feedModules as $feedModule) {
                $feedModule->schedule();
            }
        }

        $this->em->flush();
    }
}
