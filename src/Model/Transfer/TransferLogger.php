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
     * @var string
     */
    private $identifier;

    /**
     * @var \App\Model\Transfer\Issue\TransferIssueData[]
     */
    private $transferIssueDataList = [];

    /**
     * @var \App\Model\Transfer\TransferRepository
     */
    private $transferRepository;

    /**
     * @var \App\Model\Transfer\Issue\TransferIssueFacade
     */
    private $transferIssueFacade;

    /**
     * @param \Monolog\Logger $logger
     * @param string $identifier
     * @param \App\Model\Transfer\TransferRepository $transferRepository
     * @param \App\Model\Transfer\Issue\TransferIssueFacade $transferIssueFacade
     */
    public function __construct(
        Logger $logger,
        string $identifier,
        TransferRepository $transferRepository,
        TransferIssueFacade $transferIssueFacade
    ) {
        $this->logger = $logger;
        $this->identifier = $identifier;
        $this->transferRepository = $transferRepository;
        $this->transferIssueFacade = $transferIssueFacade;
    }

    public function persistAllLoggedTransferIssues(): void
    {
        $transferIssuesCount = count($this->transferIssueDataList);
        if ($transferIssuesCount > 0) {
            $transfer = $this->transferRepository->getTransferByIdentifier($this->identifier);
            $this->transferIssueFacade->saveTransferIssues($this->transferIssueDataList, $transfer);
            $this->transferIssueDataList = [];
            $this->addInfo('Transfer logger saves ' . $transferIssuesCount . ' to database');
        }
    }

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addDebug($message, array $context = []): bool
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_ERROR);

        return $this->logger->addDebug($message, $context);
    }

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addInfo($message, array $context = []): bool
    {
        return $this->logger->addInfo($message, $context);
    }

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addNotice($message, array $context = []): bool
    {
        return $this->logger->addNotice($message, $context);
    }

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addWarning($message, array $context = []): bool
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_WARNING);

        return $this->logger->addWarning($message, $context);
    }

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addError($message, array $context = []): bool
    {
        $this->transferIssueDataList[] = new TransferIssueData($message, TransferIssue::SEVERITY_ERROR);

        return $this->logger->addError($message, $context);
    }

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addCritical($message, array $context = []): bool
    {
        return $this->logger->addCritical($message, $context);
    }

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addAlert($message, array $context = []): bool
    {
        return $this->logger->addAlert($message, $context);
    }

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addEmergency($message, array $context = []): bool
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
