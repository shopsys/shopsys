<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;

class ProductFilterFacade
{
    use SetterInjectionTrait;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper
     */
    protected ProductFilterDataMapper $productFilterDataMapper;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer
     */
    protected ProductFilterNormalizer $productFilterNormalizer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory
     */
    protected ProductFilterConfigFactory $productFilterConfigFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig[]
     */
    protected array $productFilterConfigCache = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory|null
     */
    protected ?ProductFilterDataFactory $productFilterDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer $productFilterNormalizer
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory $productFilterConfigFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory|null $productFilterDataFactory
     */
    public function __construct(
        Domain $domain,
        ProductFilterDataMapper $productFilterDataMapper,
        ProductFilterNormalizer $productFilterNormalizer,
        ProductFilterConfigFactory $productFilterConfigFactory,
        ?ProductFilterDataFactory $productFilterDataFactory = null
    ) {
        $this->productFilterDataMapper = $productFilterDataMapper;
        $this->productFilterNormalizer = $productFilterNormalizer;
        $this->productFilterConfigFactory = $productFilterConfigFactory;
        $this->domain = $domain;
        $this->productFilterDataFactory = $productFilterDataFactory;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setParameterBag(ProductFilterDataFactory $productFilterDataFactory): void
    {
        $this->setDependency($productFilterDataFactory, 'productFilterDataFactory');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function getProductFilterConfigForAll(): ProductFilterConfig
    {
        $cacheKey = 'all';

        if (!array_key_exists($cacheKey, $this->productFilterConfigCache)) {
            $this->productFilterConfigCache[$cacheKey] = $this->productFilterConfigFactory->createForAll(
                $this->domain->getId(),
                $this->domain->getLocale()
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function getProductFilterConfigForBrand(Brand $brand): ProductFilterConfig
    {
        $cacheKey = 'brand_' . $brand->getId();

        if (!array_key_exists($cacheKey, $this->productFilterConfigCache)) {
            $this->productFilterConfigCache[$cacheKey] = $this->productFilterConfigFactory->createForBrand(
                $this->domain->getId(),
                $this->domain->getLocale(),
                $brand
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig
     */
    public function getProductFilterConfigForCategory(Category $category): ProductFilterConfig
    {
        $cacheKey = 'category_' . $category->getId();

        if (!array_key_exists($cacheKey, $this->productFilterConfigCache)) {
            $this->productFilterConfigCache[$cacheKey] = $this->productFilterConfigFactory->createForCategory(
                $this->domain->getId(),
                $this->domain->getLocale(),
                $category
            );
        }

        return $this->productFilterConfigCache[$cacheKey];
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    protected function getValidatedProductFilterData(Argument $argument, ProductFilterConfig $productFilterConfig): ProductFilterData
    {
        $productFilterData = $this->productFilterDataMapper->mapFrontendApiFilterToProductFilterData(
            $argument['filter']
        );

        $this->productFilterNormalizer->removeExcessiveFilters($productFilterData, $productFilterConfig);

        return $productFilterData;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForAll(Argument $argument): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForAll();

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForCategory(Argument $argument, Category $category): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForCategory($category);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
     */
    public function getValidatedProductFilterDataForBrand(Argument $argument, Brand $brand): ProductFilterData
    {
        if ($argument['filter'] === null) {
            return $this->productFilterDataFactory->create();
        }

        $productFilterConfig = $this->getProductFilterConfigForBrand($brand);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }
}
