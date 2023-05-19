<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\Decorator\EntityManagerDecorator as BaseEntityManagerDecorator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Repository\RepositoryFactory;

class EntityManagerDecorator extends BaseEntityManagerDecorator
{
    protected RepositoryFactory $repositoryFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Doctrine\ORM\Configuration $config
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
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
    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this, $this->entityNameResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($dql = ''): Query
    {
        $resolvedDql = $this->entityNameResolver->resolveIn($dql);

        return parent::createQuery($resolvedDql);
    }

    /**
     * {@inheritdoc}
     */
    public function getReference($entityName, $id): ?object
    {
        $resolvedEntityName = $this->entityNameResolver->resolve($entityName);

        return parent::getReference($resolvedEntityName, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getPartialReference($entityName, $identifier): ?object
    {
        $resolvedEntityName = $this->entityNameResolver->resolve($entityName);

        return parent::getPartialReference($resolvedEntityName, $identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function find($entityName, $id, $lockMode = null, $lockVersion = null): ?object
    {
        $resolvedEntityName = $this->entityNameResolver->resolve($entityName);

        return parent::find($resolvedEntityName, $id, $lockMode, $lockVersion);
    }

    /**
     * {@inheritdoc}
     */
    public function clear($objectName = null): void
    {
        if ($objectName !== null) {
            $objectName = $this->entityNameResolver->resolve($objectName);
        }

        parent::clear($objectName);
    }

    /**
     * @param string $className
     */
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
     * @return \Doctrine\Persistence\ObjectRepository
     */
    public function getRepository($className)
    {
        $resolvedClassName = $this->entityNameResolver->resolve($className);

        return $this->repositoryFactory->getRepository($this, $resolvedClassName);
    }
}
