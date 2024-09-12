<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class TransportTypeEnum extends AbstractEnum
{
    public const string TYPE_COMMON = 'common';
    public const string TYPE_PACKETERY = 'packetery';
    public const string TYPE_PERSONAL_PICKUP = 'personal_pickup';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Standard') => self::TYPE_COMMON,
            t('Packetery') => self::TYPE_PACKETERY,
            t('Personal pickup') => self::TYPE_PERSONAL_PICKUP,
        ];
    }
}
