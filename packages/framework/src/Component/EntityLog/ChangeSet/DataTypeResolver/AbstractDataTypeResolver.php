<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

abstract class AbstractDataTypeResolver implements DataTypeResolverInterface
{
    /**
     * @param array $changes
     * @return bool
     */
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

    /**
     * @param mixed $value
     * @return bool
     */
    abstract protected function isResolvedDataType(mixed $value): bool;
}
