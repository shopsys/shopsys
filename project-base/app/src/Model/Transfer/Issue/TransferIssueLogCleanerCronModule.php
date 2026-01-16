<?php

declare(strict_types=1);

namespace App\Model\Transfer\Issue;

use Psr\Log\LoggerInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class TransferIssueLogCleanerCronModule implements SimpleCronModuleInterface
{
    private LoggerInterface $logger;

    /**
     * @param \App\Model\Transfer\Issue\TransferIssueRepository $transferIssueRepository
     */
    public function __construct(
        private readonly TransferIssueRepository $transferIssueRepository,
    ) {
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $this->logger->info('Start clear transfer issue table');
        $this->transferIssueRepository->deleteOldTransferIssues();
        $this->logger->info('End of clear transfer issue table');
    }
}
