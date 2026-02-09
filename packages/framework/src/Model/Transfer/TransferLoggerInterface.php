<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

interface TransferLoggerInterface
{
    public function persistAllLoggedTransferIssues(): void;

    public function debug(string $message, array $context = []): void;

    public function info(string $message, array $context = []): void;

    public function notice(string $message, array $context = []): void;

    public function warning(string $message, array $context = []): void;

    public function error(string $message, array $context = []): void;

    public function critical(string $message, array $context = []): void;

    public function alert(string $message, array $context = []): void;

    public function emergency(string $message, array $context = []): void;

    public function close(): void;

    public function reset(): void;
}
