<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use BadMethodCallException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;

class ProductExportRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository
     */
    protected $parameterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository
     */
    protected $friendlyUrlRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository
     */
    protected $productVisibilityRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected $categoryFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRepository $friendlyUrlRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository $productVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null $categoryFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        ParameterRepository $parameterRepository,
        ProductFacade $productFacade,
        FriendlyUrlRepository $friendlyUrlRepository,
        Domain $domain,
        ProductVisibilityRepository $productVisibilityRepository,
        FriendlyUrlFacade $friendlyUrlFacade,
        ?CategoryFacade $categoryFacade = null
    ) {
        $this->parameterRepository = $parameterRepository;
        $this->productFacade = $productFacade;
        $this->em = $em;
        $this->friendlyUrlRepository = $friendlyUrlRepository;
        $this->domain = $domain;
        $this->productVisibilityRepository = $productVisibilityRepository;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setCategoryFacade(CategoryFacade $categoryFacade): void
    {
        if (
            $this->categoryFacade !== null
            && $this->categoryFacade !== $categoryFacade
        ) {
            throw new BadMethodCallException(sprintf(
                'Method "%s" has been already called and cannot be called multiple times.',
                __METHOD__
            ));
        }
        if ($this->categoryFacade !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $this->categoryFacade = $categoryFacade;
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
     * @param int $lastProcessedId
     * @param int $batchSize
     * @return int[]
     */
    public function getProductIdsForChanged(int $lastProcessedId, int $batchSize): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('p.id')
            ->from(Product::class, 'p')
            ->where('p.exportProduct = TRUE')
            ->andWhere('p.id > :lastProcessedId')
            ->orderBy('p.id')
            ->setParameter('lastProcessedId', $lastProcessedId)
            ->setMaxResults($batchSize)
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'id');
    }

    /**
     * @return int
     */
    public function getProductChangedCount(): int
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p.id')
            ->from(Product::class, 'p')
            ->where('p.exportProduct = TRUE');

        $result = new QueryPaginator($queryBuilder);

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
        $flagIds = $this->extractFlags($product);
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
                $domainId
            )->getId(),
            'in_stock' => $product->getCalculatedAvailability()->getDispatchTime() === 0,
            'prices' => $prices,
            'parameters' => $parameters,
            'ordering_priority' => $product->getOrderingPriority(),
            'calculated_selling_denied' => $product->getCalculatedSellingDenied(),
            'selling_denied' => $product->isSellingDenied(),
            'availability' => $product->getCalculatedAvailability()->getName($locale),
            'is_main_variant' => $product->isMainVariant(),
            'detail_url' => $detailUrl,
            'visibility' => $visibility,
            'uuid' => $product->getUuid(),
            'unit' => $product->getUnit()->getName($locale),
            'is_using_stock' => $product->isUsingStock(),
            'stock_quantity' => $product->getStockQuantity(),
            'variants' => $variantIds,
            'main_variant_id' => $product->isVariant() ? $product->getMainVariant()->getId() : null,
            'seo_h1' => $product->getSeoH1($domainId),
            'seo_title' => $product->getSeoTitle($domainId),
            'seo_meta_description' => $product->getSeoMetaDescription($domainId),
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
            $product->getId()
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
                ->where('p.variantType != :variantTypeVariant')
            ->join(ProductVisibility::class, 'prv', Join::WITH, 'prv.product = p.id')
                ->andWhere('prv.domainId = :domainId')
                ->andWhere('prv.visible = TRUE')
            ->groupBy('p.id')
            ->orderBy('p.id');

        $queryBuilder->setParameter('domainId', $domainId)
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT);

        return $queryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function extractFlags(Product $product): array
    {
        $flagIds = [];
        foreach ($product->getFlags() as $flag) {
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
            $locale
        );
        foreach ($productParameterValues as $productParameterValue) {
            $parameter = $productParameterValue->getParameter();
            $parameterValue = $productParameterValue->getValue();
            if ($parameter->getName($locale) === null || $parameterValue->getLocale() !== $locale) {
                continue;
            }

            $parameters[] = [
                'parameter_id' => $parameter->getId(),
                'parameter_name' => $parameter->getName($locale),
                'parameter_value_id' => $parameterValue->getId(),
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
        /** @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductSellingPrice[] $productSellingPrices */
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

        foreach ($this->productVisibilityRepository->findProductVisibilitiesByDomainIdAndProduct(
            $domainId,
            $product
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

        return $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityId(
            $domainId,
            'front_brand_detail',
            $brand->getId()
        );
    }
}
