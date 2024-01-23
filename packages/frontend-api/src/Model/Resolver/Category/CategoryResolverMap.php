<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;

class CategoryResolverMap extends ResolverMap
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly HreflangLinksFacade $hreflangLinksFacade,
    ) {
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
                'hreflangLinks' => function (Category $category) {
                    return $this->hreflangLinksFacade->getForCategory($category, $this->domain->getId());
                },
            ],
        ];
    }
}
