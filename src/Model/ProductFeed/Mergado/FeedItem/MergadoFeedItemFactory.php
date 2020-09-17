<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Mergado\FeedItem;

use App\Component\Image\ImageFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;

class MergadoFeedItemFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader
     */
    private $productUrlsBatchLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader
     */
    private $productParametersBatchLoader;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $availabilityFacade;

    /**
     * @var \App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser
     */
    private $productPriceCalculationForCustomerUser;

    /**
     * @var \App\Component\Image\ImageFacade
     */
    private $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader $productParametersBatchLoader
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $availabilityFacade
     * @param \App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        ProductUrlsBatchLoader $productUrlsBatchLoader,
        ProductParametersBatchLoader $productParametersBatchLoader,
        CategoryFacade $categoryFacade,
        ProductAvailabilityFacade $availabilityFacade,
        ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        ImageFacade $imageFacade,
        CurrencyFacade $currencyFacade
    ) {
        $this->productUrlsBatchLoader = $productUrlsBatchLoader;
        $this->productParametersBatchLoader = $productParametersBatchLoader;
        $this->categoryFacade = $categoryFacade;
        $this->availabilityFacade = $availabilityFacade;
        $this->productPriceCalculationForCustomerUser = $productPriceCalculationForCustomerUser;
        $this->imageFacade = $imageFacade;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\ProductFeed\Mergado\FeedItem\MergadoFeedItem
     */
    public function createForProduct(Product $product, DomainConfig $domainConfig): MergadoFeedItem
    {
        $domainId = $domainConfig->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return new MergadoFeedItem(
            $product->getId(),
            $product->getCatnum(),
            $product->getFullname($domainConfig->getLocale()),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->categoryFacade->getCategoryNamesInPathFromRootToProductMainCategoryOnDomain($product, $domainConfig),
            $this->getProductUsp($product, $domainId),
            $this->availabilityFacade->calculateProductAvailabilityDaysForDomainId($product, $domainId),
            $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId($product, $domainId, null),
            $this->getOtherProductImages($product, $domainConfig),
            $this->productParametersBatchLoader->getProductParametersByName($product, $domainConfig),
            $currency->getCode(),
            $product->getDescription($domainId),
            $product->getBrand(),
            $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig),
            $product->isVariant() ? $product->getMainVariant()->getId() : null
        );
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return array
     */
    private function getProductUsp(Product $product, int $domainId): array
    {
        return array_filter([
            $product->getShortDescriptionUsp1($domainId),
            $product->getShortDescriptionUsp2($domainId),
            $product->getShortDescriptionUsp3($domainId),
            $product->getShortDescriptionUsp4($domainId),
            $product->getShortDescriptionUsp5($domainId),
        ]);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string[]
     */
    private function getOtherProductImages(Product $product, DomainConfig $domainConfig): array
    {
        $imageUrls = [];
        $images = $this->imageFacade->getImagesByEntityIndexedById($product, null);
        array_shift($images);
        foreach ($images as $image) {
            try {
                $imageUrls[] = $this->imageFacade->getImageUrl($domainConfig, $image, 'original');
            } catch (ImageNotFoundException $exception) {
            }
        }

        return $imageUrls;
    }
}
