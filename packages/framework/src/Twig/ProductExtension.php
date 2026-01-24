<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use Override;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ProductExtension extends AbstractExtension
{
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
    ) {
    }

    /**
     * @return \Twig\TwigFilter[]
     */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('productDisplayName', $this->getProductDisplayName(...)),
        ];
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'productMainCategory',
                $this->getProductMainCategory(...),
            ),
            new TwigFunction(
                'findProductMainCategory',
                $this->findProductMainCategory(...),
            ),
            new TwigFunction(
                'getProductParameterValues',
                $this->getProductParameterValues(...),
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

    public function getProductMainCategory(Product $product, int $domainId): Category
    {
        return $this->categoryFacade->getProductMainCategoryByDomainId($product, $domainId);
    }

    public function findProductMainCategory(
        Product $product,
        int $domainId,
    ): ?Category {
        return $this->categoryFacade->findProductMainCategoryByDomainId($product, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValues(Product $product): array
    {
        return $this->productCachedAttributesFacade->getProductParameterValues($product);
    }
}
