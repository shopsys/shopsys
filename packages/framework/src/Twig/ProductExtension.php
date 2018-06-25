<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class ProductExtension extends \Twig_Extension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser
     */
    private $productPriceCalculationForUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository
     */
    private $parameterRepository;

    public function __construct(
        CategoryFacade $categoryFacade,
        ProductPriceCalculationForUser $productPriceCalculationForUser,
        Localization $localization,
        ParameterRepository $parameterRepository
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->productPriceCalculationForUser = $productPriceCalculationForUser;
        $this->localization = $localization;
        $this->parameterRepository = $parameterRepository;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('productDisplayName', [$this, 'getProductDisplayName']),
            new Twig_SimpleFilter('productListDisplayName', [$this, 'getProductListDisplayName']),
        ];
    }

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'product';
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    public function getProductDisplayName(Product $product)
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
    public function getProductListDisplayName(Product $product)
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
    public function getProductMainCategory(Product $product, $domainId)
    {
        return $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Category\Category|null
     */
    public function findProductMainCategory(Product $product, $domainId)
    {
        return $this->categoryFacade->findProductMainCategoryByDomainId($product, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     */
    public function getProductSellingPrice(Product $product)
    {
        if ($product->isSellingPriceLoaded()) {
            return $product->getSellingPrice();
        }
        try {
            $productPrice = $this->productPriceCalculationForUser->calculatePriceForCurrentUser($product);
        } catch (\Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException $ex) {
            $productPrice = null;
        }
        $product->setSellingPrice($productPrice);

        return $productPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValues(Product $product)
    {
        if ($product->areProductParameterValuesLoaded()) {
            return $product->getProductParameterValues();
        }
        $locale = $this->localization->getLocale();

        $productParameterValues = $this->parameterRepository->getProductParameterValuesByProductSortedByName($product, $locale);
        foreach ($productParameterValues as $index => $productParameterValue) {
            $parameter = $productParameterValue->getParameter();
            if ($parameter->getName() === null
                || $productParameterValue->getValue()->getLocale() !== $locale
            ) {
                unset($productParameterValues[$index]);
            }
        }
        $product->setParameterValues($productParameterValues);

        return $productParameterValues;
    }
}
