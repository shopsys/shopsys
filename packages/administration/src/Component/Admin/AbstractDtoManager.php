<?php

declare(strict_types=1);

namespace Shopsys\Administration\Component\Admin;

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
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Shopsys\Administration\Component\Security\AdminIdentifierInterface;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface as BaseProxyQueryInterface;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\AdminBundle\Model\ProxyResolverInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use function array_key_exists;
use function get_class;
use function is_callable;
use function is_object;

/**
 * @phpstan-template T of object
 */
abstract class AbstractDtoManager implements ModelManagerInterface, ProxyResolverInterface
{
    public const string ID_SEPARATOR = '~';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface[]
     */
    protected array $cache = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Doctrine\Persistence\ManagerRegistry $registry
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly ManagerRegistry $registry,
        protected readonly PropertyAccessorInterface $propertyAccessor,
    ) {
    }

    /**
     * @return string
     */
    abstract public function getSubjectClass(): string;

    abstract public function createDataObject();

    /**
     * @param \Shopsys\Administration\Component\Security\AdminIdentifierInterface $dataObject
     * @return object
     */
    abstract public function doCreate(AdminIdentifierInterface $dataObject): object;

    /**
     * @param object $object
     */
    abstract public function doDelete(object $object);

    /**
     * @param \Shopsys\Administration\Component\Security\AdminIdentifierInterface $dataObject
     * @return object
     */
    abstract public function doEdit(AdminIdentifierInterface $dataObject): object;

    /**
     * @param object $entity
     * @return \Shopsys\Administration\Component\Security\AdminIdentifierInterface
     */
    abstract public function buildDataObjectForEdit(object $entity): AdminIdentifierInterface;

    /**
     * @param object $object
     * @param bool $dataObject
     * @return string
     */
    public function getRealClass(object $object, bool $dataObject = true): string
    {
        $entityName = $this->entityNameResolver->resolve($this->getSubjectClass());

        if ($dataObject === true) {
            return $entityName . 'Data';
        }

        return $entityName;
    }

    /**
     * @param object $object
     */
    public function create(object $object): void
    {
        try {
            $createdObject = $this->doCreate($object);
            $object->id = $createdObject->getId();
        } catch (PDOException|Exception $exception) {
            throw new ModelManagerException(
                sprintf('Failed to create object: %s', ClassUtils::getClass($object)),
                (int)$exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * @param object $object
     */
    public function update(object $object): void
    {
        try {
            $this->doEdit($object);
        } catch (PDOException|Exception $exception) {
            throw new ModelManagerException(
                sprintf('Failed to update object: %s', ClassUtils::getClass($object)),
                (int)$exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * @param object $object
     */
    public function delete(object $object): void
    {
        try {
            $this->doDelete($object);
        } catch (PDOException|Exception $exception) {
            throw new ModelManagerException(
                sprintf('Failed to delete object: %s', ClassUtils::getClass($object)),
                (int)$exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * @param object $object
     */
    public function getLockVersion(object $object)
    {
        $metadata = $this->getMetadata(ClassUtils::getClass($object));

        if (!$metadata->isVersioned || !isset($metadata->reflFields[$metadata->versionField])) {
            return null;
        }

        return $metadata->reflFields[$metadata->versionField]->getValue($object);
    }

    /**
     * @param object $object
     * @param int|null $expectedVersion
     */
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
                $exception,
            );
        }
    }

    /**
     * @param string $class
     * @param int|string $id
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     * @return object|null
     */
    public function find(string $class, $id): ?object
    {
        $values = array_combine($this->getIdentifierFieldNames($class), explode(self::ID_SEPARATOR, (string)$id));

        return $this->getEntityManager($class)->getRepository($class)->find($values);
    }

    /**
     * @phpstan-param class-string<T> $class
     * @phpstan-return array<T>
     * @param string $class
     * @param array $criteria
     * @return array
     */
    public function findBy(string $class, array $criteria = []): array
    {
        return $this->getEntityManager($class)->getRepository($class)->findBy($criteria);
    }

    /**
     * @phpstan-param class-string<T> $class
     * @phpstan-return T|null
     * @param string $class
     * @param array $criteria
     * @return object|null
     */
    public function findOneBy(string $class, array $criteria = []): ?object
    {
        return $this->getEntityManager($class)->getRepository($class)->findOneBy($criteria);
    }

    /**
     * @param string|object $class
     * @phpstan-param class-string|object $class
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    protected function getEntityManager($class): EntityManagerInterface
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!isset($this->cache[$class])) {
            $em = $this->registry->getManagerForClass($class);

            if (!$em instanceof EntityManagerInterface) {
                throw new RuntimeException(sprintf('No entity manager defined for class %s', $class));
            }

            $this->cache[$class] = $em;
        }

        return $this->cache[$class];
    }

    /**
     * @param string $class
     * @param string $alias
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery(string $class, string $alias = 'o'): BaseProxyQueryInterface
    {
        $repository = $this->getEntityManager($class)->getRepository($class);
        /** @phpstan-var \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery<T> $proxyQuery */
        $proxyQuery = new ProxyQuery($repository->createQueryBuilder($alias));

        return $proxyQuery;
    }

    /**
     * @param object $query
     * @return bool
     */
    public function supportsQuery(object $query): bool
    {
        return $query instanceof ProxyQuery || $query instanceof AbstractQuery || $query instanceof QueryBuilder;
    }

    /**
     * @param object $query
     */
    public function executeQuery(object $query)
    {
        if ($query instanceof QueryBuilder) {
            return $query->getQuery()->execute();
        }

        if ($query instanceof AbstractQuery) {
            return $query->execute();
        }

        if ($query instanceof ProxyQuery) {
            /** @phpstan-var \Doctrine\ORM\Tools\Pagination\Paginator<T> $results */
            $results = $query->execute();

            return $results;
        }

        throw new InvalidArgumentException(sprintf(
            'Argument 1 passed to %s() must be an instance of %s, %s, or %s',
            __METHOD__,
            QueryBuilder::class,
            AbstractQuery::class,
            ProxyQuery::class,
        ));
    }

    /**
     * @param object $model
     * @return array
     */
    public function getIdentifierValues(object $model): array
    {
        $class = ClassUtils::getClass($model);
        $metadata = $this->getMetadata($class);
        $platform = $this->getEntityManager($class)->getConnection()->getDatabasePlatform();

        $identifiers = [];

        foreach ($metadata->getIdentifierValues($model) as $name => $value) {
            if (!is_object($value)) {
                $identifiers[] = $value;

                continue;
            }

            $fieldType = $metadata->getTypeOfField($name);

            if ($fieldType !== null && Type::hasType($fieldType)) {
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

    /**
     * @param string $class
     * @return array
     */
    public function getIdentifierFieldNames(string $class): array
    {
        return $this->getMetadata($class)->getIdentifierFieldNames();
    }

    /**
     * @param object $model
     * @return string|null
     */
    public function getNormalizedIdentifier(object $model): ?string
    {
        $values = [$model->getId()];

        return implode(self::ID_SEPARATOR, $values);
    }

    /**
     * The ORM implementation does nothing special but you still should use
     * this method when using the id in a URL to allow for future improvements.
     *
     * @param object $model
     * @return string|null
     */
    public function getUrlSafeIdentifier(object $model): ?string
    {
        return $this->getNormalizedIdentifier($model);
    }

    /**
     * @param string $class
     * @param \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface $query
     * @param array $idx
     */
    public function addIdentifiersToQuery(string $class, BaseProxyQueryInterface $query, array $idx): void
    {
        if ($idx === []) {
            throw new InvalidArgumentException(sprintf(
                'Array passed as argument 3 to "%s()" must not be empty.',
                __METHOD__,
            ));
        }

        $fieldNames = $this->getIdentifierFieldNames($class);
        $qb = $query->getQueryBuilder();

        $prefix = uniqid('', true);
        $sqls = [];

        foreach ($idx as $pos => $id) {
            $ids = explode(self::ID_SEPARATOR, (string)$id);

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

    /**
     * @param string $class
     * @param \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQueryInterface $query
     */
    public function batchDelete(string $class, BaseProxyQueryInterface $query): void
    {
        if ($query->getQueryBuilder()->getDQLPart('join') !== []) {
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

                if (0 !== (++$i % 20)) {
                    continue;
                }

                $entityManager->flush();
                $entityManager->clear();
            }

            $entityManager->flush();
            $entityManager->clear();
        } catch (PDOException|Exception $exception) {
            throw new ModelManagerException(
                sprintf('Failed to delete object: %s', $class),
                (int)$exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * @param string $class
     * @return array
     */
    public function getExportFields(string $class): array
    {
        return $this->getMetadata($class)->getFieldNames();
    }

    /**
     * @param object $object
     * @param array $array
     */
    public function reverseTransform(object $object, array $array = []): void
    {
        $metadata = $this->getMetadata(get_class($object));

        foreach ($array as $name => $value) {
            $property = $this->getFieldName($metadata, $name);
            $this->propertyAccessor->setValue($object, $property, $value);
        }
    }

    /**
     * @phpstan-template TObject of object
     * @phpstan-param class-string<TObject> $class
     * @phpstan-return \Doctrine\ORM\Mapping\ClassMetadata<TObject>
     * @param string $class
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected function getMetadata(string $class): ClassMetadata
    {
        return $this->getEntityManager($class)->getClassMetadata($class);
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata<object> $metadata
     * @param string $name
     * @return string
     */
    protected function getFieldName(ClassMetadata $metadata, string $name): string
    {
        if (array_key_exists($name, $metadata->fieldMappings)) {
            return $metadata->fieldMappings[$name]['fieldName'];
        }

        if (array_key_exists($name, $metadata->associationMappings)) {
            return $metadata->associationMappings[$name]['fieldName'];
        }

        return $name;
    }

    /**
     * @param object $value
     * @param \Doctrine\DBAL\Types\Type $type
     * @param string $fieldType
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return string
     */
    protected function getValueFromType(
        object $value,
        Type $type,
        string $fieldType,
        AbstractPlatform $platform,
    ): string {
        if ($platform->hasDoctrineTypeMappingFor($fieldType) &&
            $platform->getDoctrineTypeMapping($fieldType) === 'binary'
        ) {
            return (string)$type->convertToPHPValue($value, $platform);
        }

        // some libraries may have `toString()` implementation
        if (is_callable([$value, 'toString'])) {
            return $value->toString();
        }

        // final fallback to magic `__toString()` which may throw an exception in 7.4
        if (method_exists($value, '__toString')) {
            return $value->__toString();
        }

        return (string)$type->convertToDatabaseValue($value, $platform);
    }
}
