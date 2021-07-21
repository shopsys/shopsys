<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Blog\Category;

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
     * @param \Shopsys\Cdn\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
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
            ],
        ];
    }
}
