<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Flag;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class FlagFacade
{
    public function __construct(private FlagRepository $flagRepository)
    {
    }

    /**
     * @param int[][] $flagsIds
     */
    public function getFlagsByIds(array $flagsIds, DomainConfig $domainConfig): array
    {
        return $this->flagRepository->getFlagsByIds($flagsIds, $domainConfig);
    }
}
