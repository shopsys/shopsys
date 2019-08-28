<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionProperty;

class AnnotationsReplacer
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap
     */
    protected $annotationsReplacementsMap;

    /**
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap
     */
    public function __construct(AnnotationsReplacementsMap $annotationsReplacementsMap)
    {
        $this->annotationsReplacementsMap = $annotationsReplacementsMap;
    }

    /**
     * @param string $string
     * @return string
     */
    public function replaceIn(string $string): string
    {
        return preg_replace(
            $this->annotationsReplacementsMap->getPatterns(),
            $this->annotationsReplacementsMap->getReplacements(),
            $string
        );
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @return string
     */
    public function replaceInMethodReturnType(ReflectionMethod $reflectionMethod): string
    {
        $methodReturnTypes = $reflectionMethod->getDocBlockReturnTypes();
        $replacedReturnTypes = [];
        foreach ($methodReturnTypes as $methodReturnType) {
            $replacedReturnTypes[] = $this->replaceIn((string)$methodReturnType);
        }

        return implode('|', $replacedReturnTypes);
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @return string
     */
    public function replaceInPropertyType(ReflectionProperty $reflectionProperty): string
    {
        $propertyType = implode('|', $reflectionProperty->getDocBlockTypeStrings());

        return $this->replaceIn($propertyType);
    }
}
