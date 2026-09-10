<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Override;

class PaymentTypeEnum extends AbstractPaymentTypeEnum
{
    public const string TYPE_BASIC = 'basic';
    public const string TYPE_GOPAY = 'goPay';
    public const string TYPE_BANK_TRANSFER = 'bankTransfer';
    public const string TYPE_GIFT_VOUCHER = 'giftVoucher';

    public const array INTERNAL_PAYMENTS = [self::TYPE_BASIC, self::TYPE_BANK_TRANSFER, self::TYPE_GIFT_VOUCHER];

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Basic') => self::TYPE_BASIC,
            t('GoPay') => self::TYPE_GOPAY,
            t('Bank transfer') => self::TYPE_BANK_TRANSFER,
            t('Gift voucher') => self::TYPE_GIFT_VOUCHER,
        ];
    }
}
