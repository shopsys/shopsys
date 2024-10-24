<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade;
use Shopsys\FrameworkBundle\Component\Breadcrumb\Exception\UnableToGenerateBreadcrumbItemsException;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Breadcrumb\Exception\UnableToGenerateBreadcrumbItemsUserError;

class BreadcrumbQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbFacade $breadcrumbFacade
     */
    public function __construct(
        protected readonly BreadcrumbFacade $breadcrumbFacade,
    ) {
    }

    /**
     * @param int $id
     * @param string $routeName
     * @return array<int, array{name: string, slug: string}>
     */
    public function breadcrumbQuery(int $id, string $routeName): array
    {
        try {
            return $this->breadcrumbFacade->getBreadcrumbOnCurrentDomain(
                $id,
                $routeName,
            );
        } catch (UnableToGenerateBreadcrumbItemsException) {
            throw new UnableToGenerateBreadcrumbItemsUserError(sprintf('Unable to generate breadcrumb items for route "%s" with ID %d.', $routeName, $id));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|\Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix $categoryOrReadyCategorySeoMix
     * @return array[]
     */
    public function categoryBreadcrumbQuery(Category|ReadyCategorySeoMix $categoryOrReadyCategorySeoMix): array
    {
        if ($categoryOrReadyCategorySeoMix instanceof Category) {
            $categoryId = $categoryOrReadyCategorySeoMix->getId();
        } else {
            $categoryId = $categoryOrReadyCategorySeoMix->getCategory()->getId();
        }

        return $this->breadcrumbQuery($categoryId, 'front_product_list');
    }
}
