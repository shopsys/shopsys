<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Advert;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Advert\AdvertRepository as FrameworkAdvertRepository;
use Shopsys\FrameworkBundle\Model\Category\Category;

class AdvertRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly FrameworkAdvertRepository $advertRepository,
    ) {
    }

    /**
     * @param string[] $positionNames
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByPositionNameAndDomainId(
        int $domainId,
        array $positionNames,
        ?Category $category = null,
    ): array {
        return $this->advertRepository->getVisibleAdvertByPositionsQueryBuilder($positionNames, $domainId, $category)->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByDomainId(int $domainId): array
    {
        return $this->advertRepository->getVisibleAdvertsQueryBuilder($domainId)->getQuery()->getResult();
    }
}
