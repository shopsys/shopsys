<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\EntityConfig;

use Doctrine\Common\Collections\Collection;
use Shopsys\FrameworkBundle\Maker\Utils\NamingHelper;

/**
 * Inspired by @see \Symfony\Bundle\MakerBundle\Doctrine\EntityRelation
 */
class RelationProperty extends Property
{
    /**
     * @param string $propertyName
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityRelationTypeEnum $relationType
     * @param string $relationOwningClass
     * @param string $relationTargetEntity
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityTypeEnum $entityType
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
    public function getAnnotationLines(): array
    {
        $options = [];

        $options[] = sprintf('targetEntity="%s"', $this->relationTargetEntity);

        if ($this->orphanRemoval === true) {
            $options[] = 'orphanRemoval=true';
        }

        if ($this->inverseProperty !== null) {
            $options[] = sprintf('%s="%s"', $this->getInverseSettingName($this->relationType), $this->inverseProperty);
        }

        $annotationLines = [];

        if ($this->isCollection()) {
            $annotationLines[] = sprintf('@var \Doctrine\Common\Collections\Collection<int, \%s>', $this->relationTargetEntity);
        }

        $annotationLines[] = sprintf('@ORM\%s(%s)', $this->relationType->value, implode(', ', $options));

        if ($this->relationType === EntityRelationTypeEnum::MANY_TO_ONE) {
            $annotationLines[] = sprintf('@ORM\JoinColumn(nullable=%s, onDelete="%s")', $this->isNullable ? 'true' : 'false', $this->isNullable ? 'SET NULL' : 'CASCADE');
        } elseif ($this->relationType === EntityRelationTypeEnum::MANY_TO_MANY) {
            $annotationLines[] = sprintf('@ORM\JoinTable(name="%s")', NamingHelper::getJoinTableName($this->relationOwningClass, $this->relationTargetEntity));
        }

        return $annotationLines;
    }

    /**
     * @param bool $collectionAsArray
     * @return string
     */
    public function getTypeHint(bool $collectionAsArray = false): string
    {
        $typehintPrefix = $this->isNullable ? '?' : '';

        if ($this->isCollection()) {
            return sprintf('%s%s', $typehintPrefix, $collectionAsArray ? 'array' : '\\' . Collection::class);
        }

        return sprintf('%s%s', $typehintPrefix, '\\' . $this->relationTargetEntity);
    }

    /**
     * @return bool
     */
    public function isCollection(): bool
    {
        return $this->relationType === EntityRelationTypeEnum::ONE_TO_MANY || $this->relationType === EntityRelationTypeEnum::MANY_TO_MANY;
    }

    /**
     * @return string
     */
    public function getGetterName(): string
    {
        return 'get' . ucfirst($this->propertyName);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityRelationTypeEnum $relationType
     * @return string
     */
    public function getInverseSettingName(EntityRelationTypeEnum $relationType): string
    {
        return $relationType === EntityRelationTypeEnum::ONE_TO_MANY ? 'mappedBy' : 'inversedBy';
    }

    /**
     * @return string|null
     */
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
            '//  /**',
        ];

        $inverseRelation = EntityRelationTypeEnum::getInverseType($this->relationType);
        $infoLines[] = sprintf(
            '//   * @ORM\%s(targetEntity="%s", %s="%s")',
            $inverseRelation->value,
            $this->relationOwningClass,
            $inverseRelation === EntityRelationTypeEnum::MANY_TO_MANY ? 'mappedBy' : $this->getInverseSettingName($inverseRelation),
            $this->propertyName,
        );
        $infoLines[] = '//   */';
        $typehint = $inverseRelation === EntityRelationTypeEnum::MANY_TO_ONE ? $this->relationOwningClass : Collection::class;
        $infoLines[] = sprintf('//  private \%s $%s;', $typehint, $this->inverseProperty);

        return implode(PHP_EOL, $infoLines) . PHP_EOL;
    }
}
