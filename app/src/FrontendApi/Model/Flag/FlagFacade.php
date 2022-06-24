<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Flag;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class FlagFacade
{
    /**
     * @var \App\FrontendApi\Model\Flag\FlagRepository
     */
    private FlagRepository $flagRepository;

    /**
     * @param \App\FrontendApi\Model\Flag\FlagRepository $flagRepository
     */
    public function __construct(FlagRepository $flagRepository)
    {
        $this->flagRepository = $flagRepository;
    }

    /**
     * @param int[][] $flagsIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return array
     */
    public function getFlagsByIds(array $flagsIds, DomainConfig $domainConfig): array
    {
        return $this->flagRepository->getFlagsByIds($flagsIds, $domainConfig);
    }

    /**
     * @param string[] $flagUuids
     * @return int[]
     */
    public function getFlagIdsByUuids(array $flagUuids): array
    {
        return $this->flagRepository->getFlagIdsByUuids($flagUuids);
    }
}
