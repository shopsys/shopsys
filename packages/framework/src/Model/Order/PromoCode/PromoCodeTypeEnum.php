<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class PromoCodeTypeEnum extends AbstractEnum
{
    public const string PERCENT = 'percent';
    public const string NOMINAL = 'nominal';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Percents') => static::PERCENT,
            t('Nominal') => static::NOMINAL,
        ];
    }
}
