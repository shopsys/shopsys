<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData as BasePromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeDataFactory as BasePromoCodeDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;

/**
 * @method \App\Model\Order\PromoCode\PromoCodeData createInstance()
 */
class PromoCodeDataFactory extends BasePromoCodeDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository $promoCodeCategoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository $promoCodeLimitRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository $promoCodeBrandRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository $promoCodePricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository $promoCodeFlagRepository
     */
    public function __construct(
        private PromoCodeCategoryRepository $promoCodeCategoryRepository,
        private PromoCodeProductRepository $promoCodeProductRepository,
        private PromoCodeLimitRepository $promoCodeLimitRepository,
        private PromoCodeBrandRepository $promoCodeBrandRepository,
        private PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
        private PromoCodeFlagRepository $promoCodeFlagRepository,
    ) {
    }

    /**
     * @return \App\Model\Order\PromoCode\PromoCodeData
     */
    public function create(): BasePromoCodeData
    {
        $promoCodeData = new PromoCodeData();
        $promoCodeData->massGenerate = false;

        return $promoCodeData;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @return \App\Model\Order\PromoCode\PromoCodeData
     */
    public function createFromPromoCode(BasePromoCode $promoCode): BasePromoCodeData
    {
        $promoCodeData = new PromoCodeData();
        $this->fillFromPromoCode($promoCodeData, $promoCode);

        return $promoCodeData;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    protected function fillFromPromoCode(BasePromoCodeData $promoCodeData, BasePromoCode $promoCode)
    {
        $promoCodeData->code = $promoCode->getCode();
        $promoCodeData->domainId = $promoCode->getDomainId();

        $promoCodeData->datetimeValidFrom = $promoCode->getDatetimeValidFrom();
        $promoCodeData->datetimeValidTo = $promoCode->getDatetimeValidTo();

        $promoCodeData->flags = $this->promoCodeFlagRepository->getFlagsByPromoCodeId($promoCode->getId());
        $promoCodeData->categoriesWithSale = $this->promoCodeCategoryRepository->getCategoriesByPromoCodeId($promoCode->getId());
        $promoCodeData->brandsWithSale = $this->promoCodeBrandRepository->getBrandsByPromoCodeId($promoCode->getId());
        $promoCodeData->productsWithSale = $this->promoCodeProductRepository->getProductsByPromoCodeId($promoCode->getId());
        $promoCodeData->limits = $this->promoCodeLimitRepository->getLimitsByPromoCodeId($promoCode->getId());
        $promoCodeData->remainingUses = $promoCode->getRemainingUses();
        $promoCodeData->identifier = $promoCode->getIdentifier();
        $promoCodeData->massGenerate = $promoCode->isMassGenerate();
        $promoCodeData->prefix = $promoCode->getPrefix();
        $promoCodeData->discountType = $promoCode->getDiscountType();
        $promoCodeData->registeredCustomerUserOnly = $promoCode->isRegisteredCustomerUserOnly();
        $promoCodeData->limitedPricingGroups = $this->promoCodePricingGroupRepository->getPricingGroupsByPromoCodeId($promoCode->getId());
    }
}
