<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Product\Detail;

use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade;

class ProductDetailViewElasticsearchFacade implements ProductDetailViewFacadeInterface
{
    /**
     * @var \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewFactory
     */
    protected $productDetailViewFactory;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewElasticsearchFactory
     */
    protected $productDetailViewElasticsearchFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade
     */
    protected $productOnCurrentDomainElasticFacade;

    /**
     * @param \Shopsys\ReadModelBundle\Product\Detail\ProductDetailViewElasticsearchFactory $productDetailViewElasticsearchFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade
     */
    public function __construct(
        ProductDetailViewElasticsearchFactory $productDetailViewElasticsearchFactory,
        ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade
    ) {
        $this->productDetailViewElasticsearchFactory = $productDetailViewElasticsearchFactory;
        $this->productOnCurrentDomainElasticFacade = $productOnCurrentDomainElasticFacade;
    }

    /**
     * @param int $productId
     * @return \Shopsys\ReadModelBundle\Product\Detail\ProductDetailView
     */
    public function getVisibleProductDetail(int $productId): ProductDetailView
    {
        return $this->productDetailViewElasticsearchFactory->createFromProductArray(
            $this->productOnCurrentDomainElasticFacade->getVisibleProductArrayById($productId)
        );
    }
}
