<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category;

use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CategoryBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    public function __construct(
        protected readonly CategoryRepository $categoryRepository,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function getBreadcrumbItems(string $routeName, array $routeParameters = []): array
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

    #[Override]
    public function getRouteNames(): array
    {
        return ['front_product_list'];
    }
}
