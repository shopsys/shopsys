<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class ProductExportRepository
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
    ) {
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int $lastProcessedId
     * @param int $batchSize
     * @return array
     */
    public function getProductsData(int $domainId, string $locale, int $lastProcessedId, int $batchSize): array
    {
        $queryBuilder = $this->createQueryBuilder($domainId)
            ->andWhere('p.id > :lastProcessedId')
            ->setParameter('lastProcessedId', $lastProcessedId)
            ->setMaxResults($batchSize);

        $query = $queryBuilder->getQuery();

        $results = [];
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        foreach ($query->getResult() as $product) {
            $results[$product->getId()] = $this->extractResult($product, $domainId, $locale);
        }

        return $results;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param int[] $productIds
     * @return array
     */
    public function getProductsDataForIds(int $domainId, string $locale, array $productIds): array
    {
        $queryBuilder = $this->createQueryBuilder($domainId)
            ->andWhere('p.id IN (:productIds)')
            ->setParameter('productIds', $productIds);

        $query = $queryBuilder->getQuery();

        $result = [];
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        foreach ($query->getResult() as $product) {
            $result[$product->getId()] = $this->extractResult($product, $domainId, $locale);
        }

        return $result;
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
     * @return array
     */
    protected function extractResult(Product $product, int $domainId, string $locale): array
    {
        $flagIds = $this->extractFlags($domainId, $product);
        $categoryIds = $this->extractCategories($domainId, $product);
        $parameters = $this->extractParameters($locale, $product);
        $prices = $this->extractPrices($domainId, $product);
        $visibility = $this->extractVisibility($domainId, $product);
        $detailUrl = $this->extractDetailUrl($domainId, $product);
        $variantIds = $this->extractVariantIds($product);

        return [
            'id' => $product->getId(),
            'catnum' => $product->getCatnum(),
            'partno' => $product->getPartno(),
            'ean' => $product->getEan(),
            'name' => $product->getName($locale),
            'description' => $product->getDescription($domainId),
            'short_description' => $product->getShortDescription($domainId),
            'brand' => $product->getBrand() ? $product->getBrand()->getId() : '',
            'brand_name' => $product->getBrand() ? $product->getBrand()->getName() : '',
            'brand_url' => $this->getBrandUrlForDomainByProduct($product, $domainId),
            'flags' => $flagIds,
            'categories' => $categoryIds,
            'main_category_id' => $this->categoryFacade->getProductMainCategoryByDomainId(
                $product,
                $domainId,
            )->getId(),
            'in_stock' => $this->productAvailabilityFacade->isProductAvailableOnDomainCached($product, $domainId),
            'prices' => $prices,
            'parameters' => $parameters,
            'ordering_priority' => $product->getOrderingPriority($domainId),
            'calculated_selling_denied' => $product->getCalculatedSellingDenied(),
            'selling_denied' => $product->isSellingDenied(),
            'availability' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $domainId),
            'availability_dispatch_time' => $this->productAvailabilityFacade->getProductAvailabilityDaysByDomainId($product, $domainId),
            'is_main_variant' => $product->isMainVariant(),
            'is_variant' => $product->isVariant(),
            'detail_url' => $detailUrl,
            'visibility' => $visibility,
            'uuid' => $product->getUuid(),
            'unit' => $product->getUnit()->getName($locale),
            'is_using_stock' => $product->isUsingStock(),
            'stock_quantity' => $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $domainId),
            'variants' => $variantIds,
            'main_variant_id' => $product->isVariant() ? $product->getMainVariant()->getId() : null,
            'seo_h1' => $product->getSeoH1($domainId),
            'seo_title' => $product->getSeoTitle($domainId),
            'seo_meta_description' => $product->getSeoMetaDescription($domainId),
            'accessories' => $this->extractAccessoriesIds($product),
            'hreflang_links' => $this->hreflangLinksFacade->getForProduct($product, $domainId),
        ];
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
}
