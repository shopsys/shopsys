<?php

namespace Shopsys\FrameworkBundle\DataFixtures;

use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\FrameworkBundle\DataFixtures\Demo\AvailabilityDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\Demo\BrandDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\Demo\FlagDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\Demo\PricingGroupDataFixture as DemoPricingGroupDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\Demo\ProductDataFixtureLoader;
use Shopsys\FrameworkBundle\DataFixtures\Demo\UnitDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\Demo\VatDataFixture;
use Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain\PricingGroupDataFixture as MultidomainPricingGroupDataFixture;

class ProductDataFixtureReferenceInjector
{
    public function loadReferences(
        ProductDataFixtureLoader $productDataFixtureLoader,
        PersistentReferenceFacade $persistentReferenceFacade,
        bool $onlyForFirstDomain
    ): void {
        $vats = $this->getVatReferences($persistentReferenceFacade);
        $availabilities = $this->getAvailabilityReferences($persistentReferenceFacade);
        $categories = $this->getCategoryReferences($persistentReferenceFacade);
        $flags = $this->getFlagReferences($persistentReferenceFacade);
        $brands = $this->getBrandReferences($persistentReferenceFacade);
        $units = $this->getUnitReferences($persistentReferenceFacade);
        if ($onlyForFirstDomain === true) {
            $pricingGroups = $this->getPricingGroupReferencesForFirstDomain($persistentReferenceFacade);
        } else {
            $pricingGroups = $this->getPricingGroupReferences($persistentReferenceFacade);
        }

        $productDataFixtureLoader->refreshCachedEntities(
            $vats,
            $availabilities,
            $categories,
            $flags,
            $brands,
            $units,
            $pricingGroups
        );
    }

    /**
     * @return string[]
     */
    private function getVatReferences(PersistentReferenceFacade $persistentReferenceFacade): array
    {
        return [
            'high' => $persistentReferenceFacade->getReference(VatDataFixture::VAT_HIGH),
            'low' => $persistentReferenceFacade->getReference(VatDataFixture::VAT_LOW),
            'second_low' => $persistentReferenceFacade->getReference(VatDataFixture::VAT_SECOND_LOW),
            'zero' => $persistentReferenceFacade->getReference(VatDataFixture::VAT_ZERO),
        ];
    }

    /**
     * @return string[]
     */
    private function getAvailabilityReferences(PersistentReferenceFacade $persistentReferenceFacade): array
    {
        return [
            'in-stock' => $persistentReferenceFacade->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK),
            'out-of-stock' => $persistentReferenceFacade->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK),
            'on-request' => $persistentReferenceFacade->getReference(AvailabilityDataFixture::AVAILABILITY_ON_REQUEST),
        ];
    }

    /**
     * @return string[]
     */
    private function getCategoryReferences(PersistentReferenceFacade $persistentReferenceFacade): array
    {
        return [
            'electronics' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS),
            'tv' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_TV),
            'photo' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHOTO),
            'printers' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PRINTERS),
            'pc' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PC),
            'phones' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHONES),
            'coffee' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_COFFEE),
            'books' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_BOOKS),
            'toys' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_TOYS),
            'garden_tools' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS),
            'food' => $persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_FOOD),
        ];
    }

    /**
     * @return string[]
     */
    private function getFlagReferences(PersistentReferenceFacade $persistentReferenceFacade): array
    {
        return [
            'action' => $persistentReferenceFacade->getReference(FlagDataFixture::FLAG_ACTION_PRODUCT),
            'new' => $persistentReferenceFacade->getReference(FlagDataFixture::FLAG_NEW_PRODUCT),
            'top' => $persistentReferenceFacade->getReference(FlagDataFixture::FLAG_TOP_PRODUCT),
        ];
    }

    /**
     * @return string[]
     */
    private function getBrandReferences(PersistentReferenceFacade $persistentReferenceFacade): array
    {
        return [
            'apple' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_APPLE),
            'canon' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_CANON),
            'lg' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_LG),
            'philips' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_PHILIPS),
            'sencor' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_SENCOR),
            'a4tech' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_A4TECH),
            'brother' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_BROTHER),
            'verbatim' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_VERBATIM),
            'dlink' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_DLINK),
            'defender' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_DEFENDER),
            'delonghi' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_DELONGHI),
            'genius' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_GENIUS),
            'gigabyte' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_GIGABYTE),
            'hp' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_HP),
            'htc' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_HTC),
            'jura' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_JURA),
            'logitech' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_LOGITECH),
            'microsoft' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_MICROSOFT),
            'samsung' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_SAMSUNG),
            'sony' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_SONY),
            'orava' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_ORAVA),
            'olympus' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_OLYMPUS),
            'hyundai' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_HYUNDAI),
            'nikon' => $persistentReferenceFacade->getReference(BrandDataFixture::BRAND_NIKON),
        ];
    }

    /**
     * @return string[]
     */
    private function getUnitReferences(PersistentReferenceFacade $persistentReferenceFacade): array
    {
        return [
            'pcs' => $persistentReferenceFacade->getReference(UnitDataFixture::UNIT_PIECES),
            'm3' => $persistentReferenceFacade->getReference(UnitDataFixture::UNIT_CUBIC_METERS),
        ];
    }

    /**
     * @return string[]
     */
    private function getPricingGroupReferencesForFirstDomain(PersistentReferenceFacade $persistentReferenceFacade): array
    {
        return [
            'ordinary_domain_1' => $persistentReferenceFacade->getReference(DemoPricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1),
            'partner_domain_1' => $persistentReferenceFacade->getReference(DemoPricingGroupDataFixture::PRICING_GROUP_PARTNER_DOMAIN_1),
            'vip_domain_1' => $persistentReferenceFacade->getReference(DemoPricingGroupDataFixture::PRICING_GROUP_VIP_DOMAIN_1),
        ];
    }

    /**
     * @return string[]
     */
    private function getPricingGroupReferences(PersistentReferenceFacade $persistentReferenceFacade): array
    {
        return [
            'ordinary_domain_1' => $persistentReferenceFacade->getReference(DemoPricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1),
            'ordinary_domain_2' => $persistentReferenceFacade->getReference(MultidomainPricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_2),
            'partner_domain_1' => $persistentReferenceFacade->getReference(DemoPricingGroupDataFixture::PRICING_GROUP_PARTNER_DOMAIN_1),
            'vip_domain_1' => $persistentReferenceFacade->getReference(DemoPricingGroupDataFixture::PRICING_GROUP_VIP_DOMAIN_1),
            'vip_domain_2' => $persistentReferenceFacade->getReference(MultidomainPricingGroupDataFixture::PRICING_GROUP_VIP_DOMAIN_2),
        ];
    }

    /**
     * @return string[]
     */
    public static function getDependenciesForFirstDomain(): array
    {
        return [
            VatDataFixture::class,
            AvailabilityDataFixture::class,
            CategoryDataFixture::class,
            BrandDataFixture::class,
            UnitDataFixture::class,
            DemoPricingGroupDataFixture::class,
        ];
    }

    /**
     * @return string[]
     */
    public static function getDependenciesForMultidomain(): array
    {
        return [
            MultidomainPricingGroupDataFixture::class,
        ];
    }
}
