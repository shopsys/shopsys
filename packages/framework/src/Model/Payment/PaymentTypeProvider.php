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

    public function getAllIndexedByTranslations(): array
    {
        $allIndexedByTranslations = [];

        foreach ($this->paymentTypeEnums as $paymentTypeEnum) {
            $allIndexedByTranslations = array_merge($allIndexedByTranslations, $paymentTypeEnum->getAllIndexedByTranslations());
        }

        return $allIndexedByTranslations;
    }
}
