<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Component\Admin;


use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Shopsys\AdminBundle\Component\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Model\ProxyResolverInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractDtoManager implements ModelManagerInterface, ProxyResolverInterface
{
    public const ID_SEPARATOR = '~';

    private ManagerRegistry $registry;

    private PropertyAccessorInterface $propertyAccessor;

    /**
     * @var EntityManagerInterface[]
     */
    private array $cache = [];

    #[Required]
    public EntityNameResolver $entityNameResolver;

    public function __construct(ManagerRegistry $registry, PropertyAccessorInterface $propertyAccessor)
    {
        $this->registry = $registry;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @return mixed
     */
    abstract public function getSubjectClass();

    abstract public function createDataObject();

    abstract public function doCreate(AdminIdentifierInterface $dataObject): object;

    abstract public function doDelete(object $object);

    abstract public function doEdit(AdminIdentifierInterface $dataObject): object;

    abstract public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface;

    public function getRealClass(object $object): string
    {
        return $this->entityNameResolver->resolve($this->getSubjectClass());
    }

    public function create(object $object): void
    {
        try {
            $createdObject = $this->doCreate($object);
            $object->id = $createdObject->getId();

        } catch (\PDOException|Exception $exception) {
            throw new ModelManagerException(
                sprintf('Failed to create object: %s', ClassUtils::getClass($object)),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function update(object $object): void
    {
        try {
            $this->doEdit($object);
        } catch (\PDOException|Exception $exception) {
            throw new ModelManagerException(
                sprintf('Failed to update object: %s', ClassUtils::getClass($object)),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function delete(object $object): void
    {
        try {
            $this->doDelete($object);
        } catch (\PDOException|Exception $exception) {
            throw new ModelManagerException(
                sprintf('Failed to delete object: %s', ClassUtils::getClass($object)),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function getLockVersion(object $object)
    {
        $metadata = $this->getMetadata(ClassUtils::getClass($object));

        if (!$metadata->isVersioned || !isset($metadata->reflFields[$metadata->versionField])) {
            return null;
        }

        return $metadata->reflFields[$metadata->versionField]->getValue($object);
    }

    public function lock(object $object, ?int $expectedVersion): void
    {
        $metadata = $this->getMetadata(ClassUtils::getClass($object));

        if (!$metadata->isVersioned) {
            return;
        }

        try {
            $entityManager = $this->getEntityManager($object);
            $entityManager->lock($object, LockMode::OPTIMISTIC, $expectedVersion);
        } catch (OptimisticLockException $exception) {
            throw new LockException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @param int|string $id
     *
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     */
    public function find(string $class, $id): ?object
    {
        $values = array_combine($this->getIdentifierFieldNames($class), explode(self::ID_SEPARATOR, (string) $id));

        return $this->getEntityManager($class)->getRepository($class)->find($values);
    }

    /**
     * @phpstan-param class-string<T> $class
     * @phpstan-return array<T>
     */
    public function findBy(string $class, array $criteria = []): array
    {
        return $this->getEntityManager($class)->getRepository($class)->findBy($criteria);
    }

    /**
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     */
    public function findOneBy(string $class, array $criteria = []): ?object
    {
        return $this->getEntityManager($class)->getRepository($class)->findOneBy($criteria);
    }

    /**
     * NEXT_MAJOR: Change the visibility to private.
     *
     * @param string|object $class
     *
     * @phpstan-param class-string|object $class
     */
    public function getEntityManager($class): EntityManagerInterface
    {
        if (\is_object($class)) {
            $class = \get_class($class);
        }

        if (!isset($this->cache[$class])) {
            $em = $this->registry->getManagerForClass($class);

            if (!$em instanceof EntityManagerInterface) {
                throw new \RuntimeException(sprintf('No entity manager defined for class %s', $class));
            }

            $this->cache[$class] = $em;
        }

        return $this->cache[$class];
    }

    public function createQuery(string $class, string $alias = 'o'): BaseProxyQueryInterface
    {
        $repository = $this->getEntityManager($class)->getRepository($class);
        /** @phpstan-var ProxyQuery<T> $proxyQuery */
        $proxyQuery = new ProxyQuery($repository->createQueryBuilder($alias));

        return $proxyQuery;
    }

    public function supportsQuery(object $query): bool
    {
        return $query instanceof ProxyQuery || $query instanceof AbstractQuery || $query instanceof QueryBuilder;
    }

    public function executeQuery(object $query)
    {
        if ($query instanceof QueryBuilder) {
            return $query->getQuery()->execute();
        }

        if ($query instanceof AbstractQuery) {
            return $query->execute();
        }

        if ($query instanceof ProxyQuery) {
            /** @phpstan-var Paginator<T> $results */
            $results = $query->execute();

            return $results;
        }

        throw new \InvalidArgumentException(sprintf(
            'Argument 1 passed to %s() must be an instance of %s, %s, or %s',
            __METHOD__,
            QueryBuilder::class,
            AbstractQuery::class,
            ProxyQuery::class
        ));
    }

    public function getIdentifierValues(object $model): array
    {
        $class = ClassUtils::getClass($model);
        $metadata = $this->getMetadata($class);
        $platform = $this->getEntityManager($class)->getConnection()->getDatabasePlatform();

        $identifiers = [];

        foreach ($metadata->getIdentifierValues($model) as $name => $value) {
            if (!\is_object($value)) {
                $identifiers[] = $value;

                continue;
            }

            $fieldType = $metadata->getTypeOfField($name);
            if (null !== $fieldType && Type::hasType($fieldType)) {
                $identifiers[] = $this->getValueFromType($value, Type::getType($fieldType), $fieldType, $platform);

                continue;
            }

            $identifierMetadata = $this->getMetadata(ClassUtils::getClass($value));

            foreach ($identifierMetadata->getIdentifierValues($value) as $identifierValue) {
                $identifiers[] = $identifierValue;
            }
        }

        return $identifiers;
    }

    public function getIdentifierFieldNames(string $class): array
    {
        return $this->getMetadata($class)->getIdentifierFieldNames();
    }

    public function getNormalizedIdentifier(object $model): ?string
    {
        $values = [$model->getId()];

        return implode(self::ID_SEPARATOR, $values);
    }

    /**
     * The ORM implementation does nothing special but you still should use
     * this method when using the id in a URL to allow for future improvements.
     */
    public function getUrlSafeIdentifier(object $model): ?string
    {
        return $this->getNormalizedIdentifier($model);
    }

    /**
     * @throws \InvalidArgumentException if value passed as argument 3 is an empty array
     */
    public function addIdentifiersToQuery(string $class, BaseProxyQueryInterface $query, array $idx): void
    {
        if (!$query instanceof \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface) {
            throw new \TypeError(sprintf('The query MUST implement %s.', ProxyQueryInterface::class));
        }

        if ([] === $idx) {
            throw new \InvalidArgumentException(sprintf(
                'Array passed as argument 3 to "%s()" must not be empty.',
                __METHOD__
            ));
        }

        $fieldNames = $this->getIdentifierFieldNames($class);
        $qb = $query->getQueryBuilder();

        $prefix = uniqid();
        $sqls = [];
        foreach ($idx as $pos => $id) {
            $ids = explode(self::ID_SEPARATOR, (string) $id);

            $ands = [];
            foreach ($fieldNames as $posName => $name) {
                $parameterName = sprintf('field_%s_%s_%d', $prefix, $name, $pos);
                $ands[] = sprintf('%s.%s = :%s', current($qb->getRootAliases()), $name, $parameterName);
                $qb->setParameter($parameterName, $ids[$posName]);
            }

            $sqls[] = implode(' AND ', $ands);
        }

        $qb->andWhere(sprintf('( %s )', implode(' OR ', $sqls)));
    }

    public function batchDelete(string $class, BaseProxyQueryInterface $query): void
    {
        if (!$query instanceof ProxyQueryInterface) {
            throw new \TypeError(sprintf('The query MUST implement %s.', ProxyQueryInterface::class));
        }

        if ([] !== $query->getQueryBuilder()->getDQLPart('join')) {
            $rootAlias = current($query->getQueryBuilder()->getRootAliases());

            // Distinct is needed to iterate, even if group by is used
            // @see https://github.com/doctrine/orm/issues/5868
            $query->getQueryBuilder()->distinct();
            $query->getQueryBuilder()->select($rootAlias);
        }

        $entityManager = $this->getEntityManager($class);
        $i = 0;

        try {
            foreach ($query->getDoctrineQuery()->toIterable() as $object) {
                $entityManager->remove($object);

                if (0 === (++$i % 20)) {
                    $entityManager->flush();
                    $entityManager->clear();
                }
            }

            $entityManager->flush();
            $entityManager->clear();
        } catch (\PDOException|Exception $exception) {
            throw new ModelManagerException(
                sprintf('Failed to delete object: %s', $class),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    public function getExportFields(string $class): array
    {
        return $this->getMetadata($class)->getFieldNames();
    }

    public function reverseTransform(object $object, array $array = []): void
    {
        $metadata = $this->getMetadata(\get_class($object));

        foreach ($array as $name => $value) {
            $property = $this->getFieldName($metadata, $name);
            $this->propertyAccessor->setValue($object, $property, $value);
        }
    }

    /**
     * @phpstan-template TObject of object
     * @phpstan-param class-string<TObject> $class
     * @phpstan-return ClassMetadata<TObject>
     */
    private function getMetadata(string $class): ClassMetadata
    {
        return $this->getEntityManager($class)->getClassMetadata($class);
    }

    /**
     * @param ClassMetadata<object> $metadata
     */
    private function getFieldName(ClassMetadata $metadata, string $name): string
    {
        if (\array_key_exists($name, $metadata->fieldMappings)) {
            return $metadata->fieldMappings[$name]['fieldName'];
        }

        if (\array_key_exists($name, $metadata->associationMappings)) {
            return $metadata->associationMappings[$name]['fieldName'];
        }

        return $name;
    }

    private function getValueFromType(object $value, Type $type, string $fieldType, AbstractPlatform $platform): string
    {
        if ($platform->hasDoctrineTypeMappingFor($fieldType) &&
            'binary' === $platform->getDoctrineTypeMapping($fieldType)
        ) {
            return (string) $type->convertToPHPValue($value, $platform);
        }

        // some libraries may have `toString()` implementation
        if (\is_callable([$value, 'toString'])) {
            return $value->toString();
        }

        // final fallback to magic `__toString()` which may throw an exception in 7.4
        if (method_exists($value, '__toString')) {
            return $value->__toString();
        }

        return (string) $type->convertToDatabaseValue($value, $platform);
    }
}