<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToRetrieveMetadata;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class FeedFacade
{
    public function __construct(
        protected readonly FeedRegistry $feedRegistry,
        protected readonly FeedExportFactory $feedExportFactory,
        protected readonly FeedPathProvider $feedPathProvider,
        protected readonly FilesystemOperator $filesystem,
        protected readonly FeedModuleRepository $feedModuleRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly FeedCurrencyResolver $feedCurrencyResolver,
    ) {
    }

    /**
     * Null currency code generates the feed in every currency it is configured for
     */
    public function generateFeed(string $feedName, DomainConfig $domainConfig, ?string $currencyCode = null): void
    {
        $currencyCodes = $currencyCode !== null ? [$currencyCode] : $this->getCurrencyCodesForFeed($feedName, $domainConfig);

        foreach ($currencyCodes as $feedCurrencyCode) {
            $feedExport = $this->createFeedExport($feedName, $domainConfig, null, $feedCurrencyCode);

            while (!$feedExport->isFinished()) {
                $feedExport->generateBatch();
            }
        }
    }

    public function createFeedExport(
        string $feedName,
        DomainConfig $domainConfig,
        ?int $lastSeekId = null,
        ?string $currencyCode = null,
    ): FeedExport {
        $feedConfig = $this->feedRegistry->getFeedConfigByName($feedName);

        return $this->feedExportFactory->create($feedConfig->getFeed(), $domainConfig, $lastSeekId, $currencyCode);
    }

    /**
     * @return string[]
     */
    public function getCurrencyCodesForFeed(string $feedName, DomainConfig $domainConfig): array
    {
        return $this->feedCurrencyResolver->resolveCurrencyCodes(
            $this->feedRegistry->getFeedConfigByName($feedName),
            $domainConfig,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface[]
     */
    public function getFeedsInfo(bool $onlyForCurrentTime = false): array
    {
        $feedConfigs = $onlyForCurrentTime ? $this->feedRegistry->getFeedConfigsForCurrentTime() : $this->feedRegistry->getAllFeedConfigs();

        $feedsInfo = [];

        foreach ($feedConfigs as $feedConfig) {
            $feedsInfo[] = $feedConfig->getFeed()->getInfo();
        }

        return $feedsInfo;
    }

    /**
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

    public function getFeedUrl(
        FeedInfoInterface $feedInfo,
        DomainConfig $domainConfig,
        ?string $currencyCode = null,
    ): string {
        return $this->feedPathProvider->getFeedUrl($feedInfo, $domainConfig, $currencyCode);
    }

    public function getFeedFilepath(
        FeedInfoInterface $feedInfo,
        DomainConfig $domainConfig,
        ?string $currencyCode = null,
    ): string {
        return $this->feedPathProvider->getFeedFilepath($feedInfo, $domainConfig, $currencyCode);
    }

    public function getFeedTimestamp(
        FeedInfoInterface $feedInfo,
        DomainConfig $domainConfig,
        ?string $currencyCode = null,
    ): ?int {
        $filePath = $this->feedPathProvider->getFeedFilepath($feedInfo, $domainConfig, $currencyCode);

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

    public function scheduleFeedByName(string $name): void
    {
        $feedConfigsToSchedule = [$this->feedRegistry->getFeedConfigByName($name)];

        $this->markFeedConfigsForScheduling($feedConfigsToSchedule);
    }

    public function scheduleFeedByNameAndDomainId(string $name, int $domainId): void
    {
        $feedModule = $this->feedModuleRepository->getFeedModuleByNameAndDomainId($name, $domainId);
        $feedModule->schedule();
        $this->em->flush();
    }

    public function markFeedModuleAsUnscheduled(FeedModule $feedModule): void
    {
        $feedModule->unschedule();
        $this->em->flush();
    }

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
