<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\NoProductPriceForPricingGroupException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory;

class ListedProductViewFactory
{
    use SetterInjectionTrait;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory $priceFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly ImageViewFacadeInterface $imageViewFacade,
        protected readonly ProductActionViewFacadeInterface $productActionViewFacade,
        protected readonly ProductActionViewFactory $productActionViewFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly PriceFactory $priceFactory,
    ) {
    }

    /**
     * @param int $id
     * @param string $name
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $sellingPrice
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $action
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $image
     * @param string|null $shortDescription
     * @param array $flagIds
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    protected function create(
        int $id,
        string $name,
        string $availability,
        ProductPrice $sellingPrice,
        ProductActionView $action,
        ?ImageView $image,
        ?string $shortDescription,
        array $flagIds = [],
    ): ListedProductView {
        return new ListedProductView(
            $id,
            $name,
            $availability,
            $sellingPrice,
            $action,
            $image,
            $shortDescription,
            $flagIds,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromProduct(
        Product $product,
        ?ImageView $imageView,
        ProductActionView $productActionView,
    ): ListedProductView {
        return $this->create(
            $product->getId(),
            $product->isVariant() && $product->getVariantAlias() ? $product->getVariantAlias() : $product->getName(),
            $product->getCalculatedAvailability()->getName(),
            $this->productCachedAttributesFacade->getProductSellingPrice($product),
            $productActionView,
            $imageView,
            $product->getShortDescription($this->domain->getId()),
            $this->getFlagIdsForProduct($product),
        );
    }

    /**
     * @param array $productArray
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromArray(
        array $productArray,
        ?ImageView $imageView,
        ProductActionView $productActionView,
        PricingGroup $pricingGroup,
    ): ListedProductView {
        $productPrice = $this->priceFactory->createProductPriceFromArrayByPricingGroup(
            $productArray['prices'],
            $pricingGroup,
        );

        return $this->create(
            $productArray['id'],
            $productArray['name'],
            $productArray['availability'],
            $productPrice,
            $productActionView,
            $imageView,
            $productArray['short_description'],
            $productArray['flags'],
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function createFromProducts(array $products): array
    {
        $imageViews = $this->imageViewFacade->getMainImagesByEntityIds(
            Product::class,
            $this->getIdsForProducts($products),
        );
        $productActionViews = $this->productActionViewFacade->getForProducts($products);

        $listedProductViews = [];

        foreach ($products as $product) {
            $productId = $product->getId();
            $listedProductViews[$productId] = $this->createFromProduct(
                $product,
                $imageViews[$productId],
                $productActionViews[$productId],
            );
        }

        return $listedProductViews;
    }

    /**
     * @param array $productsArray
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView[]
     */
    public function createFromProductsArray(array $productsArray): array
    {
        $imageViews = $this->imageViewFacade->getMainImagesByEntityIds(
            Product::class,
            array_column($productsArray, 'id'),
        );

        $listedProductViews = [];

        foreach ($productsArray as $productArray) {
            $productId = $productArray['id'];

            try {
                $listedProductViews[$productId] = $this->createFromArray(
                    $productArray,
                    $imageViews[$productId],
                    $this->productActionViewFactory->createFromArray($productArray),
                    $this->currentCustomerUser->getPricingGroup(),
                );
            } catch (NoProductPriceForPricingGroupException $exception) {
                continue;
            }
        }

        return $listedProductViews;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return int[]
     */
    protected function getIdsForProducts(array $products): array
    {
        return array_map(static function (Product $product): int {
            return $product->getId();
        }, $products);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function getFlagIdsForProduct(Product $product): array
    {
        $flagIds = [];

        foreach ($product->getFlags() as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
    }
}
