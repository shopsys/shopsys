<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

use App\Component\Breadcrumb\DomainBreadcrumbGeneratorInterface;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BlogArticleBreadcrumbGenerator implements DomainBreadcrumbGeneratorInterface
{
    /**
     * @var \App\Model\Blog\Article\BlogArticleRepository
     */
    private BlogArticleRepository $blogArticleRepository;

    /**
     * @var \App\Model\Blog\Category\BlogCategoryFacade
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \App\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        BlogArticleRepository $blogArticleRepository,
        BlogCategoryFacade $blogCategoryFacade,
        Domain $domain
    ) {
        $this->blogArticleRepository = $blogArticleRepository;
        $this->blogCategoryFacade = $blogCategoryFacade;
        $this->domain = $domain;
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbItems($routeName, array $routeParameters = []): array
    {
        return $this->getBreadcrumbItemsOnDomain(
            $this->domain->getId(),
            $routeName,
            $routeParameters,
            $this->domain->getLocale()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbItemsOnDomain(int $domainId, string $routeName, array $routeParameters = [], ?string $locale = null): array
    {
        $blogArticle = $this->blogArticleRepository->getById($routeParameters['id']);

        $blogArticleMainCategoryOnDomain = $this->blogCategoryFacade->getBlogArticleMainBlogCategoryOnDomain(
            $blogArticle,
            $domainId
        );

        $breadcrumbItems = $this->getBlogCategoryBreadcrumbItemsOnDomain($domainId, $locale, $blogArticleMainCategoryOnDomain);

        $breadcrumbItems[] = new BreadcrumbItem(
            $blogArticle->getName($locale)
        );

        return $breadcrumbItems;
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @return \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbItem[]
     */
    private function getBlogCategoryBreadcrumbItemsOnDomain(int $domainId, string $locale, BlogCategory $blogCategory): array
    {
        $blogCategoriesInPath = $this->blogCategoryFacade->getVisibleBlogCategoriesInPathFromRootOnDomain(
            $blogCategory,
            $domainId
        );

        $breadcrumbItems = [];
        foreach ($blogCategoriesInPath as $blogCategoryInPath) {
            $breadcrumbItems[] = new BreadcrumbItem(
                $blogCategoryInPath->getName($locale),
                'front_blogcategory_detail',
                ['id' => $blogCategoryInPath->getId()]
            );
        }

        return $breadcrumbItems;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteNames(): array
    {
        return ['front_blogarticle_detail'];
    }
}
