<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

use Monolog\Logger;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssue;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueDataFactory;
use Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade;

class TransferLogger implements TransferLoggerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueData[]
     */
    protected array $transferIssueDataList = [];

    /**
     * @param \Monolog\Logger $logger
     * @param string $serviceTransferIdentifier
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade
     * @param \Shopsys\FrameworkBundle\Model\Transfer\Issue\TransferIssueDataFactory $transferIssueDataFactory
     */
    public function __construct(
        protected readonly Logger $logger,
        protected readonly string $serviceTransferIdentifier,
        protected readonly TransferIssueFacade $transferIssueFacade,
        protected readonly TransferIssueDataFactory $transferIssueDataFactory,
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
     * @param array $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = $this->transferIssueDataFactory->create($message, TransferIssue::SEVERITY_ERROR);

        $this->logger->debug($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = $this->transferIssueDataFactory->create($message, TransferIssue::SEVERITY_WARNING);

        $this->logger->warning($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = $this->transferIssueDataFactory->create($message, TransferIssue::SEVERITY_ERROR);

        $this->logger->error($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function critical(string $message, array $context = []): void
    {
        $this->transferIssueDataList[] = $this->transferIssueDataFactory->create($message, TransferIssue::SEVERITY_CRITICAL);

        $this->logger->critical($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
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
