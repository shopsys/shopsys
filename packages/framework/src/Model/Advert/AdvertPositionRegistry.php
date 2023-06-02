<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\Exception\AdvertPositionNotKnownException;

class AdvertPositionRegistry
{
    public const POSITION_PRODUCT_LIST = 'productList';
    public const POSITION_CATEGORIES_ABOVE_PRODUCT_LIST = 'productListMiddle';
    public const POSITION_CATEGORIES_SECOND_ROW_PRODUCT_LIST = 'productListSecondRow';
    public const POSITION_CART_PREVIEW = 'cartPreview';
    public const POSITION_HEADER = 'header';
    public const POSITION_FOOTER = 'footer';

    /**
     * @return string[]
     */
    public function getAllLabelsIndexedByNames(): array
    {
        return [
            self::POSITION_HEADER => t('under heading'),
            self::POSITION_FOOTER => t('above footer'),
            self::POSITION_PRODUCT_LIST => t('in category (above the category name)'),
            self::POSITION_CATEGORIES_ABOVE_PRODUCT_LIST => t('v kategorii (nad výpisem produktů)'),
            self::POSITION_CATEGORIES_SECOND_ROW_PRODUCT_LIST => t('v kategorii (mezi prvním a druhým řádkem produktů)'),
            self::POSITION_CART_PREVIEW => t('nad souhrnem objednávky v košíku'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getCategoryPosition(): array
    {
        return [
            self::POSITION_PRODUCT_LIST,
            self::POSITION_CATEGORIES_ABOVE_PRODUCT_LIST,
            self::POSITION_CATEGORIES_SECOND_ROW_PRODUCT_LIST,
        ];
    }

    /**
     * @param string $positionName
     * @return bool
     */
    public static function isCategoryPosition(string $positionName): bool
    {
        return in_array($positionName, self::getCategoryPosition(), true);
    }

    /**
     * @param string $positionName
     */
    public function assertPositionNameIsKnown(string $positionName): void
    {
        $knownPositionsNames = array_keys($this->getAllLabelsIndexedByNames());

        if (!in_array($positionName, $knownPositionsNames, true)) {
            throw new AdvertPositionNotKnownException(
                $positionName,
                $knownPositionsNames,
            );
        }
    }
}
