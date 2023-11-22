<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

use Shopsys\FrameworkBundle\Model\Module\Exception\NotUniqueModuleLabelException;

class ModuleList
{
    public const ACCESSORIES_ON_BUY = 'accessoriesOnBuy';
    public const PRODUCT_FILTER_COUNTS = 'productFilterCounts';
    public const PRODUCT_STOCK_CALCULATIONS = 'productStockCalculations';

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->getLabelsIndexedByName());
    }

    /**
     * @return string[]
     */
    public function getNamesIndexedByLabel(): array
    {
        $labelsIndexedByNames = $this->getLabelsIndexedByName();
        $namesIndexedByLabel = array_flip($labelsIndexedByNames);

        if (count($labelsIndexedByNames) !== count($namesIndexedByLabel)) {
            throw new NotUniqueModuleLabelException($labelsIndexedByNames);
        }

        return $namesIndexedByLabel;
    }

    /**
     * @return string[]
     */
    protected function getLabelsIndexedByName(): array
    {
        return [
            self::ACCESSORIES_ON_BUY => t('Accessories in purchase confirmation box'),
            self::PRODUCT_FILTER_COUNTS => t('Number of products in filter'),
            self::PRODUCT_STOCK_CALCULATIONS => t('Automatic stock calculation'),
        ];
    }
}
