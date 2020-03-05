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
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \App\Model\Transfer\Issue\TransferIssueData[]
     */
    private $transferIssueDataList = [];

    /**
     * @var \App\Model\Transfer\Issue\TransferIssueFacade
     */
    private $transferIssueFacade;

    /**
     * @var string
     */
    private $serviceTransferIdentifier;

    /**
     * @param \Monolog\Logger $logger
     * @param string $serviceTransferIdentifier
     * @param \App\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade
     */
    public function __construct(
        Logger $logger,
        string $serviceTransferIdentifier,
        TransferIssueFacade $transferIssueFacade
    ) {
        $this->logger = $logger;
        $this->serviceTransferIdentifier = $serviceTransferIdentifier;
        $this->transferIssueFacade = $transferIssueFacade;
    }

    public function persistAllLoggedTransferIssues(): void
    {
        $transferIssuesCount = count($this->transferIssueDataList);
        if ($transferIssuesCount > 0) {
            $this->transferIssueFacade->saveTransferIssues($this->transferIssueDataList, $this->serviceTransferIdentifier);
            $this->transferIssueDataList = [];
            $this->addInfo('Transfer logger saves ' . $transferIssuesCount . ' to database');
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addDebug(string $message, array $context = []): bool
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_ERROR);

        return $this->logger->addDebug($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addInfo(string $message, array $context = []): bool
    {
        return $this->logger->addInfo($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addNotice(string $message, array $context = []): bool
    {
        return $this->logger->addNotice($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addWarning(string $message, array $context = []): bool
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_WARNING);

        return $this->logger->addWarning($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addError(string $message, array $context = []): bool
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_ERROR);

        return $this->logger->addError($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addCritical(string $message, array $context = []): bool
    {
        return $this->logger->addCritical($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addAlert(string $message, array $context = []): bool
    {
        return $this->logger->addAlert($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addEmergency(string $message, array $context = []): bool
    {
        return $this->logger->addEmergency($message, $context);
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
