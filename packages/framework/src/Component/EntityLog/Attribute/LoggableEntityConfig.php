<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityLog\Attribute;

class LoggableEntityConfig
{
    /**
     * @param string $entityName
     * @param string $entityFullyQualifiedName
     * @param bool $isLoggable
     * @param array $excludedPropertyNames
     * @param array $includedPropertyNames
     * @param string|null $strategy
     * @param string|null $entityReadableNameFunctionName
     * @param bool $isLocalized
     * @param string|null $parentEntityName
     * @param string|null $parentEntityFunctionName
     * @param string|null $parentEntityIdentityFunctionName
     */
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

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return string
     */
    public function getEntityFullyQualifiedName(): string
    {
        return $this->entityFullyQualifiedName;
    }

    /**
     * @return bool
     */
    public function isLoggable(): bool
    {
        return $this->isLoggable;
    }

    /**
     * @return string|null
     */
    public function getStrategy(): ?string
    {
        return $this->strategy;
    }

    /**
     * @param string|null $strategy
     */
    public function setStrategy(?string $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @return string|null
     */
    public function getEntityReadableNameFunctionName(): ?string
    {
        return $this->entityReadableNameFunctionName;
    }

    /**
     * @param string|null $entityReadableNameFunctionName
     */
    public function setEntityReadableNameFunctionName(?string $entityReadableNameFunctionName): void
    {
        $this->entityReadableNameFunctionName = $entityReadableNameFunctionName;
    }

    /**
     * @return bool
     */
    public function isLocalized(): bool
    {
        return $this->isLocalized;
    }

    /**
     * @param bool $isLocalized
     */
    public function setIsLocalized(bool $isLocalized): void
    {
        $this->isLocalized = $isLocalized;
    }

    /**
     * @return string|null
     */
    public function getParentEntityName(): ?string
    {
        return $this->parentEntityName;
    }

    /**
     * @param string $parentEntityName
     */
    public function setParentEntityName(string $parentEntityName): void
    {
        $this->parentEntityName = $parentEntityName;
        $this->parentEntityFunctionName = sprintf('get%s', ucfirst($parentEntityName));
    }

    /**
     * @return string|null
     */
    public function getParentEntityFunctionName(): ?string
    {
        return $this->parentEntityFunctionName;
    }

    /**
     * @return string|null
     */
    public function getParentEntityIdentityFunctionName(): ?string
    {
        return $this->parentEntityIdentityFunctionName;
    }

    /**
     * @param string $parentEntityIdentityFunctionName
     */
    public function setParentEntityIdentityFunctionName(string $parentEntityIdentityFunctionName): void
    {
        $this->parentEntityIdentityFunctionName = $parentEntityIdentityFunctionName;
    }

    /**
     * @param string $excludedPropertyName
     */
    public function addExcludedPropertyName(string $excludedPropertyName): void
    {
        $this->excludedPropertyNames[$excludedPropertyName] = $excludedPropertyName;
    }

    /**
     * @param string $includedPropertyName
     */
    public function addIncludedPropertyName(string $includedPropertyName): void
    {
        $this->includedPropertyNames[$includedPropertyName] = $includedPropertyName;
    }

    /**
     * @param string $propertyName
     * @return bool
     */
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

    /**
     * @return bool
     */
    public function isEntityIdentifiable(): bool
    {
        return $this->getEntityReadableNameFunctionName() !== null;
    }
}
