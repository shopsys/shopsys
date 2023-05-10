<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry as BaseAdvertPositionRegistry;

class AdvertPositionRegistry extends BaseAdvertPositionRegistry
{
    public const CATEGORIES_ABOVE_PRODUCT_LIST = 'productListMiddle';
    public const CATEGORIES_SECOND_ROW_PRODUCT_LIST = 'productListSecondRow';
    public const CART_PREVIEW = 'cartPreview';

    /**
     * @return string[]
     */
    public function getAllLabelsIndexedByNames(): array
    {
        $allLabelsIndexedByNames = parent::getAllLabelsIndexedByNames();
        $allLabelsIndexedByNames[self::CART_PREVIEW] = t('nad souhrnem objednávky v košíku');
        $allLabelsIndexedByNames[self::CATEGORIES_ABOVE_PRODUCT_LIST] = t('v kategorii (nad výpisem produktů)');
        $allLabelsIndexedByNames[self::CATEGORIES_SECOND_ROW_PRODUCT_LIST] = t('v kategorii (mezi prvním a druhým řádkem produktů)');

        return $allLabelsIndexedByNames;
    }
}
