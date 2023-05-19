<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CategoryBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryRepository $categoryRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CategoryRepository $categoryRepository,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $category = $this->categoryRepository->getById($routeParameters['id']);

        $categoriesInPath = $this->categoryRepository->getVisibleCategoriesInPathFromRootOnDomain(
            $category,
            $this->domain->getId(),
        );

        $breadcrumbItems = [];

        foreach ($categoriesInPath as $categoryInPath) {
            if ($categoryInPath !== $category) {
                $breadcrumbItems[] = new BreadcrumbItem(
                    $categoryInPath->getName(),
                    $routeName,
                    ['id' => $categoryInPath->getId()],
                );
            } else {
                $breadcrumbItems[] = new BreadcrumbItem(
                    $categoryInPath->getName(),
                );
            }
        }

        return $breadcrumbItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteNames()
    {
        return ['front_product_list'];
    }
}
