<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

abstract class AbstractPaymentTypeEnum extends AbstractEnum
{
    /**
     * @return array<string, string>
     */
    abstract public function getAllIndexedByTranslations(): array;
}
