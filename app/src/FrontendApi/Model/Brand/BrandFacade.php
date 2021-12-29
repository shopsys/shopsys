<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Brand;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class BrandFacade
{
    /**
     * @var \App\FrontendApi\Model\Brand\BrandRepository
     */
    private BrandRepository $brandRepository;

    /**
     * @param \App\FrontendApi\Model\Brand\BrandRepository $brandRepository
     */
    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \App\Model\Product\Brand\Brand[]
     */
    public function getAllWithDomainsAndTranslations(DomainConfig $domainConfig): array
    {
        return $this->brandRepository->getAllWithDomainsAndTranslations($domainConfig);
    }

    /**
     * @param int[] $brandIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return array<int, \App\Model\Product\Brand\Brand|null>
     */
    public function getByIds(array $brandIds, DomainConfig $domainConfig): array
    {
        return $this->brandRepository->getByIds($brandIds, $domainConfig);
    }
}
