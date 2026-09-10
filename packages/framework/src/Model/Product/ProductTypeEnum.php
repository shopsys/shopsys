<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ProductTypeEnum extends AbstractEnum
{
    public const string TYPE_BASIC = 'basic';

    public const string TYPE_INQUIRY = 'inquiry';

    public const string TYPE_ELECTRONIC_GIFT_VOUCHER = 'electronic_gift_voucher';

    public const string TYPE_PRINTED_GIFT_VOUCHER = 'printed_gift_voucher';

    /**
     * @return string[]
     */
    public function getAllOrderedByMainVariantPriority(): array
    {
        return [
            self::TYPE_BASIC,
            self::TYPE_ELECTRONIC_GIFT_VOUCHER,
            self::TYPE_PRINTED_GIFT_VOUCHER,
            self::TYPE_INQUIRY,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Basic') => self::TYPE_BASIC,
            t('Upon inquiry') => self::TYPE_INQUIRY,
            t('Gift voucher - electronic') => self::TYPE_ELECTRONIC_GIFT_VOUCHER,
            t('Gift voucher - printed') => self::TYPE_PRINTED_GIFT_VOUCHER,
        ];
    }
}
