<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Advert;

use Shopsys\FrameworkBundle\Model\Category\Category;

class AdvertFacade
{
    protected AdvertRepository $advertRepository;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Advert\AdvertRepository $advertRepository
     */
    public function __construct(AdvertRepository $advertRepository)
    {
        $this->advertRepository = $advertRepository;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByDomainId(int $domainId): array
    {
        return $this->advertRepository->getVisibleAdvertsByDomainId($domainId);
    }

    /**
     * @param int $domainId
     * @param string $positionName
     * @param \Shopsys\FrameworkBundle\Model\Category\Category|null $category
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByDomainIdAndPositionName(int $domainId, string $positionName, ?Category $category = null): array
    {
        return $this->advertRepository->getVisibleAdvertsByPositionNameAndDomainId($domainId, $positionName, $category);
    }
}
