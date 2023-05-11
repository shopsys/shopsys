<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionProperty;

class PropertyAnnotationsFactory
{
    protected AnnotationsReplacementsMap $annotationsReplacementsMap;

    protected AnnotationsReplacer $annotationsReplacer;

    /**
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer $annotationsReplacer
     */
    public function __construct(
        AnnotationsReplacementsMap $annotationsReplacementsMap,
        AnnotationsReplacer $annotationsReplacer
    ) {
        $this->annotationsReplacementsMap = $annotationsReplacementsMap;
        $this->annotationsReplacer = $annotationsReplacer;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $frameworkClassBetterReflection
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     * @return string
     */
    public function getProjectClassNecessaryPropertyAnnotationsLines(
        ReflectionClass $frameworkClassBetterReflection,
        ReflectionClass $projectClassBetterReflection
    ): string {
        $projectClassDocBlock = $projectClassBetterReflection->getDocComment();
        $propertyAnnotationsLines = '';

        foreach ($frameworkClassBetterReflection->getProperties() as $property) {
            $propertyAnnotationLine = $this->getPropertyAnnotationLine($property, $projectClassBetterReflection);

            if ($propertyAnnotationLine !== '' && strpos($projectClassDocBlock, $propertyAnnotationLine) === false) {
                $propertyAnnotationsLines .= $propertyAnnotationLine;
            }
        }

        return $propertyAnnotationsLines;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionPropertyFromFrameworkClass
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     * @return string
     */
    protected function getPropertyAnnotationLine(
        ReflectionProperty $reflectionPropertyFromFrameworkClass,
        ReflectionClass $projectClassBetterReflection
    ): string {
        foreach ($this->annotationsReplacementsMap->getPatterns() as $frameworkClassPattern) {
            if (!$this->isPropertyDeclaredInClass(
                $reflectionPropertyFromFrameworkClass->getName(),
                $projectClassBetterReflection
            )
                && $this->isPropertyOfTypeThatIsExtendedInProject(
                    $reflectionPropertyFromFrameworkClass,
                    $frameworkClassPattern
                )
            ) {
                $replacedTypeForProperty = $this->annotationsReplacer->replaceInPropertyType(
                    $reflectionPropertyFromFrameworkClass
                );

                return sprintf(
                    " * @property %s%s $%s\n",
                    $reflectionPropertyFromFrameworkClass->isStatic() ? 'static ' : '',
                    $replacedTypeForProperty,
                    $reflectionPropertyFromFrameworkClass->getName()
                );
            }
        }

        return '';
    }

    /**
     * @param string $propertyName
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $reflectionClass
     * @return bool
     */
    protected function isPropertyDeclaredInClass(string $propertyName, ReflectionClass $reflectionClass): bool
    {
        $reflectionProperty = $reflectionClass->getProperty($propertyName);

        if ($reflectionProperty === null) {
            return false;
        }

        return $reflectionProperty->getDeclaringClass()->getName() === $reflectionClass->getName();
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @param string $frameworkClassPattern
     * @return bool
     */
    protected function isPropertyOfTypeThatIsExtendedInProject(ReflectionProperty $reflectionProperty, string $frameworkClassPattern): bool
    {
        return (bool)preg_match(
            $frameworkClassPattern,
            $reflectionProperty->getDocComment()
        );
    }
}
