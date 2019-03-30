<?php

namespace Shopsys\ShopBundle\DataFixtures\Loader;

use Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade;
use Shopsys\ShopBundle\DataFixtures\Demo\AvailabilityDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\BrandDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\FlagDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\MultidomainPricingGroupDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\ParameterDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\UnitDataFixture;
use Shopsys\ShopBundle\DataFixtures\Demo\VatDataFixture;

class ProductDataFixtureReferenceLoader
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade
     */
    private $persistentReferenceFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade $persistentReferenceFacade
     */
    public function __construct(PersistentReferenceFacade $persistentReferenceFacade)
    {
        $this->persistentReferenceFacade = $persistentReferenceFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability[]
     */
    public function getAvailabilityReferences()
    {
        return [
            $this->persistentReferenceFacade->getReference(AvailabilityDataFixture::AVAILABILITY_IN_STOCK),
            $this->persistentReferenceFacade->getReference(AvailabilityDataFixture::AVAILABILITY_OUT_OF_STOCK),
            $this->persistentReferenceFacade->getReference(AvailabilityDataFixture::AVAILABILITY_ON_REQUEST),
        ];
    }

    /**
     * @return \Shopsys\ShopBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandReferences()
    {
        return [
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_LG),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_SONY),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_SAMSUNG),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_CANON),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_SENCOR),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_GENIUS),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_GIGABYTE),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_HP),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_MICROSOFT),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_DEFENDER),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_LOGITECH),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_A4TECH),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_APPLE),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_HTC),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_GENIUS),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_GIGABYTE),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_HP),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_MICROSOFT),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_DEFENDER),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_LOGITECH),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_BROTHER),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_JURA),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_ORAVA),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_HYUNDAI),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_VERBATIM),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_DELONGHI),
            $this->persistentReferenceFacade->getReference(BrandDataFixture::BRAND_DLINK),
        ];
    }

    /**
     * @return \Shopsys\ShopBundle\Model\Category\Category[]
     */
    public function getCategoryReferences()
    {
        return [
            $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_TV),
            $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHOTO),
            $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PRINTERS),
            $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PC),
            $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_PHONES),
            $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_BOOKS),
            $this->persistentReferenceFacade->getReference(CategoryDataFixture::CATEGORY_TOYS),
        ];
    }

    /**
     * @return string[]
     */
    public function getFlagReferences()
    {
        return [
            'action' => $this->persistentReferenceFacade->getReference(FlagDataFixture::FLAG_ACTION_PRODUCT),
            'new' => $this->persistentReferenceFacade->getReference(FlagDataFixture::FLAG_NEW_PRODUCT),
            'top' => $this->persistentReferenceFacade->getReference(FlagDataFixture::FLAG_TOP_PRODUCT),
        ];
    }

    /**
     * @param int $domainId
     * @return string[]
     */
    public function getPricingGroupReferencesByDomainId(int $domainId)
    {
        if ($domainId == 1) {
            return [
                $this->persistentReferenceFacade->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1),
                $this->persistentReferenceFacade->getReference(PricingGroupDataFixture::PRICING_GROUP_PARTNER_DOMAIN_1),
                $this->persistentReferenceFacade->getReference(PricingGroupDataFixture::PRICING_GROUP_VIP_DOMAIN_1),
            ];
        }

        return [
            $this->persistentReferenceFacade->getReferenceForDomain(MultidomainPricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN, $domainId),
            $this->persistentReferenceFacade->getReferenceForDomain(MultidomainPricingGroupDataFixture::PRICING_GROUP_VIP_DOMAIN, $domainId),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Unit\Unit[]
     */
    public function getUnitReferences()
    {
        return [
            $this->persistentReferenceFacade->getReference(UnitDataFixture::UNIT_PIECES),
            $this->persistentReferenceFacade->getReference(UnitDataFixture::UNIT_CUBIC_METERS),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat[]
     */
    public function getVatReferences()
    {
        return [
            $this->persistentReferenceFacade->getReference(VatDataFixture::VAT_HIGH),
            $this->persistentReferenceFacade->getReference(VatDataFixture::VAT_LOW),
            $this->persistentReferenceFacade->getReference(VatDataFixture::VAT_SECOND_LOW),
            $this->persistentReferenceFacade->getReference(VatDataFixture::VAT_ZERO),
        ];
    }

    /**
     * @return string[]
     */
    public static function getDataFixtureDependenciesForProduct()
    {
        return [
            VatDataFixture::class,
            AvailabilityDataFixture::class,
            CategoryDataFixture::class,
            BrandDataFixture::class,
            UnitDataFixture::class,
            MultidomainPricingGroupDataFixture::class,
            ParameterDataFixture::class,
        ];
    }
}
