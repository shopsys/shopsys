<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Products;

use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use InvalidArgumentException;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\ProductsResolver as BaseProductsResolver;

class ProductsResolver extends BaseProductsResolver
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection
     */
    public function resolveByCategoryOrReadyCategorySeoMix(Argument $argument, $categoryOrReadyCategorySeoMix): ProductConnection
    {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $category = $categoryOrReadyCategorySeoMix;
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $category = $categoryOrReadyCategorySeoMix->getCategory();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class
                ),
            );
        }

        /** @var \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnection $productConnection */
        $productConnection = $this->resolveByCategory($argument, $category);

        return $productConnection;
    }
}
