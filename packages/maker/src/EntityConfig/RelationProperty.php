<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\EntityConfig;

use Doctrine\Common\Collections\Collection;
use Override;
use Shopsys\MakerBundle\Utils\NamingHelper;

/**
 * Inspired by @see \Symfony\Bundle\MakerBundle\Doctrine\EntityRelation
 */
class RelationProperty extends Property
{
    /**
     * @param string $propertyName
     * @param \Shopsys\MakerBundle\EntityConfig\EntityRelationTypeEnum $relationType
     * @param string $relationOwningClass
     * @param string $relationTargetEntity
     * @param \Shopsys\MakerBundle\EntityConfig\EntityTypeEnum $entityType
     * @param bool $isNullable
     * @param bool $orphanRemoval
     * @param string|null $inverseProperty
     */
    public function __construct(
        public string $propertyName,
        public EntityRelationTypeEnum $relationType,
        public string $relationOwningClass,
        public string $relationTargetEntity,
        public EntityTypeEnum $entityType = EntityTypeEnum::ENTITY,
        public bool $isNullable = false,
        public bool $orphanRemoval = false,
        public ?string $inverseProperty = null,
    ) {
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getAttributeLines(): array
    {
        $options = [];

        $options[] = sprintf('targetEntity: \%s::class', $this->relationTargetEntity);

        if ($this->orphanRemoval === true) {
            $options[] = 'orphanRemoval: true';
        }

        if ($this->inverseProperty !== null) {
            $options[] = sprintf("%s: '%s'", $this->getInverseSettingName($this->relationType), $this->inverseProperty);
        }

        $attributeLines = [];

        $attributeLines[] = sprintf('#[ORM\%s(%s)]', $this->relationType->value, implode(', ', $options));

        if ($this->relationType === EntityRelationTypeEnum::MANY_TO_ONE) {
            $attributeLines[] = sprintf("#[ORM\JoinColumn(nullable: %s, onDelete: '%s')]", $this->isNullable ? 'true' : 'false', $this->isNullable ? 'SET NULL' : 'CASCADE');
        } elseif ($this->relationType === EntityRelationTypeEnum::MANY_TO_MANY) {
            $attributeLines[] = sprintf("#[ORM\JoinTable(name: '%s')]", NamingHelper::getJoinTableName($this->relationOwningClass, $this->relationTargetEntity));
        }

        return $attributeLines;
    }

    /**
     * @return string|null
     */
    public function getVarDocBlock(): ?string
    {
        if ($this->isCollection()) {
            return sprintf('@var \Doctrine\Common\Collections\Collection<int, \%s>', $this->relationTargetEntity);
        }

        return null;
    }

    /**
     * @param \Shopsys\MakerBundle\EntityConfig\CollectionTypeHintTypeEnum $collectionTypeHintType
     * @return string
     */
    #[Override]
    public function getTypeHint(
        CollectionTypeHintTypeEnum $collectionTypeHintType = CollectionTypeHintTypeEnum::COLLECTION,
    ): string {
        $typehintPrefix = $this->isNullable ? '?' : '';

        if ($this->isCollection()) {
            $type = match ($collectionTypeHintType) {
                CollectionTypeHintTypeEnum::COLLECTION => '\\' . Collection::class,
                CollectionTypeHintTypeEnum::ARRAY => 'array',
                CollectionTypeHintTypeEnum::INNER_CLASS => '\\' . $this->relationTargetEntity . '[]',
            };

            return sprintf('%s%s', $typehintPrefix, $type);
        }

        return sprintf('%s%s', $typehintPrefix, '\\' . $this->relationTargetEntity);
    }

    /**
     * @return bool
     */
    #[Override]
    public function isCollection(): bool
    {
        return $this->relationType === EntityRelationTypeEnum::ONE_TO_MANY || $this->relationType === EntityRelationTypeEnum::MANY_TO_MANY;
    }

    /**
     * @return string
     */
    #[Override]
    public function getGetterName(): string
    {
        return 'get' . ucfirst($this->propertyName);
    }

    /**
     * @param \Shopsys\MakerBundle\EntityConfig\EntityRelationTypeEnum $relationType
     * @return string
     */
    public function getInverseSettingName(EntityRelationTypeEnum $relationType): string
    {
        return $relationType === EntityRelationTypeEnum::ONE_TO_MANY ? 'mappedBy' : 'inversedBy';
    }

    /**
     * @return string|null
     */
    #[Override]
    public function getAdditionalInformation(): ?string
    {
        if ($this->inverseProperty === null) {
            return null;
        }

        $infoLines = [
            sprintf(
                '// TODO Do not forget to add $%s property to %s class:',
                $this->inverseProperty,
                $this->relationTargetEntity,
            ),
        ];

        $inverseRelation = EntityRelationTypeEnum::getInverseType($this->relationType);
        $infoLines[] = sprintf(
            "//  #[ORM\%s(targetEntity: \%s::class, %s: '%s')]",
            $inverseRelation->value,
            $this->relationOwningClass,
            $inverseRelation === EntityRelationTypeEnum::MANY_TO_MANY ? 'mappedBy' : $this->getInverseSettingName($inverseRelation),
            $this->propertyName,
        );
        $typehint = $inverseRelation === EntityRelationTypeEnum::MANY_TO_ONE ? $this->relationOwningClass : Collection::class;
        $infoLines[] = sprintf('//  private \%s $%s;', $typehint, $this->inverseProperty);

        return implode(PHP_EOL, $infoLines) . PHP_EOL;
    }
}
