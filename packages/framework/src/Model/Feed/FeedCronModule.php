<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class FeedCronModule implements IteratedCronModuleInterface
{
    protected Logger $logger;

    protected ?FeedExportCreationDataQueue $feedExportCreationDataQueue = null;

    protected ?FeedExport $currentFeedExport = null;

    protected bool $areFeedsScheduled = false;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedFacade $feedFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedModuleRepository $feedModuleRepository
     */
    public function __construct(
        protected readonly FeedFacade $feedFacade,
        protected readonly Domain $domain,
        protected readonly Setting $setting,
        protected readonly FeedModuleRepository $feedModuleRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function iterate(): bool
    {
        if ($this->areFeedsScheduled === false) {
            $this->feedFacade->scheduleFeedsForCurrentTime();
            $this->areFeedsScheduled = true;
        }

        if ($this->getFeedExportCreationDataQueue()->isEmpty()) {
            $this->logger->debug('Queue is empty, no feeds to process.');

            return false;
        }

        if ($this->currentFeedExport === null) {
            $this->currentFeedExport = $this->createCurrentFeedExport();
        }

        $this->currentFeedExport->generateBatch();

        if ($this->currentFeedExport->isFinished()) {
            $feedInfo = $this->currentFeedExport->getFeedInfo();
            $domainConfig = $this->currentFeedExport->getDomainConfig();

            $currentFeedModule = $this->feedModuleRepository->getFeedModuleByNameAndDomainId(
                $this->getFeedExportCreationDataQueue()->getCurrentFeedName(),
                $this->getFeedExportCreationDataQueue()->getCurrentDomain()->getId(),
            );
            $this->feedFacade->markFeedModuleAsUnscheduled($currentFeedModule);

            $this->logger->debug(sprintf(
                'Feed "%s" generated on domain "%s" into "%s".',
                $feedInfo->getName(),
                $domainConfig->getName(),
                $this->feedFacade->getFeedFilepath($feedInfo, $domainConfig),
            ));

            $this->currentFeedExport = null;

            $existsNext = $this->getFeedExportCreationDataQueue()->next();

            if ($existsNext === true) {
                $this->currentFeedExport = $this->createCurrentFeedExport();
            }

            return $existsNext;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(): void
    {
        $lastSeekId = $this->currentFeedExport !== null ? $this->currentFeedExport->getLastSeekId() : null;

        if ($lastSeekId !== null) {
            $this->currentFeedExport->sleep();
        }

        $currentFeedName = $this->getFeedExportCreationDataQueue()->getCurrentFeedName();
        $currentDomain = $this->getFeedExportCreationDataQueue()->getCurrentDomain();

        $this->setting->set(Setting::FEED_NAME_TO_CONTINUE, $currentFeedName);
        $this->setting->set(Setting::FEED_DOMAIN_ID_TO_CONTINUE, $currentDomain->getId());
        $this->setting->set(Setting::FEED_ITEM_ID_TO_CONTINUE, $lastSeekId);

        $this->logger->debug(sprintf(
            'Going to sleep... Will continue with feed "%s" on "%s", processing from ID %d.',
            $currentFeedName,
            $currentDomain->getName(),
            $lastSeekId,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function wakeUp(): void
    {
        $feedNameToContinue = $this->setting->get(Setting::FEED_NAME_TO_CONTINUE);
        $domainIdToContinue = $this->setting->get(Setting::FEED_DOMAIN_ID_TO_CONTINUE);

        if ($feedNameToContinue !== null && $domainIdToContinue !== null) {
            $queue = $this->getFeedExportCreationDataQueue();

            while (
                $feedNameToContinue !== $queue->getCurrentFeedName()
                || $domainIdToContinue !== $queue->getCurrentDomain()->getId()
            ) {
                $queue->next();
            }
        }

        $lastSeekId = $this->setting->get(Setting::FEED_ITEM_ID_TO_CONTINUE);
        $this->currentFeedExport = $this->createCurrentFeedExport($lastSeekId);
        $this->currentFeedExport->wakeUp();

        $this->logger->debug(sprintf(
            'Waking up... Continuing with feed "%s" on "%s", processing from ID %d.',
            $this->getFeedExportCreationDataQueue()->getCurrentFeedName(),
            $this->getFeedExportCreationDataQueue()->getCurrentDomain()->getName(),
            $this->currentFeedExport->getLastSeekId(),
        ));
    }

    /**
     * @param int|null $lastSeekId
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedExport
     */
    protected function createCurrentFeedExport(?int $lastSeekId = null): FeedExport
    {
        return $this->feedFacade->createFeedExport(
            $this->getFeedExportCreationDataQueue()->getCurrentFeedName(),
            $this->getFeedExportCreationDataQueue()->getCurrentDomain(),
            $lastSeekId,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedExportCreationDataQueue
     */
    protected function getFeedExportCreationDataQueue(): FeedExportCreationDataQueue
    {
        if ($this->feedExportCreationDataQueue === null) {
            $this->feedExportCreationDataQueue = new FeedExportCreationDataQueue(
                $this->feedModuleRepository->getAllScheduledFeedModules(),
                $this->domain->getAll(),
            );
        }

        return $this->feedExportCreationDataQueue;
    }
}
