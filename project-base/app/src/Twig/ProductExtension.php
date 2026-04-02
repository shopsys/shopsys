<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Listing\ProductListOrderingModeForListFacade;
use Override;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Twig\ProductExtension as BaseProductExtension;
use Twig\TwigFunction;

/**
 * Class ProductExtension
 *
 * @property \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @method string getProductDisplayName(\App\Model\Product\Product $product)
 * @method \App\Model\Category\Category getProductMainCategory(\App\Model\Product\Product $product, int $domainId)
 * @method \App\Model\Category\Category|null findProductMainCategory(\App\Model\Product\Product $product, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValues(\App\Model\Product\Product $product)
 */
class ProductExtension extends BaseProductExtension
{
    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        private readonly ProductListOrderingModeForListFacade $productListOrderingModeForListFacade,
    ) {
        parent::__construct(
            $categoryFacade,
            $productCachedAttributesFacade,
        );
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        $functions = parent::getFunctions();
        $functions[] = new TwigFunction(
            'getOrderingNameByOrderingId',
            $this->getOrderingNameByOrderingId(...),
        );

        return $functions;
    }

    public function getOrderingNameByOrderingId(?string $orderingId): string
    {
        if ($orderingId === null) {
            return '';
        }

        $supportedOrderingModesNamesIndexedById = $this->productListOrderingModeForListFacade
            ->getProductListOrderingConfig()
            ->getSupportedOrderingModesNamesIndexedById();

        return $supportedOrderingModesNamesIndexedById[$orderingId] ?? t('Unsupported order') . ' ' . $orderingId;
    }
}
