<?php

declare(strict_types=1);

namespace App\Model\Transfer;

use App\Model\Transfer\Issue\TransferIssue;
use App\Model\Transfer\Issue\TransferIssueData;
use App\Model\Transfer\Issue\TransferIssueFacade;
use Monolog\Logger;

class TransferLogger implements TransferLoggerInterface
{
    /**
     * @var \App\Model\Transfer\Issue\TransferIssueData[]
     */
    private array $transferIssueDataList = [];

    /**
     * @param \Monolog\Logger $logger
     * @param string $serviceTransferIdentifier
     * @param \App\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade
     */
    public function __construct(
        private Logger $logger,
        private string $serviceTransferIdentifier,
        private TransferIssueFacade $transferIssueFacade,
    ) {
    }

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

    /**
     * @param string $message
     * @param mixed[] $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_ERROR);

        $this->logger->debug($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     */
    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_WARNING);

        $this->logger->warning($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_ERROR);

        $this->logger->error($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     */
    public function critical(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_CRITICAL);

        $this->logger->critical($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     */
    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * @param string $message
     * @param mixed[] $context
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function close(): void
    {
        $this->logger->close();
    }

    public function reset(): void
    {
        $this->logger->reset();
    }
}
