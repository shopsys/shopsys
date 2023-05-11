<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;

class CategoryResolverMap extends ResolverMap
{
    protected Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
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
            'Category' => [
                'seoH1' => function (Category $category) {
                    return $category->getSeoH1($this->domain->getId());
                },
                'seoTitle' => function (Category $category) {
                    return $category->getSeoTitle($this->domain->getId());
                },
                'seoMetaDescription' => function (Category $category) {
                    return $category->getSeoMetaDescription($this->domain->getId());
                },
            ],
        ];
    }
}
