<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class DailyFeedCronModule implements IteratedCronModuleInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedFacade
     */
    private $feedFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedExportCreationDataQueue
     */
    private $feedExportCreationDataQueue;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedExport|null
     */
    private $currentFeedExport;

    public function __construct(FeedFacade $feedFacade, Domain $domain, Setting $setting)
    {
        $this->feedFacade = $feedFacade;
        $this->domain = $domain;
        $this->setting = $setting;
        $this->feedExportCreationDataQueue = new FeedExportCreationDataQueue(
            $this->feedFacade->getFeedNames('daily'),
            $this->domain->getAll()
        );
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
        if ($this->feedExportCreationDataQueue->isEmpty()) {
            $this->logger->addDebug('Queue is empty, no feeds to process.');

            return false;
        }

        if ($this->currentFeedExport === null) {
            $this->currentFeedExport = $this->createCurrentFeedExport();
        }

        $this->currentFeedExport->generateBatch();

        if ($this->currentFeedExport->isFinished()) {
            $feedInfo = $this->currentFeedExport->getFeedInfo();
            $domainConfig = $this->currentFeedExport->getDomainConfig();

            $this->logger->addDebug(sprintf(
                'Feed "%s" generated on domain "%s" into "%s".',
                $feedInfo->getName(),
                $domainConfig->getName(),
                $this->feedFacade->getFeedFilepath($feedInfo, $domainConfig)
            ));

            $this->currentFeedExport = null;

            return $this->feedExportCreationDataQueue->next();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sleep(): void
    {
        $currentFeedName = $this->feedExportCreationDataQueue->getCurrentFeedName();
        $currentDomain = $this->feedExportCreationDataQueue->getCurrentDomain();
        $lastSeekId = $this->currentFeedExport !== null ? $this->currentFeedExport->getLastSeekId() : null;

        $this->setting->set(Setting::FEED_NAME_TO_CONTINUE, $currentFeedName);
        $this->setting->set(Setting::FEED_DOMAIN_ID_TO_CONTINUE, $currentDomain->getId());
        $this->setting->set(Setting::FEED_ITEM_ID_TO_CONTINUE, $lastSeekId);

        $this->logger->addDebug(sprintf(
            'Going to sleep... Will continue with feed "%s" on "%s", processing from ID %d.',
            $currentFeedName,
            $currentDomain->getName(),
            $lastSeekId
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
            $queue = $this->feedExportCreationDataQueue;
            while ($feedNameToContinue !== $queue->getCurrentFeedName() || $domainIdToContinue !== $queue->getCurrentDomain()->getId()) {
                $queue->next();
            }
        }

        $lastSeekId = $this->setting->get(Setting::FEED_ITEM_ID_TO_CONTINUE);
        $this->currentFeedExport = $this->createCurrentFeedExport($lastSeekId);

        $this->logger->addDebug(sprintf(
            'Waking up... Continuing with feed "%s" on "%s", processing from ID %d.',
            $this->feedExportCreationDataQueue->getCurrentFeedName(),
            $this->feedExportCreationDataQueue->getCurrentDomain()->getName(),
            $this->currentFeedExport->getLastSeekId()
        ));
    }

    /**
     * @param int|null $lastSeekId
     */
    private function createCurrentFeedExport(int $lastSeekId = null): FeedExport
    {
        return $this->feedFacade->createFeedExport(
            $this->feedExportCreationDataQueue->getCurrentFeedName(),
            $this->feedExportCreationDataQueue->getCurrentDomain(),
            $lastSeekId
        );
    }
}
