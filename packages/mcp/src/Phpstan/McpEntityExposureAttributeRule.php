<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Phpstan;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use Prezent\Doctrine\Translatable\Attribute\Translatable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

class McpEntityExposureAttributeRule implements Rule
{
    public const string IDENTIFIER_ENTITY_EXPOSURE = 'shopsys.mcpEntityExposure';
    public const string IDENTIFIER_COLUMN_EXPOSURE = 'shopsys.mcpColumnExposure';
    public const string IDENTIFIER_COLUMN_FIELD_NAME = 'shopsys.mcpColumnFieldName';
    public const string IDENTIFIER_COLUMN_UNKNOWN_FIELD = 'shopsys.mcpColumnUnknownField';
    public const string IDENTIFIER_COLUMN_DUPLICATE_FIELD_NAME = 'shopsys.mcpColumnDuplicateFieldName';
    public const string IDENTIFIER_COLUMN_PROPERTY_FIELD_NAME = 'shopsys.mcpColumnPropertyFieldName';

    protected const string APP_NAMESPACE = 'App\\';
    protected const string SHOPSYS_NAMESPACE = 'Shopsys\\';
    protected const string TABLE_ATTRIBUTE_CLASS = AsMcpTable::class;
    protected const string COLUMN_ATTRIBUTE_CLASS = AsMcpColumn::class;
    protected const string ORM_ENTITY_ATTRIBUTE_CLASS = Entity::class;
    protected const string ORM_COLUMN_ATTRIBUTE_CLASS = Column::class;
    protected const string ORM_EMBEDDED_ATTRIBUTE_CLASS = Embedded::class;
    protected const string ORM_ONE_TO_ONE_ATTRIBUTE_CLASS = OneToOne::class;
    protected const string ORM_MANY_TO_ONE_ATTRIBUTE_CLASS = ManyToOne::class;
    protected const string PREZENT_TRANSLATABLE_ATTRIBUTE_CLASS = Translatable::class;

    /**
     * @param array<string> $skipPathPatterns
     */
    public function __construct(
        protected array $skipPathPatterns = ['#/(tests|Tests)/#'],
    ) {
    }

    #[Override]
    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    #[Override]
    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        if ($classReflection === null || !$this->isCheckedNamespace($classReflection->getName())) {
            return [];
        }

        if ($this->isTestPath($scope->getFile())) {
            return [];
        }

        $nativeReflection = $classReflection->getNativeReflection();

        if (!$this->isOrmEntity($nativeReflection->getAttributes())) {
            return [];
        }

        $className = $classReflection->getName();
        $tableAttribute = $this->getAttributeByClassName(
            $nativeReflection->getAttributes(),
            static::TABLE_ATTRIBUTE_CLASS,
        );

