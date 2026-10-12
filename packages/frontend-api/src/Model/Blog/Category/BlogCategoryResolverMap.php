<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;

class BlogCategoryResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly DataLoaderInterface $blogCategorySlugBatchLoader,
        protected readonly DataLoaderInterface $blogCategoryBreadcrumbBatchLoader,
        protected readonly DataLoaderInterface $blogCategoryChildrenBatchLoader,
        protected readonly DataLoaderInterface $blogCategoriesBatchLoader,
        protected readonly DataLoaderInterface $blogCategorySeoAttributesBatchLoader,
        protected readonly DataLoaderInterface $blogArticlesTotalCountByBlogCategoryBatchLoader,
        protected readonly DataLoaderInterface $blogCategoryHreflangLinksBatchLoader,
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
                    return $this->blogCategorySlugBatchLoader->load($blogCategory->getId());
                },
                'breadcrumb' => function (BlogCategory $blogCategory) {
                    return $this->blogCategoryBreadcrumbBatchLoader->load($blogCategory);
                },
                'link' => function (BlogCategory $blogCategory) {
                    return $this->blogCategorySlugBatchLoader
                        ->load($blogCategory->getId())
                        ->then(fn (string $slug): string => $this->domain->getUrl() . $slug);
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
                    return $this->blogArticlesTotalCountByBlogCategoryBatchLoader->load($blogCategory);
                },
                'hreflangLinks' => function (BlogCategory $blogCategory) {
                    return $this->blogCategoryHreflangLinksBatchLoader->load($blogCategory);
                },
            ],
        ];
    }
}
