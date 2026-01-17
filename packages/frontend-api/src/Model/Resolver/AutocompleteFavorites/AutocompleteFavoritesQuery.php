<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\AutocompleteFavorites;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Autocomplete\AutocompleteFavoriteFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class AutocompleteFavoritesQuery extends AbstractQuery
{
    protected const int PRODUCT_LIMIT = 5;
    protected const int CATEGORY_LIMIT = 3;
    protected const int BRAND_LIMIT = 3;

    public function __construct(
        protected readonly AutocompleteFavoriteFacade $autocompleteFavoriteFacade,
        protected readonly ProductElasticsearchProvider $productElasticsearchProvider,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array{products: array[], categories: \Shopsys\FrameworkBundle\Model\Category\Category[], brands: \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]}
     */
    public function autocompleteFavoritesQuery(): array
    {
        $domainId = $this->domain->getId();

        return [
            'products' => $this->getListableFavoriteProducts($domainId),
            'categories' => $this->autocompleteFavoriteFacade->getVisibleCategoriesForDomain($domainId, self::CATEGORY_LIMIT),
            'brands' => $this->autocompleteFavoriteFacade->getBrandsForDomain($domainId, self::BRAND_LIMIT),
        ];
    }

    /**
     * @return array[]
     */
    protected function getListableFavoriteProducts(int $domainId): array
    {
        $productIds = $this->autocompleteFavoriteFacade->getProductIdsForDomain($domainId);

        if (count($productIds) === 0) {
            return [];
        }

        return $this->productElasticsearchProvider->getListableProductArrayByIds(
            $productIds,
            self::PRODUCT_LIMIT,
        );
    }
}
