<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Override;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;

class ScalarDataTypeResolver extends AbstractDataTypeResolver
{
    #[Override]
    protected function isResolvedDataType(mixed $value): bool
    {
        return is_scalar($value);
    }

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

    #[Override]
    public function getPriority(): int
    {
        return 2;
    }
}
