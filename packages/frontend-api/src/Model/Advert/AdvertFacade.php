<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Advert;

class AdvertFacade
{
    /**
     * @var \Shopsys\FrontendApiBundle\Model\Advert\AdvertRepository
     */
    protected $advertRepository;

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
     * @return \Shopsys\FrameworkBundle\Model\Advert\Advert[]
     */
    public function getVisibleAdvertsByDomainIdAndPositionName(int $domainId, string $positionName): array
    {
        return $this->advertRepository->getVisibleAdvertsByPositionNameAndDomainId($domainId, $positionName);
    }
}
