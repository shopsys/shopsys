<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class AdvertsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade $advertFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        protected readonly AdvertFacade $advertFacade,
        protected readonly Domain $domain,
        protected readonly CategoryFacade $categoryFacade,
    ) {
    }

    /**
     * @param string[]|null $positionNames
     * @param string|null $categoryUuid
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function advertsQuery(?array $positionNames = null, ?string $categoryUuid = null): array
    {
        if (
            $categoryUuid === null &&
            $positionNames &&
            array_key_exists(AdvertPositionRegistry::POSITION_PRODUCT_LIST, $positionNames)
        ) {
            throw new AdvertPositionWithoutCategoryUserError('Cannot retrieve advert on product list page without setting category.');
        }

        $domainId = $this->domain->getId();

        if ($positionNames === null || $positionNames === []) {
            return $this->advertFacade->getVisibleAdvertsByDomainId($domainId);
        }

        $category = null;

        if ($categoryUuid !== null) {
            $category = $this->categoryFacade->getByUuid($categoryUuid);
        }

        return $this->advertFacade->getVisibleAdvertsByDomainIdAndPositionName($domainId, $positionNames, $category);
    }
}
