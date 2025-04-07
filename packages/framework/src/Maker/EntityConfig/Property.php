<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\EntityConfig;

abstract class Property
{
    public string $propertyName;

    public EntityTypeEnum $entityType;

    /**
     * @return bool
     */
    public function isForTranslation(): bool
    {
        return $this->entityType === EntityTypeEnum::TRANSLATION;
    }

    /**
     * @return bool
     */
    public function isForDomain(): bool
    {
        return $this->entityType === EntityTypeEnum::DOMAIN;
    }

    /**
     * @return string|null
     */
    public function getAdditionalInformation(): ?string
    {
        return null;
    }

    /**
     * @return string[]
     */
    abstract public function getAnnotationLines(): array;

    /**
     * @param bool $collectionAsArray
     * @return string
     */
    abstract public function getTypeHint(bool $collectionAsArray = false): string;

    /**
     * @return string
     */
    abstract public function getGetterName(): string;

    /**
     * @return bool
     */
    abstract public function isCollection(): bool;
}
