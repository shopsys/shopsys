<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Override;

abstract class AbstractDataTypeResolver implements DataTypeResolverInterface
{
    #[Override]
    public function isResolvedDataTypeByChanges(array $changes): bool
    {
        if ($changes[0] !== null) {
            return $this->isResolvedDataType($changes[0]);
        }

        if ($changes[1] !== null) {
            return $this->isResolvedDataType($changes[1]);
        }

        return false;
    }

    abstract protected function isResolvedDataType(mixed $value): bool;
}
