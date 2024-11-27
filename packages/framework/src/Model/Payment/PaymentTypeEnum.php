<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

class PaymentTypeEnum extends AbstractPaymentTypeEnum
{
    public const string TYPE_BASIC = 'basic';
    public const string TYPE_GOPAY = 'goPay';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Basic') => self::TYPE_BASIC,
            t('GoPay') => self::TYPE_GOPAY,
        ];
    }

    /**
     * @return bool
     */
    public function shouldBeDisplayedInDefaultEshopCart(): bool
    {
        return true;
    }
}
