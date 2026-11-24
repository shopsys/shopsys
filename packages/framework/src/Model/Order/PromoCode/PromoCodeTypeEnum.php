<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class PromoCodeTypeEnum extends AbstractEnum
{
    public const string DISCOUNT_TYPE_PERCENT = 'percent';
    public const string DISCOUNT_TYPE_NOMINAL = 'nominal';
    public const string DISCOUNT_TYPE_FREE_TRANSPORT_PAYMENT = 'free_transport_payment';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Percents') => static::DISCOUNT_TYPE_PERCENT,
            t('Nominal') => static::DISCOUNT_TYPE_NOMINAL,
            t('Free shipping and payment') => static::DISCOUNT_TYPE_FREE_TRANSPORT_PAYMENT,
        ];
    }
}
