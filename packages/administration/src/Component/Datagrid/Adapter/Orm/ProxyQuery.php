<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use RuntimeException;

final class ProxyQuery
{
    public const DEFAULT_ALIAS = 'o';

    private QueryBuilder $queryBuilder;

    private string $rootAlias;

    /**
     * @var array<string, string>
     */
    private array $selects = [];

    /**
     * @var array<string, string>
     */
    private array $joins = [];

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

    public function addSelect(string $select): void
    {
        if (array_key_exists($select, $this->selects)) {
            return;
        }

        $alias = $this->processDotNotation($select);
        $this->selects[$select] = $alias;
    }

    /**
     * @return string Returns alias of the select
     */
    private function processDotNotation(string $string): string
    {
        $alias = $this->rootAlias;
        $parts = explode('.', $string);

        $currentClassMetadata = $this->entityManager->getClassMetadata($this->entityClass);

        // dot notation is processed from left to right and each part is joined
        foreach ($parts as $index => $field) {
            $path = implode('.', array_slice($parts, 0, $index + 1));
            $joinAlias = $field . '_join';

            if ($this->isLastPart($parts, $index)) {
                switch ($this->getPartType($currentClassMetadata, $field)) {
                    case PartType::FIELD:
                        $this->queryBuilder->addSelect("{$alias}.{$field} AS {$this->getAlias($path)}");

                        break;
                    case PartType::ASSOCIATION:
                        $this->joinAssociation($currentClassMetadata, $path, $field, $alias, $joinAlias);
                        $this->queryBuilder->addSelect("{$joinAlias} AS {$this->getAlias($path)}");

                        break;
                    case PartType::TRANSLATION:
                        $this->joinAssociation($currentClassMetadata, $path . '_tr', 'translations', $alias, $alias . '_tr');
                        $this->queryBuilder->addSelect("{$alias}_tr.{$field} AS {$this->getAlias($path)}");

                        break;
                }

                return $this->getAlias($path);
            }

            // If next part is last and is primary key, select it as identity without join
            if ($this->isNextPartLastAndIdentity($parts, $index, $currentClassMetadata)) {
                $path = implode('.', $parts);

                $this->queryBuilder->addSelect("IDENTITY({$alias}.{$field}) AS {$this->getAlias($path)}");

                return $this->getAlias($path);
            }

            $this->joinAssociation($currentClassMetadata, $path, $field, $alias, $joinAlias);

            $currentClassMetadata = $this->getClassMetadataForTarget($field, $currentClassMetadata);
            $alias = $joinAlias;
        }

        throw new RuntimeException(
            "Error processing dot notation for string '{$string}' in entity '{$this->entityClass}'. Ensure the field or association exists in the mapping.",
        );
    }

    private function getAlias(mixed $part): string
    {
        return strtr($part, ['.' => '__']);
    }

    private function getPartType(ClassMetadata $classMetadata, string $field): PartType
    {
        if ($classMetadata->hasField($field)) {
            return PartType::FIELD;
        }

        if ($classMetadata->hasAssociation($field)) {
            return PartType::ASSOCIATION;
        }

        if ($classMetadata->hasAssociation('translations')) {
            $translationClassMetadata = $this->getClassMetadataForTarget('translations', $classMetadata);

            if ($translationClassMetadata->hasField($field)) {
                return PartType::TRANSLATION;
            }
        }

        throw new InvalidArgumentException('Field "' . $field . '" not found in entity ' . $classMetadata->getName());
    }

    /**
     * @param string[] $parts
     */
    private function isLastPart(array $parts, int $currentIndex): bool
    {
        return $currentIndex >= count($parts) - 1;
    }

    /**
     * @param string[] $parts
     */
    private function isNextPartLastAndIdentity(array $parts, int $currentIndex, ClassMetadata $classMetadata): bool
    {
        // check if next iteration will be last part of dot notation
        if ($this->isLastPart($parts, $currentIndex + 1) === false) {
            return false;
        }

        // check if next part is primary key
        $nextPart = $parts[$currentIndex + 1];
        $associationClassMetadata = $this->getClassMetadataForTarget($parts[$currentIndex], $classMetadata);

        return in_array($nextPart, $associationClassMetadata->getIdentifier(), true) !== false;
    }

    private function getClassMetadataForTarget(string $part, ClassMetadata $currentClassMetadata): ClassMetadata
    {
        return $this->entityManager->getClassMetadata($currentClassMetadata->getAssociationTargetClass($part));
    }

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

        if ($fieldName === 'translations') {
            $this->queryBuilder->leftJoin("{$currentAlias}.{$fieldName}", $joinAlias, Join::WITH, "{$joinAlias}.locale = :{$joinAlias}_locale");
            $this->queryBuilder->setParameter("{$joinAlias}_locale", $this->locale);

            return;
        }

        $associationMapping = $classMetadata->getAssociationMapping($fieldName);
        $this->queryBuilder->leftJoin($associationMapping['targetEntity'], $joinAlias, Join::WITH, "{$currentAlias}.{$fieldName} = {$joinAlias}.id");
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
