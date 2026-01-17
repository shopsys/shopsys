<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFactory;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrontendApiBundle\Model\Price\PriceFacade;
use Shopsys\FrontendApiBundle\Model\Price\PriceInfo;
use Shopsys\FrontendApiBundle\Model\Price\PriceInfoFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Price\Exception\ProductPriceMissingUserError;

class ProductPriceQuery extends AbstractQuery
{
    public function __construct(
        protected readonly SpecialPriceFacade $specialPriceFacade,
        protected readonly SpecialPriceApiFactory $specialPriceApiFactory,
        protected readonly Domain $domain,
        protected readonly PriceFacade $priceFacade,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly PriceInfoFactory $priceInfoFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly GiftPlanSettingFacade $giftPlanSettingFacade,
        protected readonly VatDataFactory $vatDataFactory,
        protected readonly VatFactory $vatFactory,
    ) {
    }

    public function priceByProductQuery(Product|array $data): PriceInfo
    {
        if ($this->isProductUponInquiry($data)) {
            return $this->priceInfoFactory->createHiddenPriceInfo($this->currentCustomerUser->getPricingGroup());
        }

        if ($data instanceof Product) {
            $basicProductPrice = $this->productCachedAttributesFacade->getProductBasicPrice($data);

            if ($basicProductPrice === null) {
                throw new ProductPriceMissingUserError('The product price is not set.');
            }

            $specialPrice = $this->specialPriceFacade->findRelevantSpecialPrice($data, $this->domain->getId(), $basicProductPrice->getPrice());
        } else {
            $basicProductPrice = $this->priceFacade->createProductPriceFromArrayForCurrentCustomer($data['prices']);
            $specialPrice = $this->specialPriceApiFactory->createSpecialPriceFromArray($data, $basicProductPrice->getPrice());
        }

        return $this->priceInfoFactory->create(
            $basicProductPrice,
            $specialPrice,
        );
    }

    public function giftPriceByProductQuery(Product|array $data): PriceInfo
    {
        $domainId = $this->domain->getId();

        if ($data instanceof Product) {
            $vat = $data->getVatForDomain($domainId);
        } else {
            $vatPercent = $data['vat_percent'];
            $vatData = $this->vatDataFactory->create();
            $vatData->name = 'vat';
            $vatData->percent = $vatPercent;
            $vat = $this->vatFactory->create($vatData, $domainId);
        }

        $productGiftPrice = $this->giftPlanSettingFacade->calculateProductGiftPrice(
            $domainId,
            $vat,
        );

        return $this->priceInfoFactory->create(
            $productGiftPrice,
            null,
        );
    }

    protected function isProductUponInquiry(Product|array $data): bool
    {
        $productType = $data instanceof Product ? $data->getProductType() : $data['product_type'];

        return $productType === ProductTypeEnum::TYPE_INQUIRY;
    }
}
