<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Listed;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    protected $productCachedAttributesFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface
     */
    protected $imageViewFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface
     */
    protected $productActionViewFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory
     */
    protected $productActionViewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory
     */
    protected $priceFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface|null $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface|null $productActionViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory|null $productActionViewFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser|null $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory|null $priceFactory
     */
    public function __construct(
        Domain $domain,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        ?ImageViewFacadeInterface $imageViewFacade = null,
        ?ProductActionViewFacadeInterface $productActionViewFacade = null,
        ?ProductActionViewFactory $productActionViewFactory = null,
        ?CurrentCustomerUser $currentCustomerUser = null,
        ?PriceFactory $priceFactory = null
    ) {
        $this->domain = $domain;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
        $this->imageViewFacade = $imageViewFacade;
        $this->productActionViewFacade = $productActionViewFacade;
        $this->productActionViewFactory = $productActionViewFactory;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->priceFactory = $priceFactory;
    }

    /**
     * @param int $id
     * @param string $name
     * @param string|null $shortDescription
     * @param string $availability
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $sellingPrice
     * @param array $flagIds
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $action
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $image
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    protected function create(
        int $id,
        string $name,
        ?string $shortDescription,
        string $availability,
        ProductPrice $sellingPrice,
        array $flagIds,
        ProductActionView $action,
        ?ImageView $image
    ): ListedProductView {
        return new ListedProductView(
            $id,
            $name,
            $shortDescription,
            $availability,
            $sellingPrice,
            $flagIds,
            $action,
            $image
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromProduct(Product $product, ?ImageView $imageView, ProductActionView $productActionView): ListedProductView
    {
        return $this->create(
            $product->getId(),
            $product->isVariant() && $product->getVariantAlias() ? $product->getVariantAlias() : $product->getName(),
            $product->getShortDescription($this->domain->getId()),
            $product->getCalculatedAvailability()->getName(),
            $this->productCachedAttributesFacade->getProductSellingPrice($product),
            $this->getFlagIdsForProduct($product),
            $productActionView,
            $imageView
        );
    }

    /**
     * @param array $productArray
     * @param \Shopsys\ReadModelBundle\Image\ImageView|null $imageView
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionView $productActionView
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\ReadModelBundle\Product\Listed\ListedProductView
     */
    public function createFromArray(array $productArray, ?ImageView $imageView, ProductActionView $productActionView, PricingGroup $pricingGroup): ListedProductView
    {
        $productPrice = $this->priceFactory->createProductPriceFromArrayByPricingGroup(
            $productArray['prices'],
            $pricingGroup
        );
        if ($productPrice === null) {
            throw new NoProductPriceForPricingGroupException($productArray['id'], $pricingGroup->getId());
        }

        return $this->create(
            $productArray['id'],
            $productArray['name'],
            $productArray['short_description'],
            $productArray['availability'],
            $productPrice,
            $productArray['flags'],
            $productActionView,
            $imageView
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
            $this->getIdsForProducts($products)
        );
        $productActionViews = $this->productActionViewFacade->getForProducts($products);

        $listedProductViews = [];
        foreach ($products as $product) {
            $productId = $product->getId();
            $listedProductViews[$productId] = $this->createFromProduct(
                $product,
                $imageViews[$productId],
                $productActionViews[$productId]
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
            array_column($productsArray, 'id')
        );

        $listedProductViews = [];
        foreach ($productsArray as $productArray) {
            $productId = $productArray['id'];
            try {
                $listedProductViews[$productId] = $this->createFromArray(
                    $productArray,
                    $imageViews[$productId],
                    $this->productActionViewFactory->createFromArray($productArray),
                    $this->currentCustomerUser->getPricingGroup()
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
     * @param array $pricesArray
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     * @deprecated This method will be removed in next major. Use PriceFactory::createProductPriceFromArrayByPricingGroup() instead.
     */
    protected function getProductPriceFromArrayByPricingGroup(
        array $pricesArray,
        PricingGroup $pricingGroup
    ): ?ProductPrice {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use PriceFactory::createProductPriceFromArrayByPricingGroup() instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        return $this->priceFactory->createProductPriceFromArrayByPricingGroup($pricesArray, $pricingGroup);
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

    /**
     * @required
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacadeInterface $imageViewFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setImageViewFacade(ImageViewFacadeInterface $imageViewFacade): void
    {
        if ($this->imageViewFacade !== null && $this->imageViewFacade !== $imageViewFacade) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->imageViewFacade !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->imageViewFacade = $imageViewFacade;
    }

    /**
     * @required
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface $productActionViewFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductActionViewFacade(ProductActionViewFacadeInterface $productActionViewFacade): void
    {
        if ($this->productActionViewFacade !== null && $this->productActionViewFacade !== $productActionViewFacade) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->productActionViewFacade !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->productActionViewFacade = $productActionViewFacade;
    }

    /**
     * @required
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFactory $productActionViewFactory
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductActionViewFactory(ProductActionViewFactory $productActionViewFactory): void
    {
        if (
            $this->productActionViewFactory !== null
            && $this->productActionViewFactory !== $productActionViewFactory
        ) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->productActionViewFactory !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->productActionViewFactory = $productActionViewFactory;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setCurrentCustomerUser(CurrentCustomerUser $currentCustomerUser): void
    {
        if ($this->currentCustomerUser !== null && $this->currentCustomerUser !== $currentCustomerUser) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->currentCustomerUser !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory $priceFactory
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setPriceFactory(PriceFactory $priceFactory): void
    {
        if ($this->priceFactory !== null && $this->priceFactory !== $priceFactory) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->priceFactory !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->priceFactory = $priceFactory;
    }
}
