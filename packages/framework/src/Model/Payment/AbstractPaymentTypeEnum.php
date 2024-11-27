<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

abstract class AbstractPaymentTypeEnum extends AbstractEnum
{
    /**
     * @return string[]
     */
    public function getEnabledInCartCases(): array
    {
        return $this->shouldBeDisplayedInDefaultEshopCart() ? $this->getAllCases() : [];
    }

    /**
     * @return bool
     */
    abstract public function shouldBeDisplayedInDefaultEshopCart(): bool;

    /**
     * @return array<string, string>
     */
    abstract public function getAllIndexedByTranslations(): array;
}
