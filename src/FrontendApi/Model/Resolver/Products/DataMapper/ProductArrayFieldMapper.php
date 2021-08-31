<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Products\DataMapper;

use App\FrontendApi\Exception\DeprecatedMethodException;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper as BaseProductArrayFieldMapper;

/**
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @property \App\Model\Product\Flag\FlagFacade $flagFacade
 * @property \App\Model\Product\Brand\BrandFacade $brandFacade
 * @property \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
 * @method __construct(\App\Model\Category\CategoryFacade $categoryFacade, \App\Model\Product\Flag\FlagFacade $flagFacade, \App\Model\Product\Brand\BrandFacade $brandFacade, \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider, \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory)
 * @method \App\Model\Category\Category[] getCategories(array $data)
 * @method \App\Model\Product\Flag\Flag[] getFlags(array $data)
 * @method \App\Model\Product\Brand\Brand|null getBrand(array $data)
 */
class ProductArrayFieldMapper extends BaseProductArrayFieldMapper
{
    /**
     * @param array $data
     * @return bool
     */
    public function isUsingStock(array $data): bool
    {
        return true;
    }

    /**
     * @param array $data
     * @return string
     */
    public function getFullname(array $data): string
    {
        return trim(
            $data['name_prefix']
            . ' '
            . $data['name']
            . ' '
            . $data['name_sufix']
        );
    }

    /**
     * @param array $data
     * @return bool
     */
    public function hasPreorder(array $data): bool
    {
        return $data['has_preorder'];
    }

    /**
     * @param array $data
     * @return bool
     */
    public function hasSaleExclusion(array $data): bool
    {
        return $data['is_sale_exclusion'];
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function getNamePrefix(array $data): ?string
    {
        return $data['name_prefix'];
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function getNameSuffix(array $data): ?string
    {
        return $data['name_sufix'];
    }

    /**
     * @param array $data
     * @return string|null
     */
    public function getPartNumber(array $data): ?string
    {
        return $data['partno'];
    }

    /**
     * @param array $data
     * @return string
     */
    public function getCatalogNumber(array $data): string
    {
        return $data['catnum'];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getExtendedAvailability(array $data): array
    {
        return [
            'name' => $data['availability'],
            'status' => $data['availability_status'],
        ];
    }

    /**
     * @param array $data
     * @return string[]
     */
    public function getAvailability(array $data): array
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param array $data
     * @return string
     */
    public function getSlug(array $data): string
    {
        return '/' . $data['slug'];
    }

    /**
     * @param array $data
     * @return array
     */
    public function getFiles(array $data): array
    {
        return array_map(
            static fn ($fileData) => [
                'anchorText' => $fileData['anchor_text'],
                'url' => $fileData['url'],
            ],
            $data['files']
        );
    }

    /**
     * @param array $data
     * @return array
     */
    public function getStoreAvailabilities(array $data): array
    {
        return $data['store_availabilities_information'];
    }

    /**
     * @param array $data
     * @return int
     */
    public function getAvailableStoresCount(array $data): int
    {
        return $data['available_stores_count'];
    }

    /**
     * @param array $data
     * @return int
     */
    public function getExposedStoresCount(array $data): int
    {
        return $data['exposed_stores_count'];
    }

    /**
     * @param array $data
     * @return int[]
     */
    public function getRelatedProducts(array $data): array
    {
        return $this->productElasticsearchProvider->getSellableProductArrayByIds($data['related_products']);
    }
}
