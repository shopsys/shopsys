<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo;
use Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslationsRepository;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrameworkBundle\Model\Stock\ProductStockFacade;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory;

class ProductEntityFieldMapper
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ProductCollectionFacade $productCollectionFacade,
        protected readonly ProductAccessoryFacade $productAccessoryFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ParameterWithValuesFactory $parameterWithValuesFactory,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
        protected readonly DataLoaderInterface $productsSellableByIdsBatchLoader,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly DataLoaderInterface $productsVisibleByIdsBatchLoader,
        protected readonly DataLoaderInterface $productsVisibleCountByIdsBatchLoader,
        protected readonly ProductVideoTranslationsRepository $productVideoTranslationsRepository,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ProductStockFacade $productStockFacade,
    ) {
    }

    public function getShortDescription(Product $product): ?string
    {
        return $product->getShortDescription($this->domain->getId());
    }

    public function getLink(Product $product): string
    {
        $absoluteUrlsIndexedByProductId = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId(
            [$product->getId()],
            $this->domain->getCurrentDomainConfig(),
        );

        return $absoluteUrlsIndexedByProductId[$product->getId()];
    }

    public function getSlug(Product $product): string
    {
        return '/' . $this->friendlyUrlFacade->getMainFriendlyUrlSlug($this->domain->getId(), 'front_product_detail', $product->getId());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategories(Product $product): array
    {
        return $product->getCategoriesIndexedByDomainId()[$this->domain->getId()];
    }

    /**
     * @return array{name: string, status: string}
     */
    public function getAvailability(Product $product): array
    {
        return [
            'name' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId(
                $product,
                $this->domain->getId(),
            ),
            'status' => $this->productAvailabilityFacade->getProductAvailabilityStatusByDomainId(
                $product,
                $this->domain->getId(),
            ),
        ];
    }

    public function isSellingDenied(Product $product): bool
    {
        return $product->isCalculatedSellingDenied($this->domain->getId()) === true;
    }

    public function isCurrentlyOutOfStock(Product $product): bool
    {
        if ($product->isAllowedNegativeStock()) {
            return false;
        }

        return !$this->productStockFacade->isProductAvailableOnDomain($product, $this->domain->getId());
    }

    public function getAccessoriesPromise(Product $product): Promise
    {
        $accessories = $this->productAccessoryFacade->getOfferedAccessories(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
        );

        $accessoriesIds = array_map(fn (Product $accessory) => $accessory->getId(), $accessories);

        return $this->productsSellableByIdsBatchLoader->load($accessoriesIds);
    }

    public function getDescription(Product $product): ?string
    {
        return $product->getDescription($this->domain->getId());
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues[]
     */
    public function getParameters(Product $product): array
    {
        return $this->parameterWithValuesFactory->createMultipleForProduct($product);
    }

    public function getSeoH1(Product $product): ?string
    {
        return $product->getSeoH1($this->domain->getId());
    }

    public function getSeoTitle(Product $product): ?string
    {
        return $product->getSeoTitle($this->domain->getId());
    }

    public function getSeoMetaDescription(Product $product): ?string
    {
        return $product->getSeoMetaDescription($this->domain->getId());
    }

    public function getOrderingPriority(Product $product): int
    {
        return $product->getOrderingPriority($this->domain->getId());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\HreflangLink[]
     */
    public function getHreflangLinks(Product $product): array
    {
        return $this->hreflangLinksFacade->getForProduct($product, $this->domain->getId());
    }

    public function isVisible(Product $product): bool
    {
        $productVisibility = $this->productVisibilityFacade->getProductVisibility(
            $product,
            $this->currentCustomerUser->getPricingGroup(),
            $this->domain->getId(),
        );

        return $productVisibility->isVisible();
    }

    public function getVariants(Product $product): Promise
    {
        $variantIds = array_map(static fn (Product $variant) => $variant->getId(), $product->getVariants());

        return $this->productsVisibleByIdsBatchLoader->load($variantIds);
    }

    public function getVariantsCount(Product $product): Promise
    {
        $variantIds = array_map(static fn (Product $variant) => $variant->getId(), $product->getVariants());

        return $this->productsVisibleCountByIdsBatchLoader->load($variantIds);
    }

    public function isInquiryType(Product $product): bool
    {
        return $product->getProductType() === ProductTypeEnum::TYPE_INQUIRY;
    }

    public function getProductType(Product $product): string
    {
        return $product->getProductType();
    }

    public function getNameSuffix(Product $product): ?string
    {
        return $product->getNameSuffix($this->domain->getLocale());
    }

    public function getNamePrefix(Product $product): ?string
    {
        return $product->getNamePrefix($this->domain->getLocale());
    }

    public function getFullName(Product $product): string
    {
        return $product->getFullName($this->domain->getLocale());
    }

    public function getStockQuantity(Product $product): ?int
    {
        return $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $this->domain->getId());
    }

    public function isAllowedNegativeStock(Product $product): bool
    {
        return $product->isAllowedNegativeStock();
    }

    public function getStoreAvailabilities(Product $product): array
    {
        $storeAvailabilitiesInformation = $this->productAvailabilityFacade->getProductStoresAvailabilitiesInformationByDomainIdIndexedByStoreId(
            $product,
            $this->domain->getId(),
        );

        $result = [];

        foreach ($storeAvailabilitiesInformation as $storeAvailabilityInformation) {
            $result[] = [
                'store_name' => $storeAvailabilityInformation->getStoreName(),
                'store_id' => $storeAvailabilityInformation->getStoreId(),
                'availability_information' => $storeAvailabilityInformation->getAvailabilityInformation(),
                'availability_status' => $storeAvailabilityInformation->getAvailabilityStatus(),
            ];
        }

        return $result;
    }

    public function getAvailableStoresCount(Product $product): ?int
    {
        return $this->productAvailabilityFacade->getAvailableStoresCount(
            $product,
            $this->domain->getId(),
        );
    }

    public function getProductVideos(Product $product): array
    {
        $locale = $this->domain->getLocale();

        return array_map(function (ProductVideo $productVideo) use ($locale) {
            return [
                'token' => $productVideo->getVideoToken(),
                'description' => $this->productVideoTranslationsRepository->findByProductVideoIdAndLocale($productVideo->getId(), $locale),
            ];
        }, $product->getProductVideos());
    }

    public function getVatPercent(Product $product): string
    {
        return $product->getVatForDomain($this->domain->getId())->getPercent();
    }

    public function getPromotionBuyQuantity(Product $product): ?int
    {
        return $product->getPromotionXy($this->domain->getId())?->getBuyQuantity();
    }

    public function getPromotionFreeQuantity(Product $product): ?int
    {
        return $product->getPromotionXy($this->domain->getId())?->getFreeQuantity();
    }
}
