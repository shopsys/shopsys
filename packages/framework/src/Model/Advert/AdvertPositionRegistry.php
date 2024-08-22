<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\Exception\AdvertPositionNotKnownException;

class AdvertPositionRegistry
{
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
            self::POSITION_CATEGORIES_SECOND_ROW_PRODUCT_LIST => t('in category (between first and second row of products)'),
            self::POSITION_CART_PREVIEW => t('above order summary in cart'),
        ];
    }

    /**
     * @return string[]
     */
    public static function getCategoryPosition(): array
    {
        return [
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
