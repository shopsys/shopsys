<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
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
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $totalWeight
     * @return \App\Model\Transport\Transport[]
     */
    public function getAllWithEagerLoadedDomainsAndTranslations(
        DomainConfig $domainConfig,
        ?int $totalWeight = null,
    ): array {
        $queryBuilder = $this->getQueryBuilderForAll()
            ->addSelect('td')
            ->addSelect('tt')
            ->join('t.domains', 'td', Join::WITH, 'td.domainId = :domainId')
            ->join('t.translations', 'tt', Join::WITH, 'tt.locale = :locale')
            ->setParameter('domainId', $domainConfig->getId())
            ->setParameter('locale', $domainConfig->getLocale());

        if ($totalWeight !== null) {
            $queryBuilder
                ->andWhere('t.maxWeight IS NULL OR t.maxWeight >= :maxWeight')
                ->setParameter('maxWeight', $totalWeight);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
