<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

class PaymentTypeProvider
{
    /**
     * @param iterable<\Shopsys\FrameworkBundle\Model\Payment\AbstractPaymentTypeEnum> $paymentTypeEnums
     */
    public function __construct(
        protected readonly iterable $paymentTypeEnums,
    ) {
    }

    /**
     * @return array
     */
    public function getAllIndexedByTranslations(): array
    {
        $allIndexedByTranslations = [];

        foreach ($this->paymentTypeEnums as $transportTypeEnum) {
            $allIndexedByTranslations = array_merge($allIndexedByTranslations, $transportTypeEnum->getAllIndexedByTranslations());
        }

        return $allIndexedByTranslations;
    }
}
