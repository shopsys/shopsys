<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

interface TransferLoggerInterface
{
    public function persistAllLoggedTransferIssues(): void;

    /**
     * @param string $message
     * @param array $context
     */
    public function debug(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     */
    public function info(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     */
    public function notice(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     */
    public function error(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     */
    public function critical(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     */
    public function alert(string $message, array $context = []): void;

    /**
     * @param string $message
     * @param array $context
     */
    public function emergency(string $message, array $context = []): void;

    public function close(): void;

    public function reset(): void;
}
