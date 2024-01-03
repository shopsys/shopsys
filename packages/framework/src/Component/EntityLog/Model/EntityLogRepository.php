<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Model;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EntityLogRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(EntityLog::class);
    }

    /**
     * @param string $entityName
     * @param int $entityId
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilderByEntityNameAndEntityId(string $entityName, int $entityId): QueryBuilder
    {
        return $this->getRepository()->createQueryBuilder('el')
            ->select('el')
            ->where('el.entityName = :entityName AND el.entityId = :entityId')
            ->orWhere('el.parentEntityName = :entityName AND el.parentEntityId = :entityId')
            ->orderBy('el.id', 'desc')
            ->setParameter('entityName', $entityName)
            ->setParameter('entityId', $entityId);
    }

    /**
     * @param string $entityName
     * @param int $entityId
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
