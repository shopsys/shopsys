<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;

/**
 * Walks a dot notation path through the entity mapping without touching any query.
 *
 * It is the single place knowing how a path is read — the shadowing of a translated field by an own one, the
 * explicit `translations` segment, the identifier of an association available without a join. Describing a
 * path and building a query for it both consume the same walk, so they can never disagree.
 */
final class PathWalker
{
    /**
     * The association holding the translations of a translatable entity. It is a to-many association, but
     * joined with the current locale it leads to a single row, so it behaves as a to-one one.
     */
    public const string TRANSLATIONS_ASSOCIATION = 'translations';

    /**
     * The Doctrine type of the money column, registered by the application.
     */
    private const string MONEY_TYPE = 'money';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param class-string $entityClass
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException
     */
    public function walk(string $entityClass, string $path): WalkedPath
    {
        $parts = explode('.', $path);
        $lastIndex = count($parts) - 1;
        $classMetadata = $this->entityManager->getClassMetadata($entityClass);
        $steps = [];
        $crossesToMany = false;

        foreach ($parts as $index => $field) {
            $partPath = implode('.', array_slice($parts, 0, $index + 1));
            $isLast = $index === $lastIndex;

            // the translations of the entity are addressable explicitly, which is the only way to reach
            // a translated field shadowed by a field of the entity itself
            if ($field === self::TRANSLATIONS_ASSOCIATION && !$isLast && $classMetadata->hasAssociation($field)) {
                $steps[] = new PathStep(PathStepKind::TRANSLATIONS, $field, $partPath, null);
                $classMetadata = $this->getTargetClassMetadata($classMetadata, $field);

                continue;
            }

            if ($isLast && $classMetadata->hasField($field)) {
                $fieldType = $classMetadata->getTypeOfField($field);
                $steps[] = new PathStep(PathStepKind::FIELD, $field, $partPath, $fieldType);

                return $this->finish($path, $steps, $crossesToMany, $this->toValueType($fieldType));
            }

            if ($isLast && $classMetadata->hasAssociation($field) === false) {
                $translationClassMetadata = $this->getTranslationClassMetadata($classMetadata);

                if ($translationClassMetadata?->hasField($field) === true) {
                    $fieldType = $translationClassMetadata->getTypeOfField($field);
                    $steps[] = new PathStep(PathStepKind::TRANSLATED_FIELD, $field, $partPath, $fieldType);

                    return $this->finish($path, $steps, $crossesToMany, $this->toValueType($fieldType));
                }
            }

            if ($classMetadata->hasAssociation($field) === false) {
                throw new PathNotFoundException($path, $field, $classMetadata->getName());
            }

            $isToMany = $classMetadata->getAssociationMapping($field)->isToMany();
            $crossesToMany = $crossesToMany || $isToMany;
            $targetClassMetadata = $this->getTargetClassMetadata($classMetadata, $field);

            // the identifier of a to-one association is available without joining it
            if (!$isToMany && $index === $lastIndex - 1 && in_array($parts[$lastIndex], $targetClassMetadata->getIdentifier(), true)) {
                $identifierType = $targetClassMetadata->getTypeOfField($parts[$lastIndex]);
                $steps[] = new PathStep(PathStepKind::IDENTITY, $field, $partPath, $identifierType);

                return $this->finish($path, $steps, $crossesToMany, $this->toValueType($identifierType));
            }

            $steps[] = new PathStep(PathStepKind::ASSOCIATION, $field, $partPath, null);
            $classMetadata = $targetClassMetadata;

            if ($isLast) {
                return $this->finish($path, $steps, $crossesToMany, PathValueTypeEnum::ASSOCIATION, $classMetadata->getName());
            }
        }

        throw new PathNotFoundException($path, $path, $entityClass);
    }

    /**
     * @param list<\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathStep> $steps
     * @param class-string|null $targetEntityClass
     */
    private function finish(
        string $path,
        array $steps,
        bool $crossesToMany,
        PathValueTypeEnum $valueType,
        ?string $targetEntityClass = null,
    ): WalkedPath {
        return new WalkedPath($steps, new PathDescription(
            $path,
            $crossesToMany ? PathCardinalityEnum::TO_MANY : PathCardinalityEnum::TO_ONE,
            $valueType,
            $targetEntityClass,
        ));
    }

    private function toValueType(?string $doctrineType): PathValueTypeEnum
    {
        return match ($doctrineType) {
            Types::STRING, Types::TEXT, Types::ASCII_STRING => PathValueTypeEnum::STRING,
            Types::INTEGER, Types::SMALLINT, Types::BIGINT => PathValueTypeEnum::INTEGER,
            Types::DECIMAL, Types::FLOAT => PathValueTypeEnum::DECIMAL,
            Types::BOOLEAN => PathValueTypeEnum::BOOLEAN,
            Types::DATE_MUTABLE, Types::DATE_IMMUTABLE => PathValueTypeEnum::DATE,
            Types::DATETIME_MUTABLE, Types::DATETIME_IMMUTABLE, Types::DATETIMETZ_MUTABLE, Types::DATETIMETZ_IMMUTABLE => PathValueTypeEnum::DATETIME,
            self::MONEY_TYPE => PathValueTypeEnum::MONEY,
            default => PathValueTypeEnum::UNKNOWN,
        };
    }

    private function getTargetClassMetadata(ClassMetadata $classMetadata, string $association): ClassMetadata
    {
        return $this->entityManager->getClassMetadata($classMetadata->getAssociationTargetClass($association));
    }

    private function getTranslationClassMetadata(ClassMetadata $classMetadata): ?ClassMetadata
    {
        if ($classMetadata->hasAssociation(self::TRANSLATIONS_ASSOCIATION) === false) {
            return null;
        }

        return $this->getTargetClassMetadata($classMetadata, self::TRANSLATIONS_ASSOCIATION);
    }
}
