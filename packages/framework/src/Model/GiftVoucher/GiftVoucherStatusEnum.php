<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GiftVoucher;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class GiftVoucherStatusEnum extends AbstractEnum
{
    public const string STATUS_UNREDEEMED = 'unredeemed';

    public const string STATUS_REDEEMED = 'redeemed';

    public const string STATUS_CANCELLED = 'cancelled';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Unredeemed') => self::STATUS_UNREDEEMED,
            t('Redeemed') => self::STATUS_REDEEMED,
            t('Cancelled') => self::STATUS_CANCELLED,
        ];
    }
}
