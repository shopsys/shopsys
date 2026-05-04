<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;

class AnnotationsReplacer
{
    public function __construct(
        protected readonly AnnotationsReplacementsMap $annotationsReplacementsMap,
        protected readonly DocBlockParser $docBlockParser,
        protected readonly TypehintHelper $typehintHelper,
    ) {
    }

    public function replaceIn(string $string): string
    {
        return preg_replace(
            $this->annotationsReplacementsMap->getPatterns(),
            $this->annotationsReplacementsMap->getReplacements(),
            $string,
        );
    }

    public function replaceInMethodReturnType(ReflectionMethod $reflectionMethod): string
    {
        $methodReturnTypes = $this->docBlockParser->getReturnTypes($reflectionMethod->getDocComment());

        if ($methodReturnTypes === []) {
            $typehintReturnType = $this->typehintHelper->getMethodReturnTypeFromTypehint($reflectionMethod);

            if ($typehintReturnType !== null) {
                return $this->replaceIn($typehintReturnType);
            }

            return '';
        }

        $replacedReturnTypes = [];

        foreach ($methodReturnTypes as $methodReturnType) {
            $replacedReturnTypes[] = $this->replaceInStringifiedType((string)$methodReturnType);
        }

        return implode('|', $replacedReturnTypes);
    }

    public function replaceInPropertyType(ReflectionProperty $reflectionProperty): string
    {
        $type = $this->docBlockParser->getPropertyType($reflectionProperty);

        if ($type === null) {
            $type = $this->typehintHelper->getPropertyTypeFromTypehint($reflectionProperty);
        }

        if ($type === null) {
            return '';
        }

        return $this->replaceInStringifiedType((string)$type);
    }

    public function replaceInParameterType(ReflectionParameter $reflectionParameter): string
    {
        $type = $this->docBlockParser->getParameterType($reflectionParameter);

        if ($type === null) {
            $type = $this->typehintHelper->getParameterTypeFromTypehint($reflectionParameter);
        }

        if ($type === null) {
            return '';
        }

        return $this->replaceInStringifiedType((string)$type);
    }

    /**
     * phpDocumentor's stringification of generics drops whitespace after commas
     * (e.g. `array<int, X>` becomes `array<int,X>`), causing duplicate-detection
     * via str_contains to fail against existing canonically-formatted annotations.
     */
    protected function replaceInStringifiedType(string $type): string
    {
        return $this->replaceIn(preg_replace('/,(?=\S)/', ', ', $type));
    }
}
