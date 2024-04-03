<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;

class PromoCodeDataFactory implements PromoCodeDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository $promoCodeLimitRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository $promoCodeCategoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository $promoCodeBrandRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository $promoCodePricingGroupRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository $promoCodeFlagRepository
     */
    public function __construct(
        protected readonly PromoCodeLimitRepository $promoCodeLimitRepository,
        protected readonly PromoCodeCategoryRepository $promoCodeCategoryRepository,
        protected readonly PromoCodeProductRepository $promoCodeProductRepository,
        protected readonly PromoCodeBrandRepository $promoCodeBrandRepository,
        protected readonly PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
        protected readonly PromoCodeFlagRepository $promoCodeFlagRepository,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData
     */
    protected function createInstance(): PromoCodeData
    {
        return new PromoCodeData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData
     */
    public function create(): PromoCodeData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData
     */
    public function createFromPromoCode(PromoCode $promoCode): PromoCodeData
    {
        $promoCodeData = $this->createInstance();
        $this->fillFromPromoCode($promoCodeData, $promoCode);

        return $promoCodeData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData $promoCodeData
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    protected function fillFromPromoCode(PromoCodeData $promoCodeData, PromoCode $promoCode): void
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
        $promoCodeData->discountType = $promoCode->getDiscountType();
        $promoCodeData->registeredCustomerUserOnly = $promoCode->isRegisteredCustomerUserOnly();
        $promoCodeData->limitedPricingGroups = $this->promoCodePricingGroupRepository->getPricingGroupsByPromoCodeId($promoCode->getId());
        $promoCodeData->massGenerate = $promoCode->isMassGenerate();
        $promoCodeData->prefix = $promoCode->getPrefix();
    }
}
