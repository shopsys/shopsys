<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig[]
     */
    protected array $productFilterConfigCache = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper $productFilterDataMapper
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer $productFilterNormalizer
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory $productFilterConfigFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ProductFilterDataMapper $productFilterDataMapper,
        protected readonly ProductFilterNormalizer $productFilterNormalizer,
        protected readonly ProductFilterConfigFactory $productFilterConfigFactory,
    ) {
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
                $this->domain->getLocale(),
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
                $brand,
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
                $category,
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
            $argument['filter'],
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
            return new ProductFilterData();
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
            return new ProductFilterData();
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
            return new ProductFilterData();
        }

        $productFilterConfig = $this->getProductFilterConfigForBrand($brand);

        return $this->getValidatedProductFilterData($argument, $productFilterConfig);
    }
}
