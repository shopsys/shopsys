<?php

declare(strict_types=1);

namespace App\Model\Product\Type;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;

class ProductTypeDataFactory
{
    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\Product\Type\ProductTypeData
     */
    public function create(): ProductTypeData
    {
        $productTypeData = new ProductTypeData();
        $this->fillDefaultData($productTypeData);

        return $productTypeData;
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     */
    private function fillDefaultData(ProductTypeData $productTypeData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $productTypeData->freeTransport[$domainId] = false;
            $productTypeData->freeTransportMinimalPrice[$domainId] = Money::zero();
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $productTypeData->name[$locale] = null;
        }
    }

    /**
     * @param \App\Model\Product\Type\ProductType $productType
     * @return \App\Model\Product\Type\ProductTypeData
     */
    public function createFromProductType(ProductType $productType): ProductTypeData
    {
        $productTypeData = $this->create();
        $this->fillFromProductType($productTypeData, $productType);

        return $productTypeData;
    }

    /**
     * @param \App\Model\Product\Type\ProductTypeData $productTypeData
     * @param \App\Model\Product\Type\ProductType $productType
     */
    private function fillFromProductType(ProductTypeData $productTypeData, ProductType $productType): void
    {

        /** @var \App\Model\Product\Type\ProductTypeTranslation[] $translations */
        $translations = $productType->getTranslations();
        foreach ($translations as $translation) {
            $productTypeData->name[$translation->getLocale()] = $translation->getName();
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $productTypeData->freeTransport[$domainId] = $productType->isFreeTransport($domainId);
            $productTypeData->freeTransportMinimalPrice[$domainId] = $productType->getFreeTransportMinimalPrice($domainId);
        }

        $productTypeData->akeneoCode = $productType->getAkeneoCode();
    }
}
