<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;
use Shopsys\FrameworkBundle\Component\Enum\InvalidEnumCaseException;
use Shopsys\FrameworkBundle\Component\Money\Money;

class OrderRoundingTypeEnum extends AbstractEnum
{
    public const string NONE = 'none';
    public const string FIVE_CENTS = 'five_cents';
    public const string WHOLE = 'whole';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('None') => self::NONE,
            t('To five cents (0.05)') => self::FIVE_CENTS,
            t('To whole numbers (1.00)') => self::WHOLE,
        ];
    }

    public function roundPrice(string $roundingType, Money $price): Money
    {
        return match ($roundingType) {
            self::NONE => $price,
            self::FIVE_CENTS => $price->multiply(20)->round(0)->divide(20, 2),
            self::WHOLE => $price->round(0),
            default => throw new InvalidEnumCaseException(self::class, $roundingType),
        };
    }
}
