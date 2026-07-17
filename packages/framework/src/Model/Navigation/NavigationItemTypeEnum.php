<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Navigation;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class NavigationItemTypeEnum extends AbstractEnum
{
    public const string LINK = 'link';
    public const string CATEGORIES = 'categories';

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('Link URL') => self::LINK,
            t('Subcategories') => self::CATEGORIES,
        ];
    }
}
