<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Advert;

use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrameworkBundle\Model\Advert\AdvertPositionRegistry;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class AdvertsQuery extends AbstractQuery
{
    use SetterInjectionTrait;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Advert\AdvertFacade $advertFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null $categoryFacade
     */
    public function __construct(
        protected readonly AdvertFacade $advertFacade,
        protected readonly Domain $domain,
        protected ?CategoryFacade $categoryFacade = null,
    ) {
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setCategoryFacade(CategoryFacade $categoryFacade): void
    {
        $this->setDependency($categoryFacade, 'categoryFacade');
    }

    /**
     * @param string|null $positionName
     * @param string|null $categoryUuid
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function advertsQuery(?string $positionName = null, ?string $categoryUuid = null): array
    {
        if ($positionName === AdvertPositionRegistry::POSITION_PRODUCT_LIST && $categoryUuid === null) {
            DeprecationHelper::trigger('Retrieving advert on product list page without setting category is deprecated and will be disabled in next major.');
        }

        $domainId = $this->domain->getId();
        if ($positionName === null) {
            return $this->advertFacade->getVisibleAdvertsByDomainId($domainId);
        }

        $category = null;
        if ($categoryUuid !== null) {
            $category = $this->categoryFacade->getByUuid($categoryUuid);
        }

        return $this->advertFacade->getVisibleAdvertsByDomainIdAndPositionName($domainId, $positionName, $category);
    }
}
