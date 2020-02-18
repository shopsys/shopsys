<?php

declare(strict_types=1);

namespace App\Model\Product\Listed;

use Shopsys\FrameworkBundle\Component\Money\Money;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductView as BaseListedProductView;
use Shopsys\ReadModelBundle\Product\Listed\ListedProductViewFactory as BaseListedProductViewFactory;

class ListedProductViewFactory extends BaseListedProductViewFactory
{
    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        Domain $domain,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        ProductAvailabilityFacade $productAvailabilityFacade
    ) {
        parent::__construct($domain, $productCachedAttributesFacade);
        $this->productAvailabilityFacade = $productAvailabilityFacade;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromProduct(Product $product, ?ImageView $imageView, ProductActionView $productActionView): BaseListedProductView
    {
        return new ListedProductView(
            $product->getId(),
            $product->getName(),
            $product->getShortDescription($this->domain->getId()),
            $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $this->domain->getId()),
            $this->productCachedAttributesFacade->getProductSellingPrice($product),
            $this->getFlagIdsForProduct($product),
            $productActionView,
            $imageView,
            $product->getNamePrefix(),
            $product->getNameSufix(),
            $this->getProductPriceWithVatByMonkey($product->getHighPriceWithVat($this->domain->getId()) ?? Money::zero())
        );
    }

    /**
     * @param array $productArray
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromArray(array $productArray, ?ImageView $imageView, ProductActionView $productActionView, PricingGroup $pricingGroup): BaseListedProductView
    {
        return new ListedProductView(
            $productArray['id'],
            $productArray['name'],
            $productArray['short_description'],
            $productArray['availability'],
            $this->getProductPriceFromArrayByPricingGroup($productArray['prices'], $pricingGroup),
            $productArray['flags'],
            $productActionView,
            $imageView,
            $productArray['name_prefix'],
            $productArray['name_sufix'],
            $this->getProductPriceWithVatByMonkey($productArray['non_selling_price'] === null ? Money::zero() : Money::create($productArray['non_selling_price'] . ''))
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    private function getProductPriceWithVatByMonkey(Money $priceWithVat): ProductPrice
    {
        return new ProductPrice(
            new Price(
                Money::zero(),
                $priceWithVat
            ),
            false
        );
    }
}
