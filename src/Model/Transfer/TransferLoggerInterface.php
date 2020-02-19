<?php

declare(strict_types=1);

namespace App\Model\Transfer;

interface TransferLoggerInterface
{
    public function persistAllLoggedTransferIssues(): void;

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addDebug($message, array $context = []): bool;

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addInfo($message, array $context = []): bool;

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addNotice($message, array $context = []): bool;

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addWarning($message, array $context = []): bool;

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addError($message, array $context = []): bool;

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addCritical($message, array $context = []): bool;

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addAlert($message, array $context = []): bool;

    /**
     * @param mixed $message
     * @param array $context
     * @return bool
     */
    public function addEmergency($message, array $context = []): bool;

    public function close(): void;

    public function reset(): void;
}
