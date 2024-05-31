<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Override;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Symfony\Contracts\Service\ResetInterface;

class ProductExportRepository implements ResetInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade $brandCachedFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider $productExportFieldProvider
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[]|null $variantCache
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly ProductFacade $productFacade,
        protected readonly FriendlyUrlRepository $friendlyUrlRepository,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly ProductAccessoryFacade $productAccessoryFacade,
        protected readonly BrandCachedFacade $brandCachedFacade,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly ProductExportFieldProvider $productExportFieldProvider,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly ProductRepository $productRepository,
        protected ?array $variantCache = null,
    ) {
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $lastProcessedId
     * @param int $batchSize
     * @param string[] $fields
     * @return array
     */
    public function getProductsData(
        int $domainId,
        string $locale,
        int $lastProcessedId,
        int $batchSize,
        array $fields = [],
    ): array {
        $queryBuilder = $this->createQueryBuilder($domainId)
            ->andWhere('p.id > :lastProcessedId')
            ->setParameter('lastProcessedId', $lastProcessedId)
            ->setMaxResults($batchSize);

        return $this->getResults($queryBuilder, $fields, $domainId, $locale);
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int[] $productIds
     * @param string[] $fields
     * @return array
     */
    public function getProductsDataForIds(int $domainId, string $locale, array $productIds, array $fields): array
    {
        $queryBuilder = $this->createQueryBuilder($domainId)
            ->andWhere('p.id IN (:productIds)')
            ->setParameter('productIds', $productIds);

        return $this->getResults($queryBuilder, $fields, $domainId, $locale);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getProductTotalCountForDomain(int $domainId): int
    {
        $result = new QueryPaginator($this->createQueryBuilder($domainId));

        return $result->getTotalCount();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param string $locale
     * @param string[] $fields
     * @return array
     */
    protected function extractResult(Product $product, int $domainId, string $locale, array $fields): array
    {
        $exportedResult = [];

        foreach ($fields as $field) {
            $exportedResult[$field] = $this->getExportedFieldValue($domainId, $product, $locale, $field);
        }

        $this->reset();

        return $exportedResult;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $locale
     * @param string $field
     * @return mixed
     */
    protected function getExportedFieldValue(int $domainId, Product $product, string $locale, string $field): mixed
    {
        return match ($field) {
            ProductExportFieldProvider::ID => $product->getId(),
            ProductExportFieldProvider::CATNUM => $product->getCatnum(),
            ProductExportFieldProvider::PARTNO => $product->getPartno(),
            ProductExportFieldProvider::EAN => $product->getEan(),
            ProductExportFieldProvider::NAME => $product->getName($locale),
            ProductExportFieldProvider::DESCRIPTION => $product->getDescription($domainId),
            ProductExportFieldProvider::SHORT_DESCRIPTION => $product->getShortDescription($domainId),
            ProductExportFieldProvider::BRAND => $product->getBrand() ? $product->getBrand()->getId() : '',
            ProductExportFieldProvider::BRAND_NAME => $product->getBrand() ? $product->getBrand()->getName() : '',
            ProductExportFieldProvider::BRAND_URL => $this->getBrandUrlForDomainByProduct($product, $domainId),
            ProductExportFieldProvider::FLAGS => $this->extractFlags($domainId, $product),
            ProductExportFieldProvider::CATEGORIES => $this->extractCategories($domainId, $product),
            ProductExportFieldProvider::MAIN_CATEGORY_ID => $this->categoryFacade->getProductMainCategoryByDomainId(
                $product,
                $domainId,
            )->getId(),
            ProductExportFieldProvider::IN_STOCK => $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId),
            ProductExportFieldProvider::PRICES => $this->extractPrices($domainId, $product),
            ProductExportFieldProvider::PARAMETERS => $this->extractParameters($locale, $product),
            ProductExportFieldProvider::ORDERING_PRIORITY => $product->getOrderingPriority($domainId),
            ProductExportFieldProvider::CALCULATED_SELLING_DENIED => $product->getCalculatedSellingDenied(),
            ProductExportFieldProvider::SELLING_DENIED => $product->isSellingDenied(),
            ProductExportFieldProvider::AVAILABILITY => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainId),
            ProductExportFieldProvider::AVAILABILITY_DISPATCH_TIME => $this->productAvailabilityFacade->getProductAvailabilityDaysByDomainId($product, $domainId),
            ProductExportFieldProvider::IS_MAIN_VARIANT => $product->isMainVariant(),
            ProductExportFieldProvider::IS_VARIANT => $product->isVariant(),
            ProductExportFieldProvider::DETAIL_URL => $this->extractDetailUrl($domainId, $product),
            ProductExportFieldProvider::VISIBILITY => $this->extractVisibility($domainId, $product),
            ProductExportFieldProvider::UUID => $product->getUuid(),
            ProductExportFieldProvider::UNIT => $product->getUnit()->getName($locale),
            ProductExportFieldProvider::STOCK_QUANTITY => $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $domainId),
            ProductExportFieldProvider::VARIANTS => $this->extractVariantIds($product),
            ProductExportFieldProvider::MAIN_VARIANT_ID => $product->isVariant() ? $product->getMainVariant()->getId() : null,
            ProductExportFieldProvider::SEO_H1 => $product->getSeoH1($domainId),
            ProductExportFieldProvider::SEO_TITLE => $product->getSeoTitle($domainId),
            ProductExportFieldProvider::SEO_META_DESCRIPTION => $product->getSeoMetaDescription($domainId),
            ProductExportFieldProvider::ACCESSORIES => $this->extractAccessoriesIds($product),
            ProductExportFieldProvider::HREFLANG_LINKS => $this->hreflangLinksFacade->getForProduct($product, $domainId),
            default => throw new InvalidArgumentException(sprintf('There is no definition for exporting "%s" field to Elasticsearch', $field)),
        };
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function extractVariantIds(Product $product): array
    {
        $variantIds = [];

        foreach ($product->getVariants() as $variant) {
            $variantIds[] = $variant->getId();
        }

        return $variantIds;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    protected function extractDetailUrl(int $domainId, Product $product): string
    {
        $friendlyUrl = $this->friendlyUrlRepository->getMainFriendlyUrl(
            $domainId,
            'front_product_detail',
            $product->getId(),
        );

        return $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createQueryBuilder(int $domainId): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->join(ProductVisibility::class, 'prv', Join::WITH, 'prv.product = p.id')
            ->andWhere('prv.domainId = :domainId')
            ->andWhere('prv.visible = TRUE')
            ->groupBy('p.id')
            ->orderBy('p.id');

        $queryBuilder->setParameter('domainId', $domainId);

        return $queryBuilder;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function extractFlags(int $domainId, Product $product): array
    {
        $flagIds = [];

        foreach ($product->getFlags($domainId) as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function extractCategories(int $domainId, Product $product): array
    {
        $categoryIds = [];
        $categoriesIndexedByDomainId = $product->getCategoriesIndexedByDomainId();

        if (isset($categoriesIndexedByDomainId[$domainId])) {
            foreach ($categoriesIndexedByDomainId[$domainId] as $category) {
                $categoryIds[] = $category->getId();
            }
        }

        return $categoryIds;
    }

    /**
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractParameters(string $locale, Product $product): array
    {
        $parameters = [];
        $productParameterValues = $this->parameterRepository->getProductParameterValuesByProductSortedByName(
            $product,
            $locale,
        );

        foreach ($productParameterValues as $productParameterValue) {
            $parameter = $productParameterValue->getParameter();
            $parameterValue = $productParameterValue->getValue();

            if ($parameter->getName($locale) === null || $parameterValue->getLocale() !== $locale) {
                continue;
            }

            $parameters[] = [
                'parameter_id' => $parameter->getId(),
                'parameter_uuid' => $parameter->getUuid(),
                'parameter_name' => $parameter->getName($locale),
                'parameter_value_id' => $parameterValue->getId(),
                'parameter_value_uuid' => $parameterValue->getUuid(),
                'parameter_value_text' => $parameterValue->getText(),
            ];
        }

        return $parameters;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractPrices(int $domainId, Product $product): array
    {
        $prices = [];
        $productSellingPrices = $this->productFacade->getAllProductSellingPricesByDomainId($product, $domainId);

        foreach ($productSellingPrices as $productSellingPrice) {
            $sellingPrice = $productSellingPrice->getSellingPrice();
            $priceFrom = false;

            if ($sellingPrice instanceof ProductPrice) {
                $priceFrom = $sellingPrice->isPriceFrom();
            }

            $prices[] = [
                'pricing_group_id' => $productSellingPrice->getPricingGroup()->getId(),
                'price_with_vat' => (float)$sellingPrice->getPriceWithVat()->getAmount(),
                'price_without_vat' => (float)$sellingPrice->getPriceWithoutVat()->getAmount(),
                'vat' => (float)$sellingPrice->getVatAmount()->getAmount(),
                'price_from' => $priceFrom,
            ];
        }

        return $prices;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractVisibility(int $domainId, Product $product): array
    {
        $visibility = [];

        foreach ($this->productVisibilityFacade->findProductVisibilitiesByDomainIdAndProduct(
            $domainId,
            $product,
        ) as $productVisibility) {
            $visibility[] = [
                'pricing_group_id' => $productVisibility->getPricingGroup()->getId(),
                'visible' => $productVisibility->isVisible(),
            ];
        }

        return $visibility;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return string
     */
    protected function getBrandUrlForDomainByProduct(Product $product, int $domainId): string
    {
        $brand = $product->getBrand();

        if ($brand === null) {
            return '';
        }

        return $this->brandCachedFacade->getBrandUrlByDomainId($brand->getId(), $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractAccessoriesIds(Product $product): array
    {
        $accessoriesIds = [];
        $accessories = $this->productAccessoryFacade->getAllAccessories($product);

        foreach ($accessories as $accessory) {
            $accessoriesIds[] = $accessory->getAccessory()->getId();
        }

        return $accessoriesIds;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param array $fields
     * @param int $domainId
     * @param string $locale
     * @return array
     */
    protected function getResults(QueryBuilder $queryBuilder, array $fields, int $domainId, string $locale): array
    {
        $query = $queryBuilder->getQuery();

        if (count($fields) === 0) {
            $fields = $this->productExportFieldProvider->getAll();
        }

        $results = [];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        foreach ($query->getResult() as $product) {
            $results[$product->getId()] = $this->extractResult($product, $domainId, $locale, $fields);
        }

        return $results;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainVariant
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getVariantsForDefaultPricingGroup(Product $mainVariant, int $domainId): array
    {
        if ($this->variantCache === null) {
            $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
            $this->variantCache = $this->productRepository->getAllSellableVariantsByMainVariant($mainVariant, $domainId, $pricingGroup);
        }

        return $this->variantCache;
    }

    #[Override]
    public function reset(): void
    {
        $this->variantCache = null;
    }
}
