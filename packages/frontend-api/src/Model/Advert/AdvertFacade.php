<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Advert;

use Shopsys\FrameworkBundle\Model\Category\Category;

class AdvertFacade
{
    public function __construct(protected readonly AdvertRepository $advertRepository)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByDomainId(int $domainId): array
    {
        return $this->advertRepository->getVisibleAdvertsByDomainId($domainId);
    }

    /**
     * @param string[] $positionNames
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByDomainIdAndPositionName(
        int $domainId,
        array $positionNames,
        ?Category $category = null,
    ): array {
        return $this->advertRepository->getVisibleAdvertsByPositionNameAndDomainId($domainId, $positionNames, $category);
    }
}
