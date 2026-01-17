<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssue;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueDataFactory;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade;

class TransferLogger implements TransferLoggerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueData[]
     */
    protected array $transferIssueDataList = [];

    public function __construct(
        protected readonly Logger $logger,
        protected readonly string $serviceTransferIdentifier,
        protected readonly TransferIssueFacade $transferIssueFacade,
        protected readonly TransferIssueDataFactory $transferIssueDataFactory,
    ) {
    }

    #[Override]
    public function persistAllLoggedTransferIssues(): void
    {
        $transferIssuesCount = count($this->transferIssueDataList);

        if ($transferIssuesCount === 0) {
            return;
        }

        $this->transferIssueFacade->saveTransferIssues($this->transferIssueDataList, $this->serviceTransferIdentifier);
        $this->transferIssueDataList = [];
        $this->info('Transfer logger saves ' . $transferIssuesCount . ' to database');
    }

    #[Override]
    public function debug(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = $this->transferIssueDataFactory->create($message, TransferIssue::SEVERITY_ERROR);

        $this->logger->debug($message, $context);
    }

    #[Override]
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    #[Override]
    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    #[Override]
    public function warning(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = $this->transferIssueDataFactory->create($message, TransferIssue::SEVERITY_WARNING);

        $this->logger->warning($message, $context);
    }

    #[Override]
    public function error(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = $this->transferIssueDataFactory->create($message, TransferIssue::SEVERITY_ERROR);

        $this->logger->error($message, $context);
    }

    #[Override]
    public function critical(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = $this->transferIssueDataFactory->create($message, TransferIssue::SEVERITY_CRITICAL);

        $this->logger->critical($message, $context);
    }

    #[Override]
    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    #[Override]
    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    #[Override]
    public function close(): void
    {
        $this->logger->close();
    }

    #[Override]
    public function reset(): void
    {
        $this->logger->reset();
    }
}
