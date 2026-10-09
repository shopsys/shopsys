<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

interface DataTypeFormatterInterface
{
    public function supports(string $dataType): bool;

    /**
     * Formatters with a higher priority are asked first
     */
    public function getPriority(): int;

    /**
     * Returns the readable text of the value, the caller escapes it before rendering
     */
    public function formatValue(mixed $readableValue, mixed $value): string;
}
