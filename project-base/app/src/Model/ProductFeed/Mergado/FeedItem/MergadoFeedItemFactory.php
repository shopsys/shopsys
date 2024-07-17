<?php

declare(strict_types=1);

namespace App\Model\ProductFeed\Mergado\FeedItem;

use App\Component\Image\ImageFacade;
use App\Model\Category\CategoryFacade;
use App\Model\Product\Product;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityStatusEnum;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;

class MergadoFeedItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader $productUrlsBatchLoader
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader $productParametersBatchLoader
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $availabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation $productPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        private readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        private readonly ProductParametersBatchLoader $productParametersBatchLoader,
        private readonly CategoryFacade $categoryFacade,
        private readonly ProductAvailabilityFacade $availabilityFacade,
        private readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        private readonly ImageFacade $imageFacade,
        private readonly CurrencyFacade $currencyFacade,
        private readonly ProductPriceCalculation $productPriceCalculation,
        private readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        private readonly LoggerInterface $logger,
    ) {
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
        $productPrice = $this->productPriceCalculation->calculatePrice(
            $product,
            $domainId,
            $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId),
        );

        return new MergadoFeedItem(
            $product->getId(),
            $product->getCatnum(),
            $product->getFullname($domainConfig->getLocale()),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->categoryFacade->getCategoryNamesInPathFromRootToProductMainCategoryOnDomain($product, $domainConfig),
            $this->getProductUsp($product, $domainId),
            $this->availabilityFacade->getProductAvailabilityDaysByDomainId($product, $domainId),
            $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId($product, $domainId, null),
            $this->getOtherProductImages($product, $domainConfig),
            $this->productParametersBatchLoader->getProductParametersByName($product, $domainConfig),
            $currency->getCode(),
            $product->getDescription($domainId),
            $productPrice,
            [],
            $this->mapStockStatusToAvailability($this->availabilityFacade->getProductAvailabilityStatusByDomainId($product, $domainId)),
            $product->getBrand(),
            $this->productUrlsBatchLoader->getResizedProductImageUrl($product, $domainConfig),
            $product->isVariant() ? $product->getMainVariant()->getId() : null,
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
                $imageUrls[] = ImageUrlWithSizeHelper::limitSizeInImageUrl($this->imageFacade->getImageUrl($domainConfig, $image));
            } catch (ImageNotFoundException $exception) {
                $this->logger->error(sprintf('Image with id "%s" not found on filesystem', $image->getId()));
            }
        }

        return $imageUrls;
    }

    /**
     * @param string $availabilityStatus
     * @return string
     */
    private function mapStockStatusToAvailability(string $availabilityStatus): string
    {
        return match ($availabilityStatus) {
            AvailabilityStatusEnum::IN_STOCK => 'in stock',
            default => 'out of stock'
        };
    }
}
