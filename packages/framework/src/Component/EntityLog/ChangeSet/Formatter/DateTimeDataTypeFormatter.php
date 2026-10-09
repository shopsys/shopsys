<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\Formatter;

use Override;
use Shopsys\FrameworkBundle\Twig\DateTimeFormatterExtension;
use Symfony\Component\Clock\DatePoint;

class DateTimeDataTypeFormatter implements DataTypeFormatterInterface
{
    public function __construct(
        protected readonly DateTimeFormatterExtension $dateTimeFormatterExtension,
    ) {
    }

    #[Override]
    public function supports(string $dataType): bool
    {
        return in_array($dataType, ['DateTimeImmutable', 'DateTime', 'DatePoint'], true);
    }

    #[Override]
    public function getPriority(): int
    {
        return 1;
    }

    #[Override]
    public function formatValue(mixed $readableValue, mixed $value): string
    {
        return $value ? $this->dateTimeFormatterExtension->formatDateTime(new DatePoint($value)) : t('empty value');
    }
}
