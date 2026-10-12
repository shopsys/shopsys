<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class BlogCategoryResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly DataLoaderInterface $blogCategoryMainFriendlyUrlSlugBatchLoader,
        protected readonly DataLoaderInterface $blogCategoryBreadcrumbBatchLoader,
        protected readonly DataLoaderInterface $blogCategoryChildrenBatchLoader,
        protected readonly DataLoaderInterface $blogCategoriesBatchLoader,
        protected readonly DataLoaderInterface $blogCategorySeoAttributesBatchLoader,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'BlogCategory' => [
                'seo' => function (BlogCategory $blogCategory) {
                    return $this->blogCategorySeoAttributesBatchLoader->load($blogCategory);
                },
                'parent' => function (BlogCategory $blogCategory) {
                    $parent = $blogCategory->getParent();

                    if ($parent === null) {
                        return null;
                    }

                    return $this->blogCategoriesBatchLoader->load($parent->getId());
                },
                'slug' => function (BlogCategory $blogCategory) {
                    return $this->blogCategoryMainFriendlyUrlSlugBatchLoader
                        ->load($blogCategory->getId())
                        ->then(fn (string $mainFriendlyUrlSlug): string => '/' . $mainFriendlyUrlSlug);
                },
                'breadcrumb' => function (BlogCategory $blogCategory) {
                    return $this->blogCategoryBreadcrumbBatchLoader->load($blogCategory);
                },
                'link' => function (BlogCategory $blogCategory) {
                    return $this->blogCategoryMainFriendlyUrlSlugBatchLoader
                        ->load($blogCategory->getId())
                        ->then(fn (string $mainFriendlyUrlSlug): string => $this->friendlyUrlFacade->getAbsoluteUrlByMainFriendlyUrlSlug(
                            $this->domain->getId(),
                            $mainFriendlyUrlSlug,
                        ));
                },
                'children' => function (BlogCategory $blogCategory) {
                    return $this->blogCategoryChildrenBatchLoader->load($blogCategory);
                },
                'blogCategoriesTree' => function () {
                    return $this->blogCategoryFacade->getAllVisibleChildrenWithRootByDomainId(
                        $this->domain->getId(),
                    );
                },
                'articlesTotalCount' => function (BlogCategory $blogCategory) {
                    return $this->blogArticleElasticsearchFacade->getByBlogCategoryTotalCount($blogCategory);
                },
                'hreflangLinks' => function (BlogCategory $blogCategory) {
                    return $this->hreflangLinksFacade->getForBlogCategory($blogCategory, $this->domain->getId());
                },
            ],
        ];
    }
}
