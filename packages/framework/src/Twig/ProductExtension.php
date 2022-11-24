<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ProductExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade
     */
    protected $productCachedAttributesFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        ProductCachedAttributesFacade $productCachedAttributesFacade
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
    }

    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('productDisplayName', [$this, 'getProductDisplayName']),
            new TwigFilter('productListDisplayName', [$this, 'getProductListDisplayName']),
        ];
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'productMainCategory',
                [$this, 'getProductMainCategory']
            ),
            new TwigFunction(
                'findProductMainCategory',
                [$this, 'findProductMainCategory']
            ),
            new TwigFunction(
                'getProductSellingPrice',
                [$this, 'getProductSellingPrice']
            ),
            new TwigFunction(
                'getProductParameterValues',
                [$this, 'getProductParameterValues']
            ),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'product';
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    public function getProductDisplayName(Product $product): string
    {
        if ($product->getName() === null) {
            return t('ID %productId%', [
                '%productId%' => $product->getId(),
            ]);
        }

        return $product->getName();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    public function getProductListDisplayName(Product $product): string
    {
        if ($product->getName() === null) {
            return t('Product name in default language is not entered');
        }

        return $product->getName();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category
     */
    public function getProductMainCategory(Product $product, int $domainId): \Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    public function findProductMainCategory(Product $product, int $domainId): ?\Shopsys\FrameworkBundle\Model\Category\Category
    {
        return $this->categoryFacade->findProductMainCategoryByDomainId($product, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     */
    public function getProductSellingPrice(Product $product): ?\Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
    {
        return $this->productCachedAttributesFacade->getProductSellingPrice($product);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValues(Product $product): array
    {
        return $this->productCachedAttributesFacade->getProductParameterValues($product);
    }
}
