<?php

namespace Shopsys\FrameworkBundle\Component\Form;

class MultipleFormSetting
{
    const DEFAULT_MULTIPLE = false;

    /**
     * @var bool
     */
    private $isCurrentFormMultiple = self::DEFAULT_MULTIPLE;

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

    public function isCurrentFormMultiple(): bool
    {
        return $this->isCurrentFormMultiple;
    }
}
