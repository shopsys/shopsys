<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ProductTypeEnum extends AbstractEnum
{
    public const string TYPE_BASIC = 'basic';

    public const string TYPE_INQUIRY = 'inquiry';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Basic') => self::TYPE_BASIC,
            t('Upon inquiry') => self::TYPE_INQUIRY,
        ];
    }
}
