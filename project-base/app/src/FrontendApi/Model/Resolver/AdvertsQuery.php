<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver;

use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdvertPositionWithoutCategoryUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Advert\AdvertsQuery as BaseAdvertsQuery;

class AdvertsQuery extends BaseAdvertsQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade $advertFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        AdvertFacade $advertFacade,
        Domain $domain,
        CategoryFacade $categoryFacade,
        protected readonly ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
    ) {
        parent::__construct($advertFacade, $domain, $categoryFacade);
    }

    /**
     * @param string[]|null $positionNames
     * @param string|null $categoryOrSeoCategoryUuid
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function advertsQuery(?array $positionNames = null, ?string $categoryOrSeoCategoryUuid = null): array
    {
        if (
            $categoryOrSeoCategoryUuid === null &&
            $positionNames &&
            array_key_exists(AdvertPositionRegistry::POSITION_PRODUCT_LIST, $positionNames)
        ) {
            // TODO: Get adverts for the rest of the positions?
            throw new AdvertPositionWithoutCategoryUserError('Cannot retrieve advert on product list page without setting category.');
        }

        $domainId = $this->domain->getId();

        if ($positionNames === null || $positionNames === []) {
            return $this->advertFacade->getVisibleAdvertsByDomainId($domainId);
        }

        $category = null;

        if ($categoryOrSeoCategoryUuid !== null) {
            try {
                $category = $this->categoryFacade->getByUuid($categoryOrSeoCategoryUuid);
            } catch (CategoryNotFoundException $exception) {
                $seoMixCategory = $this->readyCategorySeoMixFacade->getByUuid($categoryOrSeoCategoryUuid);
                $category = $seoMixCategory->getCategory();
            }
        }

        return $this->advertFacade->getVisibleAdvertsByDomainIdAndPositionName($domainId, $positionNames, $category);
    }
}
