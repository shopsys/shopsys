<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductResolverMap extends ResolverMap
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade
     */
    protected $productCollectionFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     */
    public function __construct(Domain $domain, ProductCollectionFacade $productCollectionFacade)
    {
        $this->domain = $domain;
        $this->productCollectionFacade = $productCollectionFacade;
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Product' => [
                self::RESOLVE_TYPE => function (Product $product) {
                    if ($product->isMainVariant()) {
                        return 'MainVariant';
                    } elseif ($product->isVariant()) {
                        return 'Variant';
                    } else {
                        return 'RegularProduct';
                    }
                },
            ],
            'RegularProduct' => $this->mapProduct(),
            'Variant' => $this->mapProduct(),
            'MainVariant' => $this->mapProduct(),
        ];
    }

    /**
     * @return array
     */
    protected function mapProduct(): array
    {
        return [
            'shortDescription' => function (Product $product) {
                return $product->getShortDescription($this->domain->getId());
            },
            'link' => function (Product $product) {
                return $this->getProductLink($product);
            },
            'categories' => function (Product $product) {
                return $product->getCategoriesIndexedByDomainId()[$this->domain->getId()];
            },
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    protected function getProductLink(Product $product): string
    {
        $absoluteUrlsIndexedByProductId = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId([$product->getId()], $this->domain->getCurrentDomainConfig());

        return $absoluteUrlsIndexedByProductId[$product->getId()];
    }
}
