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

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money[]|null[]
     */
    public $freeTransportMinimalPrice;

    /**
     * @var bool[]|null[]
     */
    public $freeTransport;

    public function __construct()
    {
        $this->name = [];
        $this->freeTransport = [];
        $this->freeTransportMinimalPrice = [];
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
