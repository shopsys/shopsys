<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class ProductExtension extends \Twig_Extension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    private $productCachedAttributesFacade;

    public function __construct(
        CategoryFacade $categoryFacade,
        ProductCachedAttributesFacade $productCachedAttributesFacade
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
    }
    
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('productDisplayName', [$this, 'getProductDisplayName']),
            new Twig_SimpleFilter('productListDisplayName', [$this, 'getProductListDisplayName']),
        ];
    }
    
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'productMainCategory',
                [$this, 'getProductMainCategory']
            ),
            new Twig_SimpleFunction(
                'findProductMainCategory',
                [$this, 'findProductMainCategory']
            ),
            new Twig_SimpleFunction(
                'getProductSellingPrice',
                [$this, 'getProductSellingPrice']
            ),
            new Twig_SimpleFunction(
                'getProductParameterValues',
                [$this, 'getProductParameterValues']
            ),
        ];
    }

    public function getName(): string
    {
        return 'product';
    }

    public function getProductDisplayName(Product $product): string
    {
        if ($product->getName() === null) {
            return t('ID %productId%', [
                '%productId%' => $product->getId(),
            ]);
        }

        return $product->getName();
    }

    public function getProductListDisplayName(Product $product): string
    {
        if ($product->getName() === null) {
            return t('Product name in default language is not entered');
        }

        return $product->getName();
    }

    /**
     * @param int $domainId
     */
    public function getProductMainCategory(Product $product, $domainId): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);
    }

    /**
     * @param int $domainId
     */
    public function findProductMainCategory(Product $product, $domainId): ?\Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->categoryFacade->findProductMainCategoryByDomainId($product, $domainId);
    }

    public function getProductSellingPrice(Product $product): ?\Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
    {
        return $this->productCachedAttributesFacade->getProductSellingPrice($product);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValues(Product $product): array
    {
        return $this->productCachedAttributesFacade->getProductParameterValues($product);
    }
}
