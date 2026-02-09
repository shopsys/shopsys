<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;

interface DataTypeResolverInterface
{
    public function getResolvedChanges(array $changes): ResolvedChanges;

    public function getPriority(): int;

    public function isResolvedDataTypeByChanges(array $changes): bool;
}
