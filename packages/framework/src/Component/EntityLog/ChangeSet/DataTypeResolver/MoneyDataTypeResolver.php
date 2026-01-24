<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Override;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyDataTypeResolver extends AbstractDataTypeResolver
{
    public function __construct(
        protected readonly EntityLogFacade $entityLogFacade,
    ) {
    }

    #[Override]
    protected function isResolvedDataType(mixed $value): bool
    {
        return $value instanceof Money;
    }

    /**
     * @param array{0: \Shopsys\FrameworkBundle\Component\Money\Money|null, 1: \Shopsys\FrameworkBundle\Component\Money\Money|null} $changes
     */
    #[Override]
    public function getResolvedChanges(array $changes): ResolvedChanges
    {
        [$oldMoney, $newMoney] = $changes;

        return new ResolvedChanges(
            $this->entityLogFacade->getEntityNameByEntity($oldMoney ?? $newMoney),
            $oldMoney?->getAmount(),
            $oldMoney,
            $newMoney?->getAmount(),
            $newMoney,
        );
    }

    #[Override]
    public function getPriority(): int
    {
        return 1;
    }
}
