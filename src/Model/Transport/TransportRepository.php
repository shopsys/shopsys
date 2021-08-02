<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Transport\TransportDomain;
use Shopsys\FrameworkBundle\Model\Transport\TransportRepository as BaseTransportRepository;

/**
 * @method \App\Model\Transport\Transport[] getAll()
 * @method \App\Model\Transport\Transport[] getAllByIds(array $transportIds)
 * @method \App\Model\Transport\Transport[] getAllByDomainId(int $domainId)
 * @method \App\Model\Transport\Transport[] getAllIncludingDeleted()
 * @method \App\Model\Transport\Transport|null findById(int $id)
 * @method \App\Model\Transport\Transport getById(int $id)
 * @method \App\Model\Transport\Transport getOneByUuid(string $uuid)
 * @method \App\Model\Transport\Transport getEnabledOnDomainByUuid(string $uuid, int $domainId)
 */
class TransportRepository extends BaseTransportRepository
{
    /**
     * @param int $domainId
     * @param int $totalWeight
     * @return \App\Model\Transport\Transport[]
     */
    public function getAllByDomainIdAndAcceptingWeight(int $domainId, int $totalWeight): array
    {
        return $this->getQueryBuilderForAll()
            ->join(TransportDomain::class, 'td', Join::WITH, 't.id = td.transport AND td.domainId = :domainId')
            ->andWhere('t.maxWeight IS NULL OR t.maxWeight >= :maxWeight')
            ->setParameter('domainId', $domainId)
            ->setParameter('maxWeight', $totalWeight)
            ->getQuery()
            ->getResult();
    }
}
