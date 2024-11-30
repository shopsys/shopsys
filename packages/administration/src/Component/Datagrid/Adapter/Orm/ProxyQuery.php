<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

final class ProxyQuery
{
    public const DEFAULT_ALIAS = 'o';

    private QueryBuilder $queryBuilder;

    private string $rootAlias;

    /**
     * @var array<string, string>
     */
    private array $joins = [];

    /**
     * @param string $entityClass
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param string $locale
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $locale,
    ) {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $entityManager->getRepository($entityClass);

        $this->queryBuilder = $repository->createQueryBuilder(self::DEFAULT_ALIAS);
        $this->rootAlias = current($this->queryBuilder->getRootAliases());
        $this->queryBuilder->resetDQLPart('select');
    }

    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

    /**
     * @param string $select
     * @param string|null $alias
     */
    public function addSelect(string $select, ?string $alias = null): void
    {
        if ($alias !== null) {
            $this->queryBuilder->addSelect($select . ' AS ' . $alias);

            return;
        }

        $rootAlias = $this->rootAlias;

        if (str_contains($select, '.') === false) {
            $classMetadata = $this->entityManager->getClassMetadata($this->entityClass);

            if (!in_array($select, $classMetadata->getFieldNames(), true)) {
                throw new InvalidArgumentException('Field "' . $select . '" not found in entity ' . $this->entityClass);
            }

            $this->queryBuilder->addSelect($rootAlias . '.' . $select . ' AS ' . $select);

            return;
        }

        $this->processDotNotation($select);
    }

    /**
     * @param string $select
     */
    private function processDotNotation(string $select): void
    {
        $alias = $this->rootAlias;
        $parts = explode('.', $select);

        $currentClassMetadata = $this->entityManager->getClassMetadata($this->entityClass);

        // dot notation is processed from left to right and each part is joined with left join
        // the last part is added to select

        foreach ($parts as $index => $part) {
            $path = implode('.', array_slice($parts, 0, $index + 1));
            $joinAlias = $part;


            if ($index >= count($parts) - 1) {
                if ($currentClassMetadata->hasField($part)) {
                    $this->queryBuilder->addSelect("{$alias}.{$part}" . ' AS ' . $this->getAlias($path));

                    continue;
                }

                try {
                    // If entity does not have field, check if it has association to translations and try to select from translations
                    $this->joinAssociation($currentClassMetadata, $path . '_tr', 'translations', $alias, $alias . '_tr');
                    $this->queryBuilder->addSelect("{$alias}_tr.{$part}" . ' AS ' . $this->getAlias($path));

                    continue;
                } catch (InvalidArgumentException $exception) {
                    // We don't need to throw exeption here
                }

                throw new InvalidArgumentException('Field "' . $part . '" not found in entity ' . $currentClassMetadata->getName());
            }

            $this->joinAssociation($currentClassMetadata, $path, $part, $alias, $joinAlias);

            $currentClassMetadata = $this->entityManager->getClassMetadata(
                $currentClassMetadata->getAssociationTargetClass($part),
            );

            $alias = $joinAlias;
        }
    }

    /**
     * @param mixed $part
     * @return string
     */
    private function getAlias($part): string
    {
        return str_replace('.', '__', $part);
    }

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata $classMetadata
     * @param string $pathToJoin
     * @param string $fieldName
     * @param string $currentAlias
     * @param string $joinAlias
     */
    private function joinAssociation(
        ClassMetadata $classMetadata,
        string $pathToJoin,
        string $fieldName,
        string $currentAlias,
        string $joinAlias,
    ): void {
        if (array_key_exists($pathToJoin, $this->joins)) {
            return;
        }

        if ($classMetadata->hasAssociation($fieldName) === false) {
            throw new InvalidArgumentException('Association "' . $fieldName . '" not found in entity ' . $classMetadata->getName());
        }

        if ($classMetadata->getAssociationMapping($fieldName)['type'] !== ClassMetadata::MANY_TO_ONE && $fieldName !== 'translations') {
            throw new InvalidArgumentException('Association "' . $fieldName . '" is not MANY_TO_ONE in entity ' . $classMetadata->getName());
        }

        $this->joins[$pathToJoin] = $joinAlias;

        if ($fieldName !== 'translations') {
            $this->queryBuilder->leftJoin("{$currentAlias}.{$fieldName}", $joinAlias);

            return;
        }

        $this->queryBuilder->leftJoin("{$currentAlias}.translations", $joinAlias, Join::WITH, "{$joinAlias}.locale = :{$joinAlias}_locale");
        $this->queryBuilder->setParameter("{$joinAlias}_locale", $this->locale);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
