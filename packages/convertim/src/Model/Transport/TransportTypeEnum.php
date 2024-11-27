<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\AbstractTransportTypeEnum;

class TransportTypeEnum extends AbstractTransportTypeEnum
{
    public const string TYPE_PPL = 'convertim_ppl';
    public const string TYPE_DPD_CZECHIA = 'convertim_dpd_czechia';
    public const string TYPE_DPD_SLOVAKIA = 'convertim_dpd_slovakia';
    public const string TYPE_BALIKOVNA = 'convertim_balikovna';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('PPL (Convertim only)') => self::TYPE_PPL,
            t('DPD (Czechia) (Convertim only)') => self::TYPE_DPD_CZECHIA,
            t('DPD (Slovakia) (Convertim only)') => self::TYPE_DPD_SLOVAKIA,
            t('Balikovna (Convertim only)') => self::TYPE_BALIKOVNA,
        ];
    }

    /**
     * @return bool
     */
    public function shouldBeDisplayedInDefaultEshopCart(): bool
    {
        return false;
    }
}
