<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\EntityExtension;

use ReflectionObject;

class EntityNameResolver
{
    /**
     * @param string[] $entityExtensionMap
     */
    public function __construct(
        protected array $entityExtensionMap,
    ) {
    }

    public function resolve(string $entityName): string
    {
        return $this->entityExtensionMap[$entityName] ?? $entityName;
    }

    public function resolveIn(mixed $subject): mixed
    {
        if (is_string($subject)) {
            return $this->resolveInString($subject);
        }

        if (is_array($subject)) {
            return $this->resolveInArray($subject);
        }

        if (is_object($subject)) {
            $this->resolveInObjectProperties($subject);
        }

        return $subject;
    }

    /**
     * Replace every occurrence of the original FQNs with word borders on both sides and not followed by a back-slash
     */
    protected function resolveInString(string $string): string
    {
        foreach ($this->entityExtensionMap as $originalEntityName => $extendedEntityName) {
            $pattern = '~\b' . preg_quote($originalEntityName, '~') . '\b(?!\\\\)~u';
            $string = preg_replace($pattern, $extendedEntityName, $string);
        }

        return $string;
    }

    protected function resolveInArray(array $array): array
    {
        return array_map([$this, 'resolveIn'], $array);
    }

    /**
     * Resolve entity names recursively in all properties of the subject (even private ones)
     */
    protected function resolveInObjectProperties(object $object): void
    {
        $reflection = new ReflectionObject($object);

        foreach ($reflection->getProperties() as $property) {
            $value = $property->getValue($object);
            $resolvedValue = $this->resolveIn($value);
            $property->setValue($object, $resolvedValue);
        }
    }
}
