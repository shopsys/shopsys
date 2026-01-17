<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Override;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BlogCategoryBreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
    public function __construct(protected BlogCategoryRepository $blogCategoryRepository, protected Domain $domain)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getBreadcrumbItems($routeName, array $routeParameters = []): array
    {
        $blogCategory = $this->blogCategoryRepository->getById($routeParameters['id']);

        $blogCategoriesInPath = $this->blogCategoryRepository->getVisibleBlogCategoriesInPathFromRootOnDomain(
            $blogCategory,
            $this->domain->getId(),
        );

        $breadcrumbItems = [];

        foreach ($blogCategoriesInPath as $blogCategoryInPath) {
            if ($blogCategoryInPath !== $blogCategory) {
                $breadcrumbItems[] = new BreadcrumbItem(
                    $blogCategoryInPath->getName(),
                    $routeName,
                    ['id' => $blogCategoryInPath->getId()],
                );
            } else {
                $breadcrumbItems[] = new BreadcrumbItem(
                    $blogCategoryInPath->getName(),
                );
            }
        }

        return $breadcrumbItems;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRouteNames(): array
    {
        return ['front_blogcategory_detail'];
    }
}
