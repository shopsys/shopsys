<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;

class PromoCodeDataFactory
{
    public function __construct(
        protected readonly PromoCodeLimitRepository $promoCodeLimitRepository,
        protected readonly PromoCodeCategoryRepository $promoCodeCategoryRepository,
        protected readonly PromoCodeProductRepository $promoCodeProductRepository,
        protected readonly PromoCodeBrandRepository $promoCodeBrandRepository,
        protected readonly PromoCodePricingGroupRepository $promoCodePricingGroupRepository,
        protected readonly PromoCodeFlagRepository $promoCodeFlagRepository,
    ) {
    }

    protected function createInstance(): PromoCodeData
    {
        return new PromoCodeData();
    }

    public function create(): PromoCodeData
    {
        return $this->createInstance();
    }

    public function createFromPromoCode(PromoCode $promoCode): PromoCodeData
    {
        $promoCodeData = $this->createInstance();
        $this->fillFromPromoCode($promoCodeData, $promoCode);

        return $promoCodeData;
    }

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
        $promoCodeData->limitsByCurrencyCode = [];

        foreach ($this->promoCodeLimitRepository->getLimitsByPromoCodeId($promoCode->getId()) as $limit) {
            $promoCodeData->limitsByCurrencyCode[$limit->getCurrency()->getCode()][] = $limit;
        }
        $promoCodeData->remainingUses = $promoCode->getRemainingUses();
        $promoCodeData->discountType = $promoCode->getDiscountType();
        $promoCodeData->registeredCustomerUserOnly = $promoCode->isRegisteredCustomerUserOnly();
        $promoCodeData->limitedPricingGroups = $this->promoCodePricingGroupRepository->getPricingGroupsByPromoCodeId($promoCode->getId());
        $promoCodeData->massGenerate = $promoCode->isMassGenerate();
        $promoCodeData->prefix = $promoCode->getPrefix();
        $promoCodeData->enabled = $promoCode->isEnabled();
    }
}
