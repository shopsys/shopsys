<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyDataTypeResolver extends AbstractDataTypeResolver
{
    /**
     * {@inheritdoc}
     */
    protected function isResolvedDataType(mixed $value): bool
    {
        return $value instanceof Money;
    }

    /**
     * @param array{0: \Shopsys\FrameworkBundle\Component\Money\Money|null, 1: \Shopsys\FrameworkBundle\Component\Money\Money|null} $changes
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges
     */
    public function getResolvedChanges(array $changes): ResolvedChanges
    {
        $oldMoney = $changes[0];
        $newMoney = $changes[1];

        return new ResolvedChanges(
            EntityLogFacade::getEntityNameByEntity($oldMoney ?? $newMoney),
            $oldMoney?->getAmount(),
            $oldMoney?->getAmount(),
            $newMoney?->getAmount(),
            $newMoney?->getAmount(),
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
