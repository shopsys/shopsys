<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry as BaseAdvertPositionRegistry;

class AdvertPositionRegistry extends BaseAdvertPositionRegistry
{
    public const CATEGORIES_ABOVE_PRODUCT_LIST = 'productListMiddle';

    /**
     * @return string[]
     */
    public function getAllLabelsIndexedByNames(): array
    {
        $allLabelsIndexedByNames = parent::getAllLabelsIndexedByNames();
        $allLabelsIndexedByNames['cartPreview'] = t('nad souhrnem objednávky v košíku');
        $allLabelsIndexedByNames[self::CATEGORIES_ABOVE_PRODUCT_LIST] = t('v kategorii (nad výpisem produktů)');

        return $allLabelsIndexedByNames;
    }
}
