<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use DateTime;
use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;

class DateTimeDataTypeResolver extends AbstractDataTypeResolver
{
    protected const DATE_TIME_FORMAT_WITH_TIMEZONE = 'c';
    protected const DATE_TIME_FORMAT_FOR_HUMAN = 'd. m. Y H:i:s';

    /**
     * @param mixed $value
     * @return bool
     */
    protected function isResolvedDataType(mixed $value): bool
    {
        return $value instanceof DateTime || $value instanceof DateTimeImmutable;
    }

    /**
     * @param array{0: \DateTimeInterface|null, 1: \DateTimeInterface|null} $changes
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges
     */
    public function getResolvedChanges(array $changes): ResolvedChanges
    {
        $oldDateTime = $changes[0];
        $newDateTime = $changes[1];

        return new ResolvedChanges(
            EntityLogFacade::getEntityNameByEntity($oldDateTime ?? $newDateTime),
            null,
            $oldDateTime?->format(static::DATE_TIME_FORMAT_WITH_TIMEZONE),
            null,
            $newDateTime?->format(static::DATE_TIME_FORMAT_WITH_TIMEZONE),
        );
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 1;
    }
}
