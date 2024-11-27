<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model;

use Shopsys\FrameworkBundle\Model\Transport\AbstractTransportTypeProvider;

class TransportTypeEnum extends AbstractTransportTypeProvider
{
    public const string TYPE_PPL = 'convertim_ppl';
    public const string TYPE_DPD_CZECHIA = 'convertim_dpd_czechia';
    public const string TYPE_DPD_SLOVAKIA = 'convertim_dpd_slovakia';
    public const string TYPE_HERMES = 'convertim_hermes';
    public const string TYPE_BALIKOVNA = 'convertim_balikovna';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('PPL (Convertim only)') => static::TYPE_PPL,
            t('DPD (Czechia) (Convertim only)') => static::TYPE_DPD_CZECHIA,
            t('DPD (Slovakia) (Convertim only)') => static::TYPE_DPD_SLOVAKIA,
            t('Hermes (Convertim only)') => static::TYPE_HERMES,
            t('Balikovna (Convertim only)') => static::TYPE_BALIKOVNA,
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
