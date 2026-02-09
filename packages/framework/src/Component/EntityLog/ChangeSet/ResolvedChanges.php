<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet;

use JsonSerializable;
use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;

class ResolvedChanges implements JsonSerializable
{
    public function __construct(
        public string $dataType,
        public mixed $oldReadableValue,
        public mixed $oldValue,
        public mixed $newReadableValue,
        public mixed $newValue,
    ) {
    }

    public function isOldValueSameAsNewValue(): bool
    {
        if ($this->oldValue instanceof Money && $this->newValue instanceof Money) {
            return $this->oldValue->equals($this->newValue);
        }

        return $this->oldValue === $this->newValue;
    }

    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'dataType' => $this->dataType,
            'oldReadableValue' => $this->oldReadableValue,
            'oldValue' => $this->oldValue instanceof Money ? $this->oldValue->getAmount() : $this->oldValue,
            'newReadableValue' => $this->newReadableValue,
            'newValue' => $this->newValue instanceof Money ? $this->newValue->getAmount() : $this->newValue,
        ];
    }
}
