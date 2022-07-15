<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Blog\Category\BlogCategory;
use App\Model\Blog\Category\BlogCategoryFacade;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\Cdn\Component\Domain\Domain;

class BlogCategoryResolverMap extends ResolverMap
{
    /**
     * @var \Shopsys\Cdn\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \App\Model\Blog\Category\BlogCategoryFacade
     */
    private BlogCategoryFacade $blogCategoryFacade;

    /**
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     */
    public function __construct(
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade,
        BlogCategoryFacade $blogCategoryFacade
    ) {
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->blogCategoryFacade = $blogCategoryFacade;
    }

    /**
     * @return array
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
                    $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($this->domain->getId(), 'front_blogcategory_detail', $blogCategory->getId());

                    return '/' . $friendlyUrl->getSlug();
                },
                'link' => function (BlogCategory $blogCategory) {
                    $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl($this->domain->getId(), 'front_blogcategory_detail', $blogCategory->getId());

                    return $this->friendlyUrlFacade->getAbsoluteUrlByFriendlyUrl($friendlyUrl);
                },
                'children' => function (BlogCategory $blogCategory) {
                    return $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoryAndDomainId(
                        $blogCategory,
                        $this->domain->getId()
                    );
                },
                'blogCategoriesTree' => function () {
                    return $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoryAndDomainId(
                        $this->blogCategoryFacade->getRootBlogCategory(),
                        $this->domain->getId()
                    );
                },
            ],
        ];
    }
}
