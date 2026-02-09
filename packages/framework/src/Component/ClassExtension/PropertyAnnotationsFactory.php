<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionProperty;

class PropertyAnnotationsFactory
{
    public function __construct(
        protected readonly AnnotationsReplacementsMap $annotationsReplacementsMap,
        protected readonly AnnotationsReplacer $annotationsReplacer,
        protected readonly TypehintHelper $typehintHelper,
        protected readonly DocBlockParser $docBlockParser,
    ) {
    }

    public function getProjectClassNecessaryPropertyAnnotationsLines(
        ReflectionClass $frameworkClassBetterReflection,
        ReflectionClass $projectClassBetterReflection,
    ): string {
        $projectClassDocBlock = $projectClassBetterReflection->getDocComment();
        $propertyAnnotationsLines = '';

        foreach ($frameworkClassBetterReflection->getProperties() as $property) {
            $propertyAnnotationLine = $this->getPropertyAnnotationLine($property, $projectClassBetterReflection);

            if ($propertyAnnotationLine !== '' && !str_contains($projectClassDocBlock ?? '', $propertyAnnotationLine)) {
                $propertyAnnotationsLines .= $propertyAnnotationLine;
            }
        }

        return $propertyAnnotationsLines;
    }

    protected function getPropertyAnnotationLine(
        ReflectionProperty $reflectionPropertyFromFrameworkClass,
        ReflectionClass $projectClassBetterReflection,
    ): string {
        foreach ($this->annotationsReplacementsMap->getPatterns() as $frameworkClassPattern) {
            if (!$this->isPropertyDeclaredInClass(
                $reflectionPropertyFromFrameworkClass->getName(),
                $projectClassBetterReflection,
            )
                && $this->isPropertyOfTypeThatIsExtendedInProject(
                    $reflectionPropertyFromFrameworkClass,
                    $frameworkClassPattern,
                )
            ) {
                $replacedTypeForProperty = $this->annotationsReplacer->replaceInPropertyType(
                    $reflectionPropertyFromFrameworkClass,
                );

                return sprintf(
                    " * @property %s%s $%s\n",
                    $reflectionPropertyFromFrameworkClass->isStatic() ? 'static ' : '',
                    $replacedTypeForProperty,
                    $reflectionPropertyFromFrameworkClass->getName(),
                );
            }
        }

        return '';
    }

    protected function isPropertyDeclaredInClass(string $propertyName, ReflectionClass $reflectionClass): bool
    {
        $reflectionProperty = $reflectionClass->getProperty($propertyName);

        if ($reflectionProperty === null) {
            return false;
        }

        return $reflectionProperty->getDeclaringClass()->getName() === $reflectionClass->getName();
    }

    protected function isPropertyOfTypeThatIsExtendedInProject(
        ReflectionProperty $reflectionProperty,
        string $frameworkClassPattern,
    ): bool {
        $type = $this->docBlockParser->getPropertyType($reflectionProperty);

        if ($type === null) {
            $type = $this->typehintHelper->getPropertyTypeFromTypehint($reflectionProperty);
        }

        if ($type === null) {
            return false;
        }

        return (bool)preg_match($frameworkClassPattern, (string)$type);
    }
}
