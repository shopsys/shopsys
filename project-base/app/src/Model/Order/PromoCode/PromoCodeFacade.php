<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade as BasePromoCodeFacade;

/**
 * @property \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository
 * @method \App\Model\Order\PromoCode\PromoCode getById(int $promoCodeId)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAll()
 * @method \App\Model\Order\PromoCode\PromoCode create(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method \App\Model\Order\PromoCode\PromoCode edit(int $promoCodeId, \App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method \App\Model\Order\PromoCode\PromoCode|null findPromoCodeByCodeAndDomain(string $code, int $domainId)
 * @method refreshPromoCodeRelations(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method refreshPromoCodeLimits(\App\Model\Order\PromoCode\PromoCode $promoCode, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit[] $limits)
 * @method refreshPromoCodePricingGroups(\App\Model\Order\PromoCode\PromoCode $promoCode, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[] $pricingGroups)
 * @method refreshPromoCodeCategories(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Category\Category[] $categories)
 * @method refreshPromoCodeProducts(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Product\Product[] $products)
 * @method refreshPromoCodeBrands(\App\Model\Order\PromoCode\PromoCode $promoCode, \App\Model\Product\Brand\Brand[] $brands)
 * @method refreshPromoCodeFlags(\App\Model\Order\PromoCode\PromoCode $promoCode, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[] $flags)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Order\PromoCode\PromoCodeRepository $promoCodeRepository, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFactoryInterface $promoCodeFactory, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitRepository $promoCodeLimitRepository, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository $promoCodeProductRepository, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository $promoCodeCategoryRepository, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductFactory $promoCodeProductFactory, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryFactory $promoCodeCategoryFactory, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository $promoCodeBrandRepository, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandFactory $promoCodeBrandFactory, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupRepository $promoCodePricingGroupRepository, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroupFactory $promoCodePricingGroupFactory, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository $promoCodeFlagRepository, \Shopsys\FrameworkBundle\Component\String\HashGenerator $hashGenerator)
 * @method massCreate(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method \App\Model\Order\PromoCode\PromoCode[]|null findByMassBatchId(int $batchId)
 */
class PromoCodeFacade extends BasePromoCodeFacade
{
}
