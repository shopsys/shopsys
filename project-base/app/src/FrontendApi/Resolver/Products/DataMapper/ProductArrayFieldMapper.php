<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\DataMapper;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper as BaseProductArrayFieldMapper;

/**
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @property \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
 * @property \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
 * @method \App\Model\Category\Category[] getCategories( $data)
 * @method \App\Model\Product\Flag\Flag[] getFlags( $data)
 * @method \App\Model\Product\Brand\Brand|null getBrand(array $data)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class ProductArrayFieldMapper extends BaseProductArrayFieldMapper
{
    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        FlagFacade $flagFacade,
        BrandFacade $brandFacade,
        ProductElasticsearchProvider $productElasticsearchProvider,
        ParameterWithValuesFactory $parameterWithValuesFactory,
        ProductFrontendLimitProvider $productFrontendLimitProvider,
        DataLoaderInterface $productsSellableByIdsBatchLoader,
        CurrentCustomerUser $currentCustomerUser,
        DataLoaderInterface $productsVisibleByIdsBatchLoader,
        DataLoaderInterface $productsVisibleCountByIdsBatchLoader,
        Domain $domain,
        HreflangLinksFacade $hreflangLinksFacade,
        private DataLoaderInterface $categoriesBatchLoader,
        private DataLoaderInterface $flagsBatchLoader,
        private DataLoaderInterface $brandsBatchLoader,
    ) {
        parent::__construct(
            $categoryFacade,
            $flagFacade,
            $brandFacade,
            $productElasticsearchProvider,
            $parameterWithValuesFactory,
            $productFrontendLimitProvider,
            $productsSellableByIdsBatchLoader,
            $currentCustomerUser,
            $productsVisibleByIdsBatchLoader,
            $productsVisibleCountByIdsBatchLoader,
            $domain,
            $hreflangLinksFacade,
        );
    }

    public function getPartNumber(array $data): ?string
    {
        return $data['partno'];
    }

    public function getCatalogNumber(array $data): string
    {
        return $data['catnum'];
    }

    /**
     * @return int[]
     */
    public function getRelatedProducts(array $data): array
    {
        return $this->productElasticsearchProvider->getSellableProductArrayByIds(
            $data['related_products'],
            $this->productFrontendLimitProvider->getProductsFrontendLimit(),
        );
    }

    public function getBreadcrumb(array $data): array
    {
        return $data['breadcrumb'];
    }

    public function getCategoriesPromise(array $data): Promise
    {
        return $this->categoriesBatchLoader->load($data['categories']);
    }

    public function getFlagsPromise(array $data): Promise
    {
        return $this->flagsBatchLoader->load($data['flags']);
    }

    public function getRelatedProductsPromise(array $data): Promise
    {
        return $this->productsSellableByIdsBatchLoader->load($data['related_products']);
    }

    public function getBrandPromise(array $data): ?Promise
    {
        $brandId = $data['brand'];

        return $brandId !== '' ? $this->brandsBatchLoader->load($brandId) : null;
    }

    public function isMainVariant(array $data): bool
    {
        return $data['is_main_variant'];
    }
}
