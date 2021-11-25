<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Blog\Category\BlogCategory;
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
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(Domain $domain, FriendlyUrlFacade $friendlyUrlFacade)
    {
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
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
            ],
        ];
    }
}
