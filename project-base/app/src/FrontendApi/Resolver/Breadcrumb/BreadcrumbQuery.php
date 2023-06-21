<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Breadcrumb;

use App\Component\Breadcrumb\BreadcrumbFacade;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use InvalidArgumentException;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class BreadcrumbQuery extends AbstractQuery
{
    /**
     * @param \App\Component\Breadcrumb\BreadcrumbFacade $breadcrumbFacade
     */
    public function __construct(
        private readonly BreadcrumbFacade $breadcrumbFacade,
    ) {
    }

    /**
     * @param int $id
     * @param string $routeName
     * @return array<int, array{name: string, slug: string}>
     */
    public function breadcrumbQuery(int $id, string $routeName): array
    {
        return $this->breadcrumbFacade->getBreadcrumbOnCurrentDomain(
            $id,
            $routeName,
        );
    }

    /**
     * @param \App\Model\Category\Category|\App\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return array[]
     */
    public function categoryBreadcrumbQuery($categoryOrReadyCategorySeoMix): array
    {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $categoryId = $categoryOrReadyCategorySeoMix->getId();
        } elseif ($categoryOrReadyCategorySeoMix instanceof ReadyCategorySeoMix) {
            $categoryId = $categoryOrReadyCategorySeoMix->getCategory()->getId();
        } else {
            throw new InvalidArgumentException(
                sprintf(
                    'The "$categoryOrReadyCategorySeoMix" argument must be an instance of "%s" or "%s".',
                    Category::class,
                    ReadyCategorySeoMix::class,
                ),
            );
        }

        return $this->breadcrumbQuery($categoryId, 'front_product_list');
    }
}
