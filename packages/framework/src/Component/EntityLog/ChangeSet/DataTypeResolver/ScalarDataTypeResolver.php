<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Override;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;

class ScalarDataTypeResolver extends AbstractDataTypeResolver
{
    /**
     * @param mixed $value
     * @return bool
     */
    #[Override]
    protected function isResolvedDataType(mixed $value): bool
    {
        return is_scalar($value);
    }

    /**
     * @param array $changes
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges
     */
    #[Override]
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
    #[Override]
    public function getPriority(): int
    {
        return 2;
    }
}
