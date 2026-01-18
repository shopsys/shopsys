<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;

interface DataTypeResolverInterface
{
    /**
     * @param array{0: mixed, 1: mixed} $changes
     */
    public function getResolvedChanges(array $changes): ResolvedChanges;

    public function getPriority(): int;

    /**
     * @param array{0: mixed, 1: mixed} $changes
     */
    public function isResolvedDataTypeByChanges(array $changes): bool;
}
