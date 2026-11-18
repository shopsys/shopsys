<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\DBAL\Types\DateTimeUtcImmutableType;
use Doctrine\DBAL\Types\DateTimeUtcType;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\SmallFloatType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\InverseSideMapping;
use LogicException;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\IntegerRange;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;

/**
 * Reads a path by these rules:
 *
 * - a field of the entity shadows a translated field of the same name;
 * - the explicit `translations` segment reaches the translated field — `translations.name`;
 * - the identifier of an owning to-one association is read without a join — `currency.id`.
 */
final class PathWalker implements PathWalkerInterface
{
    /**
     * The association holding the translations of a translatable entity. It is a to-many association, but
     * joined with the current locale it leads to a single row, so it behaves as a to-one one.
     */
    public const string TRANSLATIONS_ASSOCIATION = PathDescription::TRANSLATIONS_SEGMENT;

    private const string MONEY_TYPE = 'money';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function walk(string $entityClass, string $path): WalkedPath
    {
        $parts = explode('.', $path);
        $lastIndex = count($parts) - 1;
        $classMetadata = $this->entityManager->getClassMetadata($entityClass);
        $steps = [];

        // a field of an embeddable spans several segments, so the index moves to the last one of the field it reads
        for ($index = 0; $index <= $lastIndex; $index++) {
            [$field, $index] = $this->readField($classMetadata, $parts, $index, $path);
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
                $steps[] = new PathStep(PathStepKind::FIELD, $field, $partPath, $classMetadata->getTypeOfField($field));

                return $this->finish($path, $steps);
            }

            if ($isLast && $classMetadata->hasAssociation($field) === false) {
                $translationClassMetadata = $this->getTranslationClassMetadata($classMetadata);

                if ($translationClassMetadata?->hasField($field) === true) {
                    $steps[] = new PathStep(PathStepKind::TRANSLATED_FIELD, $field, $partPath, $translationClassMetadata->getTypeOfField($field));

                    return $this->finish($path, $steps);
                }
            }

            if ($classMetadata->hasAssociation($field) === false) {
                throw new PathNotFoundException($path, $field, $classMetadata->getName());
            }

            $associationMapping = $classMetadata->getAssociationMapping($field);
            $isToMany = $associationMapping->isToMany();
            $targetClassMetadata = $this->getTargetClassMetadata($classMetadata, $field);
            $isOwningToOne = !$isToMany && $associationMapping->isOwningSide();

            if ($isOwningToOne) {
                $this->assertIdentifierIsNotComposite($path, $field, $classMetadata, $targetClassMetadata);
            }

            // only the owning side holds the column of the identifier, the inverse side of a one-to-one is joined
            if ($isOwningToOne && $index === $lastIndex - 1 && $this->isIdentifierField($targetClassMetadata, $parts[$lastIndex])) {
                $steps[] = new PathStep(PathStepKind::IDENTITY, $field, $partPath, $targetClassMetadata->getTypeOfField($parts[$lastIndex]));

                return $this->finish($path, $steps);
            }

            $steps[] = new PathStep(
                PathStepKind::ASSOCIATION,
                $field,
                $partPath,
                null,
                $targetClassMetadata->getName(),
                $associationMapping instanceof InverseSideMapping ? $associationMapping->mappedBy : null,
                $isToMany,
            );
            $classMetadata = $targetClassMetadata;

            if ($isLast) {
                return $this->finish($path, $steps);
            }
        }

