<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

class ProductTypeData
{
    /**
     * @var string[]|null[]
     */
    public $name;

    /**
     * @var string|null
     */
    public $akeneoCode;

    public function __construct()
    {
        $this->name = [];
    }

    /**
     * @param \App\Model\Product\Type\ProductType $productType
     */
    public function fillFromProductType(ProductType $productType): void
    {
        $translations = $productType->getTranslations();
        $this->name = [];
        foreach ($translations as $translate) {
            $this->name[$translate->getLocale()] = $translate->getName();
        }
        $this->akeneoCode = $productType->getAkeneoCode();
    }
}
