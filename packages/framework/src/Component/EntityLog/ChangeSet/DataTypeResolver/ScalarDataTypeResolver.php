<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;

class ScalarDataTypeResolver extends AbstractDataTypeResolver
{
    /**
     * @param mixed $value
     * @return bool
     */
    protected function isResolvedDataType(mixed $value): bool
    {
        return is_scalar($value);
    }

    /**
     * @param array $changes
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges
     */
    public function getResolvedChanges(array $changes): ResolvedChanges
    {
        return new ResolvedChanges(
            gettype($changes[0] ?? $changes[1]),
            $changes[0],
            $changes[0],
            $changes[1],
            $changes[1],
        );
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 2;
    }
}
