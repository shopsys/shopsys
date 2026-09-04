<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ZboziServiceTypeEnum extends AbstractEnum
{
    public const string FREE_GIFT = 'free_gift';
    public const string EXTENDED_WARRANTY = 'extended_warranty';
    public const string VOUCHER = 'voucher';
    public const string FREE_ACCESSORIES = 'free_accessories';
    public const string FREE_CASE = 'free_case';
    public const string FREE_INSTALLATION = 'free_installation';
    public const string EXTENDED_RETURN = 'extended_return';
    public const string FREE_RETURN = 'free_return';
    public const string PREMIUM_INSTALLATION = 'premium_installation';
    public const string APPLIANCE_PICKUP = 'appliance_pickup';
    public const string AUTHORIZED_SERVICE = 'authorized_service';
    public const string SPLIT_PAYMENT = 'split_payment';
    public const string PAY_LATER = 'pay_later';
    public const string GIFT_PACKAGE = 'gift_package';
    public const string CUSTOM = 'custom';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Free gift') => self::FREE_GIFT,
            t('Extended warranty') => self::EXTENDED_WARRANTY,
            t('Voucher for next purchase') => self::VOUCHER,
            t('Free accessories') => self::FREE_ACCESSORIES,
            t('Free case') => self::FREE_CASE,
            t('Free installation') => self::FREE_INSTALLATION,
            t('Extended return period') => self::EXTENDED_RETURN,
            t('Free return shipping') => self::FREE_RETURN,
            t('Professional appliance installation') => self::PREMIUM_INSTALLATION,
            t('Old appliance removal') => self::APPLIANCE_PICKUP,
            t('Authorized service') => self::AUTHORIZED_SERVICE,
            t('Installment purchase option') => self::SPLIT_PAYMENT,
            t('Deferred payment option') => self::PAY_LATER,
            t('Gift packaging') => self::GIFT_PACKAGE,
            t('Other') => self::CUSTOM,
        ];
    }
}
