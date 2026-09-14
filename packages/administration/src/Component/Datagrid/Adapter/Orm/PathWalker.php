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
use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\InverseSideMapping;
use Override;
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
 *
 * It is a stateless service — the ORM adapter describes a path and its query resolves the path by the very same
 * instance.
 */
final class PathWalker implements PathWalkerInterface
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

    /**
     * The integers a column of the type holds — a greater one fails the query instead of matching nothing. A big
     * integer holds every integer of PHP.
     *
     * @var array<string, array{int, int}>
     */
    private const array INTEGER_RANGES = [
        Types::SMALLINT => [-32768, 32767],
        Types::INTEGER => [-2147483648, 2147483647],
    ];

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
        $crossesToMany = false;

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
                $fieldType = $classMetadata->getTypeOfField($field);
                $steps[] = new PathStep(PathStepKind::FIELD, $field, $partPath, $fieldType);

                return $this->finish($path, $steps, $crossesToMany, $this->toValueType($fieldType), fieldType: $fieldType);
            }

            if ($isLast && $classMetadata->hasAssociation($field) === false) {
                $translationClassMetadata = $this->getTranslationClassMetadata($classMetadata);

                if ($translationClassMetadata?->hasField($field) === true) {
                    $fieldType = $translationClassMetadata->getTypeOfField($field);
                    $steps[] = new PathStep(PathStepKind::TRANSLATED_FIELD, $field, $partPath, $fieldType);

                    return $this->finish($path, $steps, $crossesToMany, $this->toValueType($fieldType), fieldType: $fieldType);
                }
            }

            if ($classMetadata->hasAssociation($field) === false) {
                throw new PathNotFoundException($path, $field, $classMetadata->getName());
            }

            $associationMapping = $classMetadata->getAssociationMapping($field);
            $isToMany = $associationMapping->isToMany();
            $crossesToMany = $crossesToMany || $isToMany;
            $targetClassMetadata = $this->getTargetClassMetadata($classMetadata, $field);

            // the identifier of a to-one association is available without joining it, unless the association is owned
            // by the entity it leads to (the inverse side of a one-to-one), which holds the column of the identifier;
            // an identifier of several fields is read by the field, IDENTITY() reads the first column otherwise
            if (!$isToMany && $associationMapping->isOwningSide() && $index === $lastIndex - 1 && $this->isIdentifierField($targetClassMetadata, $parts[$lastIndex])) {
                $identifierType = $targetClassMetadata->getTypeOfField($parts[$lastIndex]);
                $identifierField = $targetClassMetadata->isIdentifierComposite ? $parts[$lastIndex] : null;
                $steps[] = new PathStep(PathStepKind::IDENTITY, $field, $partPath, $identifierType, identifierField: $identifierField);

                return $this->finish($path, $steps, $crossesToMany, $this->toValueType($identifierType), fieldType: $identifierType);
            }

            $steps[] = new PathStep(
                PathStepKind::ASSOCIATION,
                $field,
                $partPath,
                null,
                $targetClassMetadata->getName(),
                $associationMapping instanceof InverseSideMapping ? $associationMapping->mappedBy : null,
                $this->getComparedIdentifierFields($associationMapping, $targetClassMetadata),
                isToMany: $isToMany,
            );
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
     * @param string|null $fieldType Doctrine type of the terminal field, which bounds an integer
     */
    private function finish(
        string $path,
        array $steps,
        bool $crossesToMany,
        PathValueTypeEnum $valueType,
        ?string $targetEntityClass = null,
        ?string $fieldType = null,
    ): WalkedPath {
        $integerRange = self::INTEGER_RANGES[$fieldType ?? ''] ?? null;

        return new WalkedPath($steps, new PathDescription(
            $path,
            $crossesToMany ? PathCardinalityEnum::TO_MANY : PathCardinalityEnum::TO_ONE,
            $valueType,
            $targetEntityClass,
            $integerRange[0] ?? null,
            $integerRange[1] ?? null,
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
            ($type instanceof StringType || $type instanceof TextType) && $type instanceof GuidType === false => PathValueTypeEnum::STRING,
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
     * The identifier fields an owning association to an entity identified by several fields is compared by, each
     * read by IDENTITY() — an identifier that is an association itself has no column to be read so.
     *
     * @return string[]
     */
    private function getComparedIdentifierFields(
        AssociationMapping $associationMapping,
        ClassMetadata $targetClassMetadata,
    ): array {
        if ($associationMapping->isOwningSide() === false || $targetClassMetadata->isIdentifierComposite === false) {
            return [];
        }

        $identifierFields = $targetClassMetadata->getIdentifierFieldNames();

        return array_filter($identifierFields, $targetClassMetadata->hasField(...)) === $identifierFields ? $identifierFields : [];
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
