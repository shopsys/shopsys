<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFactory;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrontendApiBundle\Model\Price\PriceFacade;
use Shopsys\FrontendApiBundle\Model\Price\PriceInfo;
use Shopsys\FrontendApiBundle\Model\Price\PriceInfoFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class ProductPriceQuery extends AbstractQuery
{
    public function __construct(
        protected readonly SpecialPriceApiFactory $specialPriceApiFactory,
        protected readonly Domain $domain,
        protected readonly PriceFacade $priceFacade,
        protected readonly PriceInfoFactory $priceInfoFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly GiftPlanSettingFacade $giftPlanSettingFacade,
        protected readonly VatDataFactory $vatDataFactory,
        protected readonly VatFactory $vatFactory,
    ) {
    }

    public function priceByProductQuery(array $data): PriceInfo
    {
        if ($this->isProductUponInquiry($data)) {
            return $this->priceInfoFactory->createHiddenPriceInfo($this->currentCustomerUser->getPricingGroup());
        }

        $basicProductPrice = $this->priceFacade->createProductPriceFromArrayForCurrentCustomer($data['prices']);
        $specialPrice = $this->specialPriceApiFactory->createSpecialPriceFromArray($data, $basicProductPrice->getPrice());

        return $this->priceInfoFactory->create(
            $basicProductPrice,
            $specialPrice,
        );
    }

    public function giftPriceByProductQuery(array $data): PriceInfo
    {
        $domainId = $this->domain->getId();

        $vatData = $this->vatDataFactory->create();
        $vatData->name = 'vat';
        $vatData->percent = $data['vat_percent'];
        $vat = $this->vatFactory->create($vatData, $domainId);

        $productGiftPrice = $this->giftPlanSettingFacade->calculateProductGiftPrice(
            $domainId,
            $vat,
        );

        return $this->priceInfoFactory->create(
            $productGiftPrice,
            null,
        );
    }

    protected function isProductUponInquiry(array $data): bool
    {
        return $data['product_type'] === ProductTypeEnum::TYPE_INQUIRY;
    }
}
