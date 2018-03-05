<?php

namespace Shopsys\FrameworkBundle\Model\Category;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CategoryBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryRepository
     */
    private $categoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        CategoryRepository $categoryRepository,
        Domain $domain
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->domain = $domain;
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = [])
    {
        $category = $this->categoryRepository->getById($routeParameters['id']);

        $categoriesInPath = $this->categoryRepository->getVisibleCategoriesInPathFromRootOnDomain(
            $category,
            $this->domain->getId()
        );

        $breadcrumbItems = [];
        foreach ($categoriesInPath as $categoryInPath) {
            if ($categoryInPath !== $category) {
                $breadcrumbItems[] = new BreadcrumbItem(
                    $categoryInPath->getName(),
                    $routeName,
                    ['id' => $categoryInPath->getId()]
                );
            } else {
                $breadcrumbItems[] = new BreadcrumbItem(
                    $categoryInPath->getName()
                );
            }
        }

        return $breadcrumbItems;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteNames()
    {
        return ['front_product_list'];
    }
}
