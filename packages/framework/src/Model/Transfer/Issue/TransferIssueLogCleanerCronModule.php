<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer\Issue;

use Monolog\Logger;
use Override;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class TransferIssueLogCleanerCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    public function __construct(
        protected readonly TransferIssueRepository $transferIssueRepository,
    ) {
    }

    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function run(): void
    {
        $this->logger->info('Start clear transfer issue table');
        $this->transferIssueRepository->deleteOldTransferIssues();
        $this->logger->info('End of clear transfer issue table');
    }
}
