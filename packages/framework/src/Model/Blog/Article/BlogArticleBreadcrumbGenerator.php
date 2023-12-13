<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;

class BlogArticleBreadcrumbGenerator implements DomainBreadcrumbGeneratorInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = []): array
    {
        return $this->getBreadcrumbItemsOnDomain(
            $this->domain->getId(),
            $routeName,
            $routeParameters,
            $this->domain->getLocale(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbItemsOnDomain(
        int $domainId,
        string $routeName,
        array $routeParameters = [],
        ?string $locale = null,
    ): array {
        $blogArticle = $this->blogArticleRepository->getById($routeParameters['id']);

        $blogArticleMainCategoryOnDomain = $this->blogCategoryFacade->getBlogArticleMainBlogCategoryOnDomain(
            $blogArticle,
            $domainId,
        );

        $breadcrumbItems = $this->getBlogCategoryBreadcrumbItemsOnDomain($domainId, $locale, $blogArticleMainCategoryOnDomain);

        $breadcrumbItems[] = new BreadcrumbItem(
            $blogArticle->getName($locale),
        );

        return $breadcrumbItems;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    protected function getBlogCategoryBreadcrumbItemsOnDomain(
        int $domainId,
        string $locale,
        BlogCategory $blogCategory,
    ): array {
        $blogCategoriesInPath = $this->blogCategoryFacade->getVisibleBlogCategoriesInPathFromRootOnDomain(
            $blogCategory,
            $domainId,
        );

        $breadcrumbItems = [];

        foreach ($blogCategoriesInPath as $blogCategoryInPath) {
            $breadcrumbItems[] = new BreadcrumbItem(
                $blogCategoryInPath->getName($locale),
                'front_blogcategory_detail',
                ['id' => $blogCategoryInPath->getId()],
            );
        }

        return $breadcrumbItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteNames(): array
    {
        return ['front_blogarticle_detail'];
    }
}
