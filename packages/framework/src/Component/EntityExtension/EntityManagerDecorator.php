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

        foreach ($identityMap[$className] as $entity) {
            $this->refresh($entity);
        }
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
