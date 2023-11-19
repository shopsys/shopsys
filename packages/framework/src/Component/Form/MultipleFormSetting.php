<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Form;

class MultipleFormSetting
{
    public const DEFAULT_MULTIPLE = false;

    protected bool $isCurrentFormMultiple = self::DEFAULT_MULTIPLE;

    public function currentFormIsMultiple(): void
    {
        $this->isCurrentFormMultiple = true;
    }

    public function currentFormIsNotMultiple(): void
    {
        $this->isCurrentFormMultiple = false;
    }

    public function reset(): void
    {
        $this->isCurrentFormMultiple = self::DEFAULT_MULTIPLE;
    }

    /**
     * @return bool
     */
    public function isCurrentFormMultiple(): bool
    {
        return $this->isCurrentFormMultiple;
    }
}
