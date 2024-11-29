<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
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
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade $specialPriceFacade
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Price\SpecialPriceApiFactory $specialPriceApiFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Price\PriceFacade $priceFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrontendApiBundle\Model\Price\PriceInfoFactory $priceInfoFactory
     */
    public function __construct(
        protected readonly SpecialPriceFacade $specialPriceFacade,
        protected readonly SpecialPriceApiFactory $specialPriceApiFactory,
        protected readonly Domain $domain,
        protected readonly PriceFacade $priceFacade,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly PriceInfoFactory $priceInfoFactory,
    ) {
    }

    /**
     * @param array|\Shopsys\FrameworkBundle\Model\Product\Product $data
     * @return \Shopsys\FrontendApiBundle\Model\Price\PriceInfo
     */
    public function priceByProductQuery(Product|array $data): PriceInfo
    {
        if ($this->isProductUponInquiry($data)) {
            return $this->priceInfoFactory->createHiddenPriceInfo();
        }

        if ($data instanceof Product) {
            $basicProductPrice = $this->productCachedAttributesFacade->getProductSellingPrice($data);

            if ($basicProductPrice === null) {
                throw new ProductPriceMissingUserError('The product price is not set.');
            }

            $specialPrice = $this->specialPriceFacade->getEffectiveSpecialPrice($data, $this->domain->getId(), $basicProductPrice);
        } else {
            $basicProductPrice = $this->priceFacade->createProductPriceFromArrayForCurrentCustomer($data['prices']);
            $specialPrice = $this->specialPriceApiFactory->createSpecialPriceFromArray($data, $basicProductPrice);
        }

        return $this->priceInfoFactory->create(
            $basicProductPrice,
            $specialPrice,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @return bool
     */
    protected function isProductUponInquiry(Product|array $data): bool
    {
        $productType = $data instanceof Product ? $data->getProductType() : $data['product_type'];

        return $productType === ProductTypeEnum::TYPE_INQUIRY;
    }
}
