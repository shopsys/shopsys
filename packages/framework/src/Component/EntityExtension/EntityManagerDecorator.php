<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator as BaseEntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Repository\RepositoryFactory;
use Override;

class EntityManagerDecorator extends BaseEntityManagerDecorator
{
    protected const int REFRESH_BATCH_SIZE = 1000;

    protected RepositoryFactory $repositoryFactory;

    public function __construct(
        EntityManagerInterface $em,
        Configuration $config,
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
        parent::__construct($em);

        $this->repositoryFactory = $config->getRepositoryFactory();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this, $this->entityNameResolver);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function createQuery(string $dql = ''): Query
    {
        $resolvedDql = $this->entityNameResolver->resolveIn($dql);

        return parent::createQuery($resolvedDql);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getReference(string $entityName, mixed $id): object|null
    {
        $resolvedEntityName = $this->entityNameResolver->resolve($entityName);

        return parent::getReference($resolvedEntityName, $id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function find(
        string $className,
        mixed $id,
        LockMode|int|null $lockMode = null,
        int|null $lockVersion = null,
    ): object|null {
        $resolvedClassName = $this->entityNameResolver->resolve($className);

        return parent::find($resolvedClassName, $id, $lockMode, $lockVersion);
    }

    public function refreshLoadedEntitiesByClassName(string $className): void
    {
        $className = $this->entityNameResolver->resolve($className);

        $identityMap = $this->getUnitOfWork()->getIdentityMap();

        if (!array_key_exists($className, $identityMap)) {
            return;
        }

        $initializedEntities = array_values(array_filter(
            $identityMap[$className],
            fn (object $entity): bool => !$this->isUninitializedObject($entity),
        ));

        if ($this->hasRefreshCascadingAssociation($className)) {
            foreach ($initializedEntities as $entity) {
                $this->refresh($entity);
            }

            return;
        }

        $entitiesBatches = array_chunk($initializedEntities, static::REFRESH_BATCH_SIZE);

        foreach ($entitiesBatches as $entitiesBatch) {
            $this->refreshEntities($className, $entitiesBatch);
        }
    }

    /**
     * @param object[] $entities
     */
    protected function refreshEntities(string $className, array $entities): void
    {
        $identifierFieldNames = $this->getClassMetadata($className)->getIdentifierFieldNames();
        $queryBuilder = $this->createQueryBuilder()
            ->select('e')
            ->from($className, 'e');

        if (count($identifierFieldNames) === 1) {
            $identifierFieldName = array_first($identifierFieldNames);
            $queryBuilder
                ->where(sprintf('e.%s IN (:identifiers)', $identifierFieldName))
                ->setParameter('identifiers', array_map(
                    fn (object $entity): mixed => $this->getUnitOfWork()->getEntityIdentifier($entity)[$identifierFieldName],
                    $entities,
                ));
        } else {
            $identifierConditions = [];

            foreach ($entities as $entityIndex => $entity) {
                $identifier = $this->getUnitOfWork()->getEntityIdentifier($entity);
                $identifierFieldConditions = [];

                foreach ($identifierFieldNames as $identifierFieldName) {
                    $parameterName = $identifierFieldName . $entityIndex;
                    $identifierFieldConditions[] = sprintf('e.%s = :%s', $identifierFieldName, $parameterName);
                    $queryBuilder->setParameter($parameterName, $identifier[$identifierFieldName]);
                }

                $identifierConditions[] = '(' . implode(' AND ', $identifierFieldConditions) . ')';
            }

            $queryBuilder->where(implode(' OR ', $identifierConditions));
        }

        $queryBuilder->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult();
    }

    protected function hasRefreshCascadingAssociation(string $className): bool
    {
        $associationMappings = $this->getClassMetadata($className)->associationMappings;

        foreach ($associationMappings as $associationMapping) {
            if ($associationMapping->isCascadeRefresh()) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getRepository(string $className): EntityRepository
    {
        $resolvedClassName = $this->entityNameResolver->resolve($className);

        return $this->repositoryFactory->getRepository($this, $resolvedClassName);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getClassMetadata(string $className): ClassMetadata
    {
        $resolvedClassName = $this->entityNameResolver->resolve($className);

        return parent::getClassMetadata($resolvedClassName);
    }
}
