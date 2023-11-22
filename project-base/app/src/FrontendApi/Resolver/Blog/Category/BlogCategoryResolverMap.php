<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Blog\Category\BlogCategoryFacade;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class BlogCategoryResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \App\Model\Blog\Article\Elasticsearch\BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly BlogCategoryFacade $blogCategoryFacade,
        private readonly BlogArticleElasticsearchFacade $blogArticleElasticsearchFacade,
    ) {
    }

    /**
     * @return mixed[]
     */
    protected function map(): array
    {
        return [
            'BlogCategory' => [
                'seoH1' => function (BlogCategory $blogCategory) {
                    return $blogCategory->getSeoH1($this->domain->getId());
                },
                'seoTitle' => function (BlogCategory $blogCategory) {
                    return $blogCategory->getSeoTitle($this->domain->getId());
                },
                'seoMetaDescription' => function (BlogCategory $blogCategory) {
                    return $blogCategory->getSeoMetaDescription($this->domain->getId());
                },
                'parent' => function (BlogCategory $blogCategory) {
                    $parent = $blogCategory->getParent();

                    return $parent !== null && $parent->getParent() !== null ? $parent : null;
                },
                'slug' => function (BlogCategory $blogCategory) {
                    return '/' . $this->friendlyUrlFacade->getMainFriendlyUrlSlug($this->domain->getId(), 'front_blogcategory_detail', $blogCategory->getId());
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
                    return $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoryAndDomainId(
                        $this->blogCategoryFacade->getRootBlogCategory(),
                        $this->domain->getId(),
                    );
                },
                'articlesTotalCount' => function (BlogCategory $blogCategory) {
                    return $this->blogArticleElasticsearchFacade->getByBlogCategoryTotalCount($blogCategory);
                },
            ],
        ];
    }
}
