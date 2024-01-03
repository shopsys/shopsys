<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\ChangeSet;

class ResolvedChanges
{
    /**
     * @param string $dataType
     * @param mixed $oldReadableValue
     * @param mixed $oldValue
     * @param mixed $newReadableValue
     * @param mixed $newValue
     */
    public function __construct(
        public string $dataType,
        public mixed $oldReadableValue,
        public mixed $oldValue,
        public mixed $newReadableValue,
        public mixed $newValue,
    ) {
    }

    /**
     * @return bool
     */
    public function isOldValueSameAsNewValue(): bool
    {
        return $this->oldValue === $this->newValue;
    }
}
