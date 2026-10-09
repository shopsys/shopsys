<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use Override;

/**
 * Fallback for all data types without a dedicated formatter (strings, numbers, related entities, arrays)
 */
class ScalarDataTypeFormatter implements DataTypeFormatterInterface
{
    #[Override]
    public function supports(string $dataType): bool
    {
        return true;
    }

    #[Override]
    public function getPriority(): int
    {
        return 0;
    }

    #[Override]
    public function formatValue(mixed $readableValue, mixed $value): string
    {
        return (string)($readableValue ?: t('empty value'));
    }
}
