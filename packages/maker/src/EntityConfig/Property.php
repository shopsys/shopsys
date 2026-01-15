<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\EntityConfig;

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
    abstract public function getAttributeLines(): array;

    /**
     * @param \Shopsys\MakerBundle\EntityConfig\CollectionTypeHintTypeEnum $collectionTypeHintType
     * @return string
     */
    abstract public function getTypeHint(
        CollectionTypeHintTypeEnum $collectionTypeHintType = CollectionTypeHintTypeEnum::COLLECTION,
    ): string;

    /**
     * @return string
     */
    abstract public function getGetterName(): string;

    /**
     * @return bool
     */
    abstract public function isCollection(): bool;

    /**
     * @return string|null
     */
    public function getAnnotationForDataObject(): ?string
    {
        $type = null;

        if ($this->isForTranslation()) {
            $type = sprintf('array<string, %s>', $this->getTypeHint(CollectionTypeHintTypeEnum::INNER_CLASS));
        }

        if ($this->isForDomain()) {
            $type = sprintf('array<int, %s>', $this->getTypeHint(CollectionTypeHintTypeEnum::INNER_CLASS));
        }

        if ($this->isCollection()) {
            $type = $this->getTypeHint(CollectionTypeHintTypeEnum::INNER_CLASS);
        }

        if ($type !== null) {
            return PHP_EOL . '    /**' . PHP_EOL . '    * @var ' . $type . PHP_EOL . '     */' . PHP_EOL;
        }

        return null;
    }
}
