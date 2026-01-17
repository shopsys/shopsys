<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\EntityConfig;

abstract class Property
{
    public string $propertyName;

    public EntityTypeEnum $entityType;

    public function isForTranslation(): bool
    {
        return $this->entityType === EntityTypeEnum::TRANSLATION;
    }

    public function isForDomain(): bool
    {
        return $this->entityType === EntityTypeEnum::DOMAIN;
    }

    public function getAdditionalInformation(): ?string
    {
        return null;
    }

    /**
     * @return string[]
     */
    abstract public function getAnnotationLines(): array;

    abstract public function getTypeHint(
        CollectionTypeHintTypeEnum $collectionTypeHintType = CollectionTypeHintTypeEnum::COLLECTION,
    ): string;

    abstract public function getGetterName(): string;

    abstract public function isCollection(): bool;

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
