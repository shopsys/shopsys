<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class AdvertsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly AdvertFacade $advertFacade,
        protected readonly Domain $domain,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
    ) {
    }

    /**
     * @param string[]|null $positionNames
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function advertsQuery(?array $positionNames = null, ?string $categoryOrSeoCategoryUuid = null): array
    {
        $domainId = $this->domain->getId();

        if ($positionNames === null || $positionNames === []) {
            return $this->advertFacade->getVisibleAdvertsByDomainId($domainId);
        }

        $category = null;

        if ($categoryOrSeoCategoryUuid !== null) {
            try {
                $category = $this->categoryFacade->getByUuid($categoryOrSeoCategoryUuid);
            } catch (CategoryNotFoundException) {
                $seoMixCategory = $this->readyCategorySeoMixFacade->getByUuid($categoryOrSeoCategoryUuid);
                $category = $seoMixCategory->getCategory();
            }
        }

        return $this->advertFacade->getVisibleAdvertsByDomainIdAndPositionName($domainId, $positionNames, $category);
    }
}
