<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Attribute;

class LoggableEntityConfig
{
    public function __construct(
        protected string $entityName,
        protected string $entityFullyQualifiedName,
        protected bool $isLoggable,
        protected array $excludedPropertyNames = [],
        protected array $includedPropertyNames = [],
        protected ?string $strategy = null,
        protected ?string $entityReadableNameFunctionName = null,
        protected bool $isLocalized = false,
        protected ?string $parentEntityName = null,
        protected ?string $parentEntityFunctionName = null,
        protected ?string $parentEntityIdentityFunctionName = null,
    ) {
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityFullyQualifiedName(): string
    {
        return $this->entityFullyQualifiedName;
    }

    public function isLoggable(): bool
    {
        return $this->isLoggable;
    }

    public function getStrategy(): ?string
    {
        return $this->strategy;
    }

    public function setStrategy(?string $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function getEntityReadableNameFunctionName(): ?string
    {
        return $this->entityReadableNameFunctionName;
    }

    public function setEntityReadableNameFunctionName(?string $entityReadableNameFunctionName): void
    {
        $this->entityReadableNameFunctionName = $entityReadableNameFunctionName;
    }

    public function isLocalized(): bool
    {
        return $this->isLocalized;
    }

    public function setIsLocalized(bool $isLocalized): void
    {
        $this->isLocalized = $isLocalized;
    }

    public function getParentEntityName(): ?string
    {
        return $this->parentEntityName;
    }

    public function setParentEntityName(string $parentEntityName): void
    {
        $this->parentEntityName = $parentEntityName;
        $this->parentEntityFunctionName = sprintf('get%s', ucfirst($parentEntityName));
    }

    public function getParentEntityFunctionName(): ?string
    {
        return $this->parentEntityFunctionName;
    }

    public function getParentEntityIdentityFunctionName(): ?string
    {
        return $this->parentEntityIdentityFunctionName;
    }

    public function setParentEntityIdentityFunctionName(string $parentEntityIdentityFunctionName): void
    {
        $this->parentEntityIdentityFunctionName = $parentEntityIdentityFunctionName;
    }

    public function addExcludedPropertyName(string $excludedPropertyName): void
    {
        $this->excludedPropertyNames[$excludedPropertyName] = $excludedPropertyName;
    }

    public function addIncludedPropertyName(string $includedPropertyName): void
    {
        $this->includedPropertyNames[$includedPropertyName] = $includedPropertyName;
    }

    public function isPropertyLoggable(string $propertyName): bool
    {
        if ($this->isLoggable() === false) {
            return false;
        }

        if ($this->getStrategy() === null) {
            return false;
        }

        if ($this->getStrategy() === Loggable::STRATEGY_EXCLUDE_ALL) {
            return array_key_exists($propertyName, $this->includedPropertyNames);
        }

        return array_key_exists($propertyName, $this->excludedPropertyNames) === false;
    }

    public function isEntityIdentifiable(): bool
    {
        return $this->getEntityReadableNameFunctionName() !== null;
    }
}
