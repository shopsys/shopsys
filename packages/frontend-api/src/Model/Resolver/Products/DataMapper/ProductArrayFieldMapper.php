<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper;

use DateTimeImmutable;
use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityInfo;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory;

class ProductArrayFieldMapper
{
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly FlagFacade $flagFacade,
        protected readonly BrandFacade $brandFacade,
        protected readonly ProductElasticsearchProvider $productElasticsearchProvider,
        protected readonly ParameterWithValuesFactory $parameterWithValuesFactory,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
        protected readonly DataLoaderInterface $productsSellableByIdsBatchLoader,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly DataLoaderInterface $productsVisibleByIdsBatchLoader,
        protected readonly DataLoaderInterface $productsVisibleCountByIdsBatchLoader,
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
    ) {
    }

    public function getShortDescription(array $data): ?string
    {
        return $data['short_description'];
    }

    public function getLink(array $data): string
    {
        return $this->domain->getUrl() . '/' . $data['slug'];
    }

    public function getSlug(array $data): string
    {
        return '/' . $data['slug'];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategories(array $data): array
    {
        return $this->categoryFacade->getByIds($data['categories']);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlags(array $data): array
    {
        return $this->flagFacade->getByIds($data['flags']);
    }

    public function getAvailability(array $data): ProductAvailabilityInfo
    {
        return $this->productAvailabilityFacade->createProductAvailabilityInfo(
            $data['in_stock'],
            $this->getExpectedRestockingDate($data),
            $this->domain->getId(),
        );
    }

    /**
     * @return string[]
     */
    public function getUnit(array $data): array
    {
        return ['name' => $data['unit']];
    }

    public function getStockQuantity(array $data): ?int
    {
        return $data['stock_quantity'];
    }

    public function isAllowedNegativeStock(array $data): bool
    {
        return $data['is_allowed_negative_stock'];
    }

    public function getBrand(array $data): ?Brand
    {
        if ((int)$data['brand'] > 0) {
            return $this->brandFacade->getById((int)$data['brand']);
        }

        return null;
    }

    public function isSellingDenied(array $data): bool
    {
        return $data['selling_denied'] === true;
    }

    public function isPersonalPickupOnly(array $data): bool
    {
        return $data['personal_pickup_only'] === true;
    }

    public function isCurrentlyOutOfStock(array $data): bool
    {
        if ($this->isAllowedNegativeStock($data)) {
            return false;
        }

        return ($this->getStockQuantity($data) ?? 0) <= 0;
    }

    /**
     * A date that has passed since the product was exported is treated as absent
     */
    public function getExpectedRestockingDate(array $data): ?DateTimeImmutable
    {
        $expectedRestockingDate = $data['expected_restocking_date'];

        if ($expectedRestockingDate === null) {
            return null;
        }

        $expectedRestockingDate = new DateTimeImmutable($expectedRestockingDate);

        if ($this->productAvailabilityFacade->hasExpectedRestockingDatePassed(
            $expectedRestockingDate,
            $this->domain->getId(),
        )) {
            return null;
        }

        return $expectedRestockingDate;
    }

    public function getAccessoriesPromise(array $data): Promise
    {
        return $this->productsSellableByIdsBatchLoader->load($data['accessories']);
    }

    public function getRelatedProductsPromise(array $data): Promise
    {
        return $this->productsSellableByIdsBatchLoader->load($data['related_products']);
    }

    public function getDescription(array $data): ?string
    {
        return $data['description'];
    }

    public function getParameters(array $data): array
    {
        return $this->parameterWithValuesFactory->createParametersArrayFromProductArray($data);
    }

    public function getSeoH1(array $data): ?string
    {
        return $data['seo_h1'];
    }

    public function getSeoTitle(array $data): ?string
    {
        return $data['seo_title'];
    }

    public function getSeoMetaDescription(array $data): ?string
    {
        return $data['seo_meta_description'];
    }

    public function getOrderingPriority(array $data): int
    {
        return $data['ordering_priority'];
    }

    public function getVariants(array $data): Promise
    {
        return $this->productsVisibleByIdsBatchLoader->load($data['variants']);
    }

    public function getVariantsCount(array $data): Promise
    {
        return $this->productsVisibleCountByIdsBatchLoader->load($data['variants']);
    }

    public function getMainVariant(array $data): array
    {
        return $this->productElasticsearchProvider->getVisibleProductArrayById($data['main_variant_id']);
    }

    public function getHreflangLinks(array $data): array
    {
        return $this->hreflangLinksFacade->getHreflangLinksWithIncludedDomainUrl($data['hreflang_links']);
    }

    public function isVisible(array $data): bool
    {
        $currentCustomerPricingGroup = $this->currentCustomerUser->getPricingGroup();

        foreach ($data['visibility'] as $visibility) {
            if ($currentCustomerPricingGroup->getId() === $visibility['pricing_group_id']) {
                return $visibility['visible'];
            }
        }

        return false;
    }

    public function isInquiryType(array $data): bool
    {
        return $data['product_type'] === ProductTypeEnum::TYPE_INQUIRY;
    }

    public function getProductType(array $data): string
    {
        return $data['product_type'];
    }

    public function getNamePrefix(array $data): ?string
    {
        return $data['name_prefix'];
    }

    public function getNameSuffix(array $data): ?string
    {
        return $data['name_suffix'];
    }

    public function getFullname(array $data): string
    {
        return trim(
            $data['name_prefix']
            . ' '
            . $data['name']
            . ' '
            . $data['name_suffix'],
        );
    }

    public function getStoreAvailabilities(array $data): array
    {
        return $data['store_availabilities_information'];
    }

    public function getAvailableStoresCount(array $data): ?int
    {
        return $data['available_stores_count'];
    }

    public function getProductVideos(array $data): array
    {
        return $data['product_videos'];
    }

    public function getVatPercent(array $data): string
    {
        return $data['vat_percent'];
    }

    public function getPromotionBuyQuantity(array $data): ?int
    {
        return $data['promotion']['buy_quantity'];
    }

    public function getPromotionFreeQuantity(array $data): ?int
    {
        return $data['promotion']['free_quantity'];
    }
}
