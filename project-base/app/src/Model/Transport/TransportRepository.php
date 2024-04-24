<?php

declare(strict_types=1);

namespace App\Model\Transport;

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
 * @method \App\Model\Transport\Transport[] getAllWithEagerLoadedDomainsAndTranslations(\Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig, int|null $totalWeight = null)
 */
class TransportRepository extends BaseTransportRepository
{
}
