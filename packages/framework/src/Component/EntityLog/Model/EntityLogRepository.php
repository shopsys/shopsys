<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EntityLogRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(EntityLog::class);
    }

    public function getQueryBuilderByEntityNameAndEntityId(string $entityName, int $entityId): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('el')
            ->select('el')
            ->where('el.entityName = :entityName AND el.entityId = :entityId')
            ->orWhere('el.parentEntityName = :entityName AND el.parentEntityId = :entityId')
            ->orderBy('el.id', 'desc')
            ->setParameter('entityName', $entityName)
            ->setParameter('entityId', $entityId);
    }

    public function getCountByEntityNameAndEntityId(string $entityName, int $entityId): int
    {
        return (int)$this->getRepository()->createQueryBuilder('el')
            ->select('COUNT(el.id)')
            ->where('el.entityName = :entityName AND el.entityId = :entityId')
            ->orWhere('el.parentEntityName = :entityName AND el.parentEntityId = :entityId')
            ->setParameter('entityName', $entityName)
            ->setParameter('entityId', $entityId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog[]
     */
    public function getEntityLogsFromLastLogCollection(string $entityName, int $entityId): array
    {
        /** @var \Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLog|null $lastEntityLog */
        $lastEntityLog = $this->getQueryBuilderByEntityNameAndEntityId($entityName, $entityId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($lastEntityLog) {
            return $this->getRepository()->findBy([
                'logCollectionNumber' => $lastEntityLog->getLogCollectionNumber(),
            ]);
        }

        return [];
    }
}
