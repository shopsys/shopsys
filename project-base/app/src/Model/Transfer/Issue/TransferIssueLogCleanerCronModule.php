<?php

declare(strict_types=1);

namespace App\Model\Transfer\Issue;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class TransferIssueLogCleanerCronModule implements SimpleCronModuleInterface
{
    private Logger $logger;

    /**
     * @param \App\Model\Transfer\Issue\TransferIssueRepository $transferIssueRepository
     */
    public function __construct(private TransferIssueRepository $transferIssueRepository)
    {
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
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
