<?php

declare(strict_types=1);

namespace App\Component\Test;

use ReflectionClass;
use ReflectionProperty;
use Zalas\Injector\Service\Exception\MissingServiceIdException;
use Zalas\Injector\Service\Extractor;
use Zalas\Injector\Service\Property;

/**
 * Service extractor that recognizes both @inject annotation and #[Inject] attribute
 * 
 * @IgnoreAnnotation("inject")
 */
final class AttributeServiceExtractor implements Extractor
{
    /**
     * @param array<class-string> $ignoredClasses
     */
    public function __construct(
        private readonly array $ignoredClasses = [],
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function extract(string $class): array
    {
        return $this->extractFromReflection(new ReflectionClass($class));
    }

    /**
     * @param \ReflectionClass $class
     * @return array<\Zalas\Injector\Service\Property>
     */
    private function extractFromReflection(ReflectionClass $class): array
    {
        $properties = $this->mapClassToServiceProperties($class);
        $parentProperties = $class->getParentClass() ? $this->extractFromReflection($class->getParentClass()) : [];

        return array_merge($properties, $parentProperties);
    }

    /**
     * @param \ReflectionClass $class
     * @return array<\Zalas\Injector\Service\Property>
     */
    private function mapClassToServiceProperties(ReflectionClass $class): array
    {
        if (in_array($class->getName(), $this->ignoredClasses, true)) {
            return [];
        }

        return array_map([$this, 'createProperty'], $this->filterReflectionPropertiesForInjection($class));
    }

    /**
     * @param \ReflectionClass $class
     * @return array<\ReflectionProperty>
     */
    private function filterReflectionPropertiesForInjection(ReflectionClass $class): array
    {
        return array_filter(
            $class->getProperties(),
            function (ReflectionProperty $property) use ($class): bool {
                return $property->getDeclaringClass()->getName() === $class->getName()
                    && $this->shouldInjectProperty($property);
            },
        );
    }

    /**
     * Check if property should be injected (has @inject annotation or #[Inject] attribute)
     */
    private function shouldInjectProperty(ReflectionProperty $property): bool
    {
        // Check for #[Inject] attribute
        $injectAttributes = $property->getAttributes(Inject::class);
        if (!empty($injectAttributes)) {
            return true;
        }

        // Check for @inject annotation
        $docComment = $property->getDocComment();
        return $docComment !== false && preg_match('#\s*\**\s*\@inject#', $docComment) === 1;
    }

    /**
     * Extract service ID from property (from attribute parameter, annotation parameter, or type hint)
     */
    private function extractServiceId(ReflectionProperty $property): string
    {
        // Check #[Inject] attribute first
        $injectAttributes = $property->getAttributes(Inject::class);
        if (!empty($injectAttributes)) {
            $injectAttribute = $injectAttributes[0]->newInstance();
            $explicitServiceId = $injectAttribute->getServiceId();
            if ($explicitServiceId !== null) {
                return $explicitServiceId;
            }
        }

        // Check @inject annotation with explicit service ID
        $docComment = $property->getDocComment();
        if ($docComment !== false && preg_match('#\s*\**\s*\@inject (?P<serviceId>[^\s]+)#', $docComment, $matches) === 1) {
            return $matches['serviceId'];
        }

        // Use type hint as service ID
        $type = $property->getType();
        if ($type && !$type->isBuiltin()) {
            return $type->getName();
        }

        throw new MissingServiceIdException($property->getDeclaringClass()->getName(), $property->getName());
    }

    private function createProperty(ReflectionProperty $property): Property
    {
        return new Property($property->getDeclaringClass()->getName(), $property->getName(), $this->extractServiceId($property));
    }
}