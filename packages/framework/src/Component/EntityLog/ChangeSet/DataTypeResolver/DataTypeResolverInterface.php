<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;

interface DataTypeResolverInterface
{
    /**
     * @param array $changes
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges
     */
    public function getResolvedChanges(array $changes): ResolvedChanges;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @param array $changes
     * @return bool
     */
    public function isResolvedDataTypeByChanges(array $changes): bool;
}
