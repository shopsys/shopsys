<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolverMap as BaseCategoryResolverMap;

class CategoryResolverMap extends BaseCategoryResolverMap
{
    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(Domain $domain, FriendlyUrlFacade $friendlyUrlFacade)
    {
        parent::__construct($domain);

        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        $map = parent::map();

        $map['Category']['slug'] = function (Category $category) {
            return $this->getSlug($category);
        };

        return $map;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @return string
     */
    private function getSlug(Category $category): string
    {
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(
            $this->domain->getId(),
            'front_product_list',
            $category->getId()
        );

        return $friendlyUrl->getSlug();
    }
}
