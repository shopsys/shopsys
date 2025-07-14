<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Override;

class PaymentTypeEnum extends AbstractPaymentTypeEnum
{
    public const string TYPE_BASIC = 'basic';
    public const string TYPE_GOPAY = 'goPay';

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Basic') => self::TYPE_BASIC,
            t('GoPay') => self::TYPE_GOPAY,
        ];
    }
}
