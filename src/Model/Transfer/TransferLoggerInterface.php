<?php

declare(strict_types=1);

namespace App\Model\Transfer;

interface TransferLoggerInterface
{
    public function persistAllLoggedTransferIssues(): void;

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addDebug(string $message, array $context = []): bool;

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addInfo(string $message, array $context = []): bool;

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addNotice(string $message, array $context = []): bool;

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addWarning(string $message, array $context = []): bool;

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addError(string $message, array $context = []): bool;

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addCritical(string $message, array $context = []): bool;

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addAlert(string $message, array $context = []): bool;

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function addEmergency(string $message, array $context = []): bool;

    public function close(): void;

    public function reset(): void;
}
