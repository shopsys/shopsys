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
use Shopsys\FrontendApiBundle\Model\Seo\SeoAttributesResultFactory;

class BlogCategoryResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
        protected readonly DataLoaderInterface $blogCategorySlugBatchLoader,
        protected readonly SeoAttributesResultFactory $seoAttributesResultFactory,
    ) {
    }

    #[Override]
    protected function map(): array
    {
        return [
            'BlogCategory' => [
                'seo' => function (BlogCategory $blogCategory) {
                    return $this->seoAttributesResultFactory->createFromSeoAttributes(
                        $blogCategory->getSeoAttributes($this->domain->getId()),
                        $blogCategory->getDescription($this->domain->getLocale()),
                    );
                },
                'parent' => function (BlogCategory $blogCategory) {
                    return $blogCategory->getParent();
                },
                'slug' => function (BlogCategory $blogCategory) {
                    return $this->blogCategorySlugBatchLoader->load($blogCategory->getId());
                },
                'link' => function (BlogCategory $blogCategory) {
                    return $this->friendlyUrlFacade->getAbsoluteUrlByRouteNameAndEntityIdOnCurrentDomain('front_blogcategory_detail', $blogCategory->getId());
                },
                'children' => function (BlogCategory $blogCategory) {
                    return $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoryAndDomainId(
                        $blogCategory,
                        $this->domain->getId(),
                    );
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
