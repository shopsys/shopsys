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
     * Resolves a field path in dot notation (e.g. "catnum", "items.catnum", "translations.name")
     * to a DQL expression usable in query conditions, adding the necessary joins.
     */
    public function getFieldExpression(string $fieldPath): string
    {
        [$expression, $partType] = $this->resolveFieldPath($fieldPath);

        if ($partType === PartType::ASSOCIATION) {
            $parts = explode('.', $fieldPath);

            throw new InvalidArgumentException(
                "Field path '{$fieldPath}' ends with association '" . end($parts) . "', which cannot be used in a condition directly. Use a field of the association instead.",
            );
        }

        return $expression;
    }

    /**
     * Resolves a field path whose last part is a to-one association (e.g. "brand", "order.status")
     * to a DQL expression comparable with entities (e.g. "o.brand"), adding joins only for the intermediate parts.
     */
    public function getAssociationTargetExpression(string $fieldPath): string
    {
        [$expression, $partType] = $this->resolveFieldPath($fieldPath, false);

        if ($partType !== PartType::ASSOCIATION) {
            throw new InvalidArgumentException(
                "Field path '{$fieldPath}' does not end with an association. Use getFieldExpression() for fields.",
            );
        }

        return $expression;
    }

    /**
     * @return string Returns alias of the select
     */
    public function processDotNotation(string $string): string
    {
        [$expression] = $this->resolveFieldPath($string);
        $selectAlias = $this->getAlias($string);

        $this->queryBuilder->addSelect("{$expression} AS {$selectAlias}");

        return $selectAlias;
    }

    /**
     * @param bool $joinLastAssociation Whether a path ending with an association joins it and resolves to the join alias,
     *                                  or resolves to the "alias.field" expression without the join
     * @return array{string, \Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PartType} The DQL expression and the type of the last resolved part
     */
    private function resolveFieldPath(string $fieldPath, bool $joinLastAssociation = true): array
    {
        $alias = $this->rootAlias;
        $parts = explode('.', $fieldPath);

        $currentClassMetadata = $this->entityManager->getClassMetadata($this->entityClass);

        // dot notation is processed from left to right and each part is joined
        foreach ($parts as $index => $field) {
            $path = implode('.', array_slice($parts, 0, $index + 1));
            $joinAlias = $field . '_join';

            if ($this->isLastPart($parts, $index)) {
                switch ($this->getPartType($currentClassMetadata, $field)) {
                    case PartType::FIELD:
                        return ["{$alias}.{$field}", PartType::FIELD];
                    case PartType::ASSOCIATION:
                        if ($joinLastAssociation === false) {
                            return ["{$alias}.{$field}", PartType::ASSOCIATION];
                        }

                        $this->joinAssociation($currentClassMetadata, $path, $field, $alias, $joinAlias);

                        return [$joinAlias, PartType::ASSOCIATION];
                    case PartType::TRANSLATION:
                        $this->joinAssociation($currentClassMetadata, $alias . '_tr', 'translations', $alias, $alias . '_tr');

                        return ["{$alias}_tr.{$field}", PartType::TRANSLATION];
                }
            }

            // If next part is last and is primary key, resolve it as identity without join
            if ($this->isNextPartLastAndIdentity($parts, $index, $currentClassMetadata)) {
                return ["IDENTITY({$alias}.{$field})", PartType::FIELD];
            }

            $this->joinAssociation($currentClassMetadata, $path, $field, $alias, $joinAlias);

            $currentClassMetadata = $this->getClassMetadataForTarget($field, $currentClassMetadata);
            $alias = $joinAlias;
        }

        throw new RuntimeException(
            "Error processing dot notation for string '{$fieldPath}' in entity '{$this->entityClass}'. Ensure the field or association exists in the mapping.",
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

        if ($classMetadata->getAssociationMapping($fieldName)->type() !== ClassMetadata::MANY_TO_ONE && $fieldName !== 'translations') {
            throw new InvalidArgumentException('Association "' . $fieldName . '" is not MANY_TO_ONE in entity ' . $classMetadata->getName());
        }

        $this->joins[$pathToJoin] = $joinAlias;

        if ($fieldName === 'translations') {
            $this->queryBuilder->leftJoin("{$currentAlias}.{$fieldName}", $joinAlias, Join::WITH, "{$joinAlias}.locale = :{$joinAlias}_locale");
            $this->queryBuilder->setParameter("{$joinAlias}_locale", $this->locale);

            return;
        }

        $associationMapping = $classMetadata->getAssociationMapping($fieldName);
        $this->queryBuilder->leftJoin($associationMapping->targetEntity, $joinAlias, Join::WITH, "{$currentAlias}.{$fieldName} = {$joinAlias}.id");
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
