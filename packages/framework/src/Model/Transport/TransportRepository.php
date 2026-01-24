<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;

class TransportRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getTransportRepository(): EntityRepository
    {
        return $this->em->getRepository(Transport::class);
    }

    public function getQueryBuilderForAll(): QueryBuilder
    {
        return $this->getTransportRepository()->createQueryBuilder('t')
            ->where('t.deleted = :deleted')->setParameter('deleted', false)
            ->orderBy('t.position')
            ->addOrderBy('t.id');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getAll(): array
    {
        return $this->getQueryBuilderForAll()->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getAllByIds(array $transportIds): array
    {
        if (count($transportIds) === 0) {
            return [];
        }

        return $this->getQueryBuilderForAll()
            ->andWhere('t.id IN (:transportIds)')->setParameter('transportIds', $transportIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getAllByDomainId(int $domainId): array
    {
        return $this->getQueryBuilderForAll()
            ->join(TransportDomain::class, 'td', Join::WITH, 't.id = td.transport AND td.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
     */
    public function getAllIncludingDeleted(): array
    {
        return $this->getTransportRepository()->findAll();
    }

    public function findById(int $id): ?Transport
    {
        return $this->getQueryBuilderForAll()
            ->andWhere('t.id = :transportId')->setParameter('transportId', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getById(int $id): Transport
    {
        $transport = $this->findById($id);

        if ($transport === null) {
            throw new TransportNotFoundException(
                'Transport with ID ' . $id . ' not found.',
            );
        }

        return $transport;
    }

    public function getOneByUuid(string $uuid): Transport
    {
        $transport = $this->getTransportRepository()->findOneBy(['uuid' => $uuid]);

        if ($transport === null) {
            throw new TransportNotFoundException('Transport with UUID ' . $uuid . ' does not exist.');
        }

        return $transport;
    }

    public function getEnabledOnDomainByUuid(string $uuid, int $domainId): Transport
    {
        $queryBuilder = $this->getTransportRepository()->createQueryBuilder('t')
            ->join(TransportDomain::class, 'td', Join::WITH, 't.id = td.transport AND td.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->where('t.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->andWhere('t.deleted = false')
            ->andWhere('td.enabled = true')
            ->andWhere('t.hidden = false');

        $transport = $queryBuilder->getQuery()->getOneOrNullResult();

        if ($transport === null) {
            throw new TransportNotFoundException('Transport with UUID ' . $uuid . ' does not exist.');
        }

        return $transport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport[]
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
                ->join('t.prices', 'tp', Join::WITH, 'tp.domainId = :domainId')
                ->andWhere('tp.maxWeight IS NULL OR tp.maxWeight >= :maxWeight')
                ->setParameter('maxWeight', $totalWeight);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function deleteAllPricesByTransport(Transport $transport): void
    {
        $this->em->createQueryBuilder()
            ->delete(TransportPrice::class, 'tp')
            ->where('tp.transport = :transport')
            ->setParameter('transport', $transport)
            ->getQuery()
            ->execute();
    }
}
