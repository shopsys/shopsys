<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNotFoundException;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;

class FeedCronModule implements IteratedCronModuleInterface
{
    protected Logger $logger;

    protected ?FeedExportCreationDataQueue $feedExportCreationDataQueue = null;

    protected ?FeedExport $currentFeedExport = null;

    protected bool $areFeedsScheduled = false;

    public function __construct(
        protected readonly FeedFacade $feedFacade,
        protected readonly Domain $domain,
        protected readonly Setting $setting,
        protected readonly FeedModuleRepository $feedModuleRepository,
        protected readonly FeedModuleFacade $feedModuleFacade,
        protected readonly FeedRegistry $feedRegistry,
        protected readonly FeedCurrencyResolver $feedCurrencyResolver,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function iterate(): bool
    {
        if ($this->areFeedsScheduled === false) {
            $this->feedFacade->scheduleFeedsForCurrentTime();
            $this->areFeedsScheduled = true;
        }

        if ($this->getFeedExportCreationDataQueue()->isEmpty()) {
            $this->logger->info('Queue is empty, no feeds to process.');

            return false;
        }

        if ($this->currentFeedExport === null) {
            $this->currentFeedExport = $this->createCurrentFeedExport();

            if ($this->currentFeedExport === null) {
                return false;
            }

            $this->logStartOfCurrentFeedExport();
        }

        $this->currentFeedExport->generateBatch();

        if ($this->currentFeedExport->isFinished()) {
            $feedInfo = $this->currentFeedExport->getFeedInfo();
            $domainConfig = $this->currentFeedExport->getDomainConfig();
            $currencyCode = $this->currentFeedExport->getCurrencyCode();

            if ($this->getFeedExportCreationDataQueue()->isCurrentLastCurrencyOfFeedModule()) {
                $currentFeedModule = $this->feedModuleRepository->getFeedModuleByNameAndDomainId(
                    $this->getFeedExportCreationDataQueue()->getCurrentFeedName(),
                    $this->getFeedExportCreationDataQueue()->getCurrentDomain()->getId(),
                );

                $this->feedFacade->markFeedModuleAsUnscheduled($currentFeedModule);
            }
            $this->cleanSettingsValues();

            $this->logger->info(sprintf(
                'Feed "%s" generated on domain "%s" in currency "%s" into "%s".',
                $feedInfo->getName(),
                $domainConfig->getName(),
                $currencyCode,
                $this->feedFacade->getFeedFilepath($feedInfo, $domainConfig, $currencyCode),
            ));

            $this->currentFeedExport = null;

            $existsNext = $this->getFeedExportCreationDataQueue()->next();

            if ($existsNext === true) {
                $this->currentFeedExport = $this->createCurrentFeedExport();

                if ($this->currentFeedExport === null) {
                    return false;
                }

                $this->logStartOfCurrentFeedExport();
            }

            return $existsNext;
        }

        return true;
    }

    protected function logStartOfCurrentFeedExport(): void
    {
        $this->logger->info(sprintf(
            'Started generation of feed "%s" generated on domain "%s" in currency "%s" into "%s".',
            $this->currentFeedExport->getFeedInfo()->getName(),
            $this->currentFeedExport->getDomainConfig()->getName(),
            $this->currentFeedExport->getCurrencyCode(),
            $this->feedFacade->getFeedFilepath($this->currentFeedExport->getFeedInfo(), $this->currentFeedExport->getDomainConfig(), $this->currentFeedExport->getCurrencyCode()),
        ));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function sleep(): void
    {
        $lastSeekId = $this->currentFeedExport !== null ? $this->currentFeedExport->getLastSeekId() : null;

        if ($lastSeekId !== null) {
            $this->currentFeedExport->sleep();
        }

        $currentFeedName = $this->getFeedExportCreationDataQueue()->getCurrentFeedName();
        $currentDomain = $this->getFeedExportCreationDataQueue()->getCurrentDomain();
        $currentCurrencyCode = $this->getFeedExportCreationDataQueue()->getCurrentCurrencyCode();

        $this->setting->set(Setting::FEED_NAME_TO_CONTINUE, $currentFeedName);
        $this->setting->set(Setting::FEED_DOMAIN_ID_TO_CONTINUE, $currentDomain->getId());
        $this->setting->set(Setting::FEED_CURRENCY_CODE_TO_CONTINUE, $currentCurrencyCode);
        $this->setting->set(Setting::FEED_ITEM_ID_TO_CONTINUE, $lastSeekId);

        $this->logger->info(sprintf(
            'Going to sleep... Will continue with feed "%s" on "%s" in currency "%s", processing from ID %d.',
            $currentFeedName,
            $currentDomain->getName(),
            $currentCurrencyCode,
            $lastSeekId,
        ));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function wakeUp(): void
    {
        $feedNameToContinue = $this->setting->get(Setting::FEED_NAME_TO_CONTINUE);
        $domainIdToContinue = $this->setting->get(Setting::FEED_DOMAIN_ID_TO_CONTINUE);
        $currencyCodeToContinue = $this->setting->get(Setting::FEED_CURRENCY_CODE_TO_CONTINUE);

        if ($feedNameToContinue !== null && $domainIdToContinue !== null) {
            $queue = $this->getFeedExportCreationDataQueue();

            while (
                $queue->isEmpty() === false && (
                    $feedNameToContinue !== $queue->getCurrentFeedName() ||
                    $domainIdToContinue !== $queue->getCurrentDomain()->getId() ||
                    ($currencyCodeToContinue !== null && $currencyCodeToContinue !== $queue->getCurrentCurrencyCode())
                )
            ) {
                $queue->next();
            }

            if ($queue->isEmpty()) {
                $this->cleanSettingsValues();

                return;
            }
        }


        $lastSeekId = $this->setting->get(Setting::FEED_ITEM_ID_TO_CONTINUE);
        $this->currentFeedExport = $this->createCurrentFeedExport($lastSeekId);

        if ($this->currentFeedExport === null) {
            return;
        }

        $this->currentFeedExport->wakeUp();

        $this->logger->info(sprintf(
            'Waking up... Continuing with feed "%s" on "%s", processing from ID %d.',
            $this->getFeedExportCreationDataQueue()->getCurrentFeedName(),
            $this->getFeedExportCreationDataQueue()->getCurrentDomain()->getName(),
            $this->currentFeedExport->getLastSeekId(),
        ));
    }

    protected function createCurrentFeedExport(?int $lastSeekId = null): ?FeedExport
    {
        try {
            $feedExport = $this->feedFacade->createFeedExport(
                $this->getFeedExportCreationDataQueue()->getCurrentFeedName(),
                $this->getFeedExportCreationDataQueue()->getCurrentDomain(),
                $lastSeekId,
                $this->getFeedExportCreationDataQueue()->getCurrentCurrencyCode(),
            );
        } catch (FeedNotFoundException $e) {
            $this->logger->error($e->getMessage());

            $this->feedModuleFacade->deleteFeedCronModulesByName($this->getFeedExportCreationDataQueue()->getCurrentFeedName());

            $isNextFeedInQueue = $this->getFeedExportCreationDataQueue()->next();

            if ($isNextFeedInQueue === false) {
                return null;
            }

            return $this->createCurrentFeedExport();
        }

        return $feedExport;
    }

    protected function getFeedExportCreationDataQueue(): FeedExportCreationDataQueue
    {
        if ($this->feedExportCreationDataQueue === null) {
            $this->feedExportCreationDataQueue = new FeedExportCreationDataQueue(
                $this->feedModuleRepository->getAllScheduledFeedModules(),
                $this->domain->getAll(),
                $this->feedRegistry,
                $this->feedCurrencyResolver,
            );
        }

        return $this->feedExportCreationDataQueue;
    }

    protected function cleanSettingsValues(): void
    {
        $this->setting->set(Setting::FEED_NAME_TO_CONTINUE, null);
        $this->setting->set(Setting::FEED_DOMAIN_ID_TO_CONTINUE, null);
        $this->setting->set(Setting::FEED_CURRENCY_CODE_TO_CONTINUE, null);
        $this->setting->set(Setting::FEED_ITEM_ID_TO_CONTINUE, null);
    }
}
