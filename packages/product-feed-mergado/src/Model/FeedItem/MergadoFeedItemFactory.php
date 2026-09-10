<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\MergadoBundle\Model\FeedItem;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductParametersBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductUrlsBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;

class MergadoFeedItemFactory
{
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly ProductUrlsBatchLoader $productUrlsBatchLoader,
        protected readonly ProductParametersBatchLoader $productParametersBatchLoader,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly ProductAvailabilityFacade $availabilityFacade,
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly ImageFacade $imageFacade,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function createForProduct(Product $product, DomainConfig $domainConfig): MergadoFeedItem
    {
        $domainId = $domainConfig->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        $productPricesResult = $this->productPriceCalculationForCustomerUser
            ->calculatePricesForCustomerUserAndDomainId($product, $domainId);

        return new MergadoFeedItem(
            $product->getId(),
            $product->getCatnum(),
            $product->getFullName($domainConfig->getLocale()),
            $this->productUrlsBatchLoader->getProductUrl($product, $domainConfig),
            $this->categoryFacade->getCategoryNamesInPathFromRootToProductMainCategoryOnDomain($product, $domainConfig),
            $this->getProductUsp($product, $domainId),
            // a dispatch time over the limit resolves to a concrete date — even though the Mergado specification
            // declares DELIVERY_DAYS as a number of days only, mirroring the Zbozi feed logic is deliberate here
            $this->availabilityFacade->getProductAvailabilityDaysOrDateForFeedsByDomainId($product, $domainId),
            $productPricesResult->sellingProductPrice->getPrice(),
            $this->getOtherProductImages($product, $domainConfig),
            $this->productParametersBatchLoader->getProductParametersByName($product, $domainConfig),
            $currency->getCode(),
            $product->getDescription($domainId),
            [],
            $this->availabilityFacade->isProductAvailableOnDomainCached($product, $domainId) ? 'in stock' : 'out of stock',
            $product->getBrand(),
            $this->productUrlsBatchLoader->getProductImageUrl($product, $domainConfig),
            $product->isVariant() ? $product->getMainVariant()->getId() : null,
            $this->getDeliveryId($product),
            $this->getDeliveryPrice($product, $domainConfig),
        );
    }

    protected function getDeliveryPrice(Product $product, DomainConfig $domainConfig): ?Money
    {
        if (!$product->isElectronicGiftVoucher()) {
            return null;
        }

        return $this->transportFacade->findEmailTransportLowestPriceWithVatByDomainId($domainConfig->getId());
    }

    protected function getDeliveryId(Product $product): ?string
    {
        return $product->isElectronicGiftVoucher() ? 'ONLINE' : null;
    }

    protected function getProductUsp(Product $product, int $domainId): array
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
     * @return string[]
     */
    protected function getOtherProductImages(Product $product, DomainConfig $domainConfig): array
    {
        $imageUrls = [];
        $images = $this->imageFacade->getImagesByEntityIndexedById($product, null);
        array_shift($images);

        foreach ($images as $image) {
            try {
                $imageUrls[] = $this->imageFacade->getImageUrl($domainConfig, $image);
            } catch (ImageNotFoundException $exception) {
                $this->logger->error(sprintf('Image with id "%s" not found on filesystem', $image->getId()));
            }
        }

        return $imageUrls;
    }
}