        throw new PathNotFoundException($path, $path, $entityClass);
    }

    /**
     * An owning to-one association to an entity identified by several fields is refused:
     *
     * - DQL cannot compare it with the joined entity as a whole;
     * - `IDENTITY()` reads only the first column of the identifier.
     */
    private function assertIdentifierIsNotComposite(
        string $path,
        string $field,
        ClassMetadata $classMetadata,
        ClassMetadata $targetClassMetadata,
    ): void {
        if ($targetClassMetadata->isIdentifierComposite === false) {
            return;
        }

        throw new LogicException(sprintf(
            'Path "%s" cannot be resolved, association "%s" of entity "%s" leads to entity "%s" identified by several fields. Join the entity in configureQuery() and display its value by a virtual field instead.',
            $path,
            $field,
            $classMetadata->getName(),
            $targetClassMetadata->getName(),
        ));
    }

    /**
     * @param non-empty-list<\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathStep> $steps
     */
    private function finish(string $path, array $steps): WalkedPath
    {
        $lastStep = $steps[array_key_last($steps)];

        return new WalkedPath($steps, new PathDescription(
            $path,
            array_any($steps, static fn (PathStep $step): bool => $step->isToMany) ? PathCardinalityEnum::TO_MANY : PathCardinalityEnum::TO_ONE,
            $lastStep->kind === PathStepKind::ASSOCIATION ? PathValueTypeEnum::ASSOCIATION : $this->toValueType($lastStep->fieldType),
            $lastStep->targetEntityClass,
            $this->getIntegerRange($lastStep->fieldType),
        ));
    }

    /**
     * A value outside the range of the column fails the query instead of matching nothing.
     * A big integer column holds every PHP integer, so it has no range.
     */
    private function getIntegerRange(?string $fieldType): ?IntegerRange
    {
        return match ($fieldType) {
            Types::SMALLINT => new IntegerRange(-32768, 32767),
            Types::INTEGER => new IntegerRange(-2147483648, 2147483647),
            default => null,
        };
    }

    private function toValueType(?string $doctrineType): PathValueTypeEnum
    {
        return match ($doctrineType) {
            Types::STRING, Types::TEXT, Types::ASCII_STRING => PathValueTypeEnum::STRING,
            Types::GUID => PathValueTypeEnum::GUID,
            Types::INTEGER, Types::SMALLINT, Types::BIGINT => PathValueTypeEnum::INTEGER,
            Types::DECIMAL, Types::FLOAT => PathValueTypeEnum::DECIMAL,
            Types::BOOLEAN => PathValueTypeEnum::BOOLEAN,
            Types::DATE_MUTABLE, Types::DATE_IMMUTABLE => PathValueTypeEnum::DATE,
            Types::DATETIME_MUTABLE, Types::DATETIME_IMMUTABLE, Types::DATETIMETZ_MUTABLE, Types::DATETIMETZ_IMMUTABLE => PathValueTypeEnum::DATETIME,
            self::MONEY_TYPE => PathValueTypeEnum::MONEY,
            default => $this->toValueTypeByClass($doctrineType),
        };
    }

    /**
     * An own type of a project extends a type of Doctrine, so it is read by the type it extends — an own type storing
     * text is text as well — and so is a type only a newer Doctrine knows by its name (a date and time stored in UTC).
     * A GUID extends the string type too, but a database stores it in a column of its own type no text function takes.
     */
    private function toValueTypeByClass(?string $doctrineType): PathValueTypeEnum
    {
        if ($doctrineType === null || Type::hasType($doctrineType) === false) {
            return PathValueTypeEnum::UNKNOWN;
        }

        $type = Type::getType($doctrineType);

        return match (true) {
            $type instanceof GuidType => PathValueTypeEnum::GUID,
            $type instanceof StringType || $type instanceof TextType => PathValueTypeEnum::STRING,
            $type instanceof DateTimeUtcType || $type instanceof DateTimeUtcImmutableType => PathValueTypeEnum::DATETIME,
            $type instanceof SmallFloatType => PathValueTypeEnum::DECIMAL,
            default => PathValueTypeEnum::UNKNOWN,
        };
    }

    /**
     * A field of an embeddable is mapped on the entity holding it, named by the whole way through the embeddables
     * (`address.country`), so the segments leading through an embeddable read as a single field. The embeddable
     * alone holds no value to compare.
     *
     * @param list<string> $parts
     * @return array{string, int} The field and the index of its last segment
     */
    private function readField(ClassMetadata $classMetadata, array $parts, int $index, string $path): array
    {
        $field = $parts[$index];

        while (isset($classMetadata->embeddedClasses[$field])) {
            if ($index === count($parts) - 1) {
                throw new PathNotFoundException($path, $field, $classMetadata->getName());
            }

            $field .= '.' . $parts[++$index];
        }

        return [$field, $index];
    }

    /**
     * An identifier that is an association itself (a derived identity) has no column of its own to be read by IDENTITY().
     */
    private function isIdentifierField(ClassMetadata $classMetadata, string $field): bool
    {
        return $classMetadata->hasField($field) && in_array($field, $classMetadata->getIdentifier(), true);
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
