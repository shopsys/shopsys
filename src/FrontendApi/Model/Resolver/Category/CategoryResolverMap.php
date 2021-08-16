<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Category;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolverMap as BaseCategoryResolverMap;

class CategoryResolverMap extends BaseCategoryResolverMap
{
    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private ReadyCategorySeoMixFacade $readyCategorySeoMixFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(Domain $domain, FriendlyUrlFacade $friendlyUrlFacade, ReadyCategorySeoMixFacade $readyCategorySeoMixFacade)
    {
        parent::__construct($domain);

        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
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
        $map['Category']['readyCategorySeoMixLinks'] = function (Category $category) {
            return $this->getLinksByCategory($category);
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

    /**
     * @param \App\Model\Category\Category $category
     * @return array<array<string, string>>
     */
    private function getLinksByCategory(Category $category): array
    {
        return array_map(
            fn (ReadyCategorySeoMix $readyCategorySeoMix) => [
                'name' => $readyCategorySeoMix->getH1(),
                'slug' => $this->friendlyUrlFacade->getMainFriendlyUrl(
                    $this->domain->getId(),
                    'front_category_seo',
                    $readyCategorySeoMix->getId()
                )->getSlug(),
            ],
            $this->readyCategorySeoMixFacade->getAllForShowInCategory($category, $this->domain->getId())
        );
    }
}
