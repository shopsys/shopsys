<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\DataTypeResolver;

use Nette\Utils\Json;
use Override;
use Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet\ResolvedChanges;

class ArrayDataTypeResolver extends AbstractDataTypeResolver
{
    #[Override]
    protected function isResolvedDataType(mixed $value): bool
    {
        return is_array($value);
    }

    #[Override]
    public function getResolvedChanges(array $changes): ResolvedChanges
    {
        $oldValue = $changes[0];
        $newValue = $changes[1];

        return new ResolvedChanges(
            'array',
            $this->getReadableValue($oldValue),
            $oldValue,
            $this->getReadableValue($newValue),
            $newValue,
        );
    }

    protected function getReadableValue(?array $value): ?string
    {
        return $value === null ? null : Json::encode($value);
    }

    #[Override]
    public function getPriority(): int
    {
        return 2;
    }
}
