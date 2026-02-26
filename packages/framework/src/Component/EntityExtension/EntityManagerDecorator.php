<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator as BaseEntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Repository\RepositoryFactory;
use Doctrine\Persistence\ObjectRepository;
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
    public function createQuery($dql = ''): Query
    {
        $resolvedDql = $this->entityNameResolver->resolveIn($dql);

        return parent::createQuery($resolvedDql);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getReference($entityName, $id): object
    {
        $resolvedEntityName = $this->entityNameResolver->resolve($entityName);

        return parent::getReference($resolvedEntityName, $id);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function find($entityName, $id, $lockMode = null, $lockVersion = null): ?object
    {
        $resolvedEntityName = $this->entityNameResolver->resolve($entityName);

        return parent::find($resolvedEntityName, $id, $lockMode, $lockVersion);
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
     * @param string $className
     */
    #[Override]
    public function getRepository($className): ObjectRepository
    {
        $resolvedClassName = $this->entityNameResolver->resolve($className);

        return $this->repositoryFactory->getRepository($this, $resolvedClassName);
    }

    /**
     * @param string $className
     */
    #[Override]
    public function getClassMetadata($className): ClassMetadata
    {
        $resolvedClassName = $this->entityNameResolver->resolve($className);

        return parent::getClassMetadata($resolvedClassName);
    }
}