        if ($tableAttribute === null) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Entity "%s" must declare #[AsMcpTable(exposed: bool)].',
                    $className,
                ))->identifier(static::IDENTIFIER_ENTITY_EXPOSURE)->build(),
            ];
        }

        if (!$tableAttribute->newInstance()->exposed) {
            return [];
        }

        $classLevelErrors = [];
        $classLevelColumnExposureByFieldNames = $this->collectClassLevelColumnExposures(
            $nativeReflection,
            $className,
            $classLevelErrors,
        );

        return array_merge(
            $classLevelErrors,
            $this->collectPropertyLevelErrors($nativeReflection, $className, $classLevelColumnExposureByFieldNames),
        );
    }

    /**
     * @param \PHPStan\Rules\IdentifierRuleError[] $errors
     * @return array<string, bool>
     */
    protected function collectClassLevelColumnExposures(
        ReflectionClass $nativeReflection,
        string $className,
        array &$errors,
    ): array {
        $classLevelColumnExposureByFieldNames = [];

        foreach ($nativeReflection->getAttributes(static::COLUMN_ATTRIBUTE_CLASS) as $attribute) {
            $asMcpColumn = $attribute->newInstance();

            if ($asMcpColumn->fieldName === null) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Class-level #[AsMcpColumn] on entity "%s" must define fieldName.',
                    $className,
                ))->identifier(static::IDENTIFIER_COLUMN_FIELD_NAME)->build();

                continue;
            }

            if (!$this->hasMappedField($nativeReflection, $asMcpColumn->fieldName)) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Class-level #[AsMcpColumn(fieldName: "%s")] on entity "%s" must reference an existing mapped property.',
                    $asMcpColumn->fieldName,
                    $className,
                ))->identifier(static::IDENTIFIER_COLUMN_UNKNOWN_FIELD)->build();
            }

            if (array_key_exists($asMcpColumn->fieldName, $classLevelColumnExposureByFieldNames)) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Class-level #[AsMcpColumn(fieldName: "%s")] on entity "%s" must not be declared more than once.',
                    $asMcpColumn->fieldName,
                    $className,
                ))->identifier(static::IDENTIFIER_COLUMN_DUPLICATE_FIELD_NAME)->build();

                continue;
            }

            $classLevelColumnExposureByFieldNames[$asMcpColumn->fieldName] = $asMcpColumn->exposed;
        }

        return $classLevelColumnExposureByFieldNames;
    }

    /**
     * @param array<string, bool> $classLevelColumnExposureByFieldNames
     * @return \PHPStan\Rules\IdentifierRuleError[]
     */
    protected function collectPropertyLevelErrors(
        ReflectionClass $nativeReflection,
        string $className,
        array $classLevelColumnExposureByFieldNames,
    ): array {
        $errors = [];

        foreach ($nativeReflection->getProperties() as $property) {
            $mcpColumnAttribute = $this->getAttributeByClassName($property->getAttributes(), static::COLUMN_ATTRIBUTE_CLASS);

            if ($mcpColumnAttribute !== null && $mcpColumnAttribute->newInstance()->fieldName !== null) {
                $errors[] = RuleErrorBuilder::message(sprintf(
                    'Property-level #[AsMcpColumn] on "%s::$%s" must not define fieldName.',
                    $className,
                    $property->getName(),
                ))->identifier(static::IDENTIFIER_COLUMN_PROPERTY_FIELD_NAME)->build();
            }

            if (!$this->requiresMcpColumnAttribute($property)) {
                continue;
            }

            if ($this->hasMcpColumnExposure($property, $classLevelColumnExposureByFieldNames)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Mapped property "%s::$%s" must declare #[AsMcpColumn(exposed: bool)] because the entity is exposed via MCP.',
                $className,
                $property->getName(),
            ))->identifier(static::IDENTIFIER_COLUMN_EXPOSURE)->build();
        }

        return $errors;
    }

    protected function isCheckedNamespace(string $className): bool
    {
        return str_starts_with($className, static::APP_NAMESPACE)
            || str_starts_with($className, static::SHOPSYS_NAMESPACE);
    }

    protected function isTestPath(string $filePath): bool
    {
        foreach ($this->skipPathPatterns as $skipPathPattern) {
            if (preg_match($skipPathPattern, $filePath) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<\ReflectionAttribute<object>> $attributes
     */
    protected function isOrmEntity(array $attributes): bool
    {
        return $this->getAttributeByClassName($attributes, static::ORM_ENTITY_ATTRIBUTE_CLASS) !== null;
    }

    protected function requiresMcpColumnAttribute(ReflectionProperty $property): bool
    {
        $attributes = $property->getAttributes();

        if ($this->getAttributeByClassName($attributes, static::ORM_COLUMN_ATTRIBUTE_CLASS) !== null) {
            return true;
        }

        if ($this->getAttributeByClassName($attributes, static::ORM_EMBEDDED_ATTRIBUTE_CLASS) !== null) {
            return true;
        }

        if ($this->getAttributeByClassName($attributes, static::ORM_MANY_TO_ONE_ATTRIBUTE_CLASS) !== null) {
            return true;
        }

        if ($this->getAttributeByClassName($attributes, static::PREZENT_TRANSLATABLE_ATTRIBUTE_CLASS) !== null) {
            return true;
        }

        $oneToOneAttribute = $this->getAttributeByClassName($attributes, static::ORM_ONE_TO_ONE_ATTRIBUTE_CLASS);

        if ($oneToOneAttribute === null) {
            return false;
        }

        $oneToOneMapping = $oneToOneAttribute->newInstance();

        return !property_exists($oneToOneMapping, 'mappedBy') || $oneToOneMapping->mappedBy === null;
    }

    /**
     * @param array<string, bool> $classLevelColumnExposureByFieldNames
     */
    protected function hasMcpColumnExposure(
        ReflectionProperty $property,
        array $classLevelColumnExposureByFieldNames,
    ): bool {
        if ($this->getAttributeByClassName($property->getAttributes(), static::COLUMN_ATTRIBUTE_CLASS) !== null) {
            return true;
        }

        return array_key_exists($property->getName(), $classLevelColumnExposureByFieldNames);
    }

    protected function hasMappedField(ReflectionClass $reflectionClass, string $fieldName): bool
    {
        if (!$reflectionClass->hasProperty($fieldName)) {
            return false;
        }

        return $this->requiresMcpColumnAttribute($reflectionClass->getProperty($fieldName));
    }

    /**
     * @param array<\ReflectionAttribute<object>> $attributes
     * @return \ReflectionAttribute<object>|null
     */
    protected function getAttributeByClassName(array $attributes, string $attributeClassName): ?ReflectionAttribute
    {
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === $attributeClassName) {
                return $attribute;
            }
        }

        return null;
    }
}
