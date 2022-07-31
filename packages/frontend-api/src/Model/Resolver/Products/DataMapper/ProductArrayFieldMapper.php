<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory;

class ProductArrayFieldMapper
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected CategoryFacade $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     */
    protected FlagFacade $flagFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    protected BrandFacade $brandFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider
     */
    protected ProductElasticsearchProvider $productElasticsearchProvider;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory
     */
    protected ParameterWithValuesFactory $parameterWithValuesFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     * @param \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        FlagFacade $flagFacade,
        BrandFacade $brandFacade,
        ProductElasticsearchProvider $productElasticsearchProvider,
        ParameterWithValuesFactory $parameterWithValuesFactory
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->flagFacade = $flagFacade;
        $this->brandFacade = $brandFacade;
        $this->productElasticsearchProvider = $productElasticsearchProvider;
        $this->parameterWithValuesFactory = $parameterWithValuesFactory;
    }

    /**
     * @param array{short_description: ?string} $data
     * @return string|null
     */
    public function getShortDescription(array $data): ?string
    {
        return $data['short_description'];
    }

    /**
     * @param array{detail_url: string} $data
     * @return string
     */
    public function getLink(array $data): string
    {
        return $data['detail_url'];
    }

    /**
     * @param array{categories: int[]} $data
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    public function getCategories(array $data): array
    {
        return $this->categoryFacade->getByIds($data['categories']);
    }

    /**
     * @param array{flags: int[]} $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlags(array $data): array
    {
        return $this->flagFacade->getByIds($data['flags']);
    }

    /**
     * @param array{availability: string} $data
     * @return array{name: string}
     */
    public function getAvailability(array $data): array
    {
        return ['name' => $data['availability']];
    }

    /**
     * @param array{unit: string} $data
     * @return array{name: string}
     */
    public function getUnit(array $data): array
    {
        return ['name' => $data['unit']];
    }

    /**
     * @param array{stock_quantity: ?int} $data
     * @return int|null
     */
    public function getStockQuantity(array $data): ?int
    {
        return $data['stock_quantity'];
    }

    /**
     * @param array{is_using_stock: bool} $data
     * @return bool
     */
    public function isUsingStock(array $data): bool
    {
        return $data['is_using_stock'];
    }

    /**
     * @param array{brand: int|numeric-string} $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null
     */
    public function getBrand(array $data): ?Brand
    {
        if ((int)$data['brand'] > 0) {
            return $this->brandFacade->getById((int)$data['brand']);
        }

        return null;
    }

    /**
     * @param array{calculated_selling_denied: bool} $data
     * @return bool
     */
    public function isSellingDenied(array $data): bool
    {
        return $data['calculated_selling_denied'];
    }

    /**
     * @param array{accessories: int[]} $data
     * @return array<int, mixed>
     */
    public function getAccessories(array $data): array
    {
        return $this->productElasticsearchProvider->getSellableProductArrayByIds($data['accessories']);
    }

    /**
     * @param array{description: ?string} $data
     * @return string|null
     */
    public function getDescription(array $data): ?string
    {
        return $data['description'];
    }

    /**
     * @param array{parameters: mixed} $data
     * @return array<int, mixed>
     */
    public function getParameters(array $data): array
    {
        return $this->parameterWithValuesFactory->createParametersArrayFromProductArray($data);
    }

    /**
     * @param array{seo_h1: ?string} $data
     * @return string|null
     */
    public function getSeoH1(array $data): ?string
    {
        return $data['seo_h1'];
    }

    /**
     * @param array{seo_title: ?string} $data
     * @return string|null
     */
    public function getSeoTitle(array $data): ?string
    {
        return $data['seo_title'];
    }

    /**
     * @param array{seo_meta_description: ?string} $data
     * @return string|null
     */
    public function getSeoMetaDescription(array $data): ?string
    {
        return $data['seo_meta_description'];
    }

    /**
     * @param array{ordering_priority: int} $data
     * @return int
     */
    public function getOrderingPriority(array $data): int
    {
        return $data['ordering_priority'];
    }

    /**
     * @param array{variants: int[]} $data
     * @return array<int, mixed>
     */
    public function getVariants(array $data): array
    {
        return $this->productElasticsearchProvider->getSellableProductArrayByIds($data['variants']);
    }

    /**
     * @param array{main_variant_id: int} $data
     * @return array<int, mixed>
     */
    public function getMainVariant(array $data): array
    {
        return $this->productElasticsearchProvider->getVisibleProductArrayById($data['main_variant_id']);
    }
}
