<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use InvalidArgumentException;
use OutOfBoundsException;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

class MethodAnnotationsFactory
{
    /**
     * @var \InvalidArgumentException[]
     */
    protected array $warningBag = [];

    public function __construct(
        protected readonly AnnotationsReplacementsMap $annotationsReplacementsMap,
        protected readonly AnnotationsReplacer $annotationsReplacer,
        protected readonly DocBlockParser $docBlockParser,
        protected readonly TypehintHelper $typehintHelper,
    ) {
    }

    /**
     * @return \InvalidArgumentException[]
     */
    public function getWarnings(): array
    {
        return $this->warningBag;
    }

    public function getProjectClassNecessaryMethodAnnotationsLines(
        ReflectionClass $frameworkClassBetterReflection,
        ReflectionClass $projectClassBetterReflection,
    ): string {
        $projectClassDocBlock = $projectClassBetterReflection->getDocComment();
        $methodAnnotationsLines = '';

        foreach ($frameworkClassBetterReflection->getMethods() as $method) {
            $methodAnnotationLine = $this->getMethodAnnotationLine($method, $projectClassBetterReflection);

            if ($methodAnnotationLine !== '' && !str_contains($projectClassDocBlock ?? '', $methodAnnotationLine)) {
                $methodAnnotationsLines .= $methodAnnotationLine;
            }
        }

        return $methodAnnotationsLines;
    }

    public function getMethodAnnotationLine(
        ReflectionMethod $reflectionMethodFromFrameworkClass,
        ReflectionClass $projectClassBetterReflection,
    ): string {
        foreach ($this->annotationsReplacementsMap->getPatterns() as $frameworkClassPattern) {
            $methodName = $reflectionMethodFromFrameworkClass->getName();

            if ($this->isMethodImplementedInClass($methodName, $projectClassBetterReflection)) {
                continue;
            }

            $methodReturnTypeIsExtended = $this->methodReturningTypeIsExtendedInProject(
                $frameworkClassPattern,
                $reflectionMethodFromFrameworkClass,
            );

            $methodParameterTypeIsExtended = $this->methodParameterTypeIsExtendedInProject(
                $frameworkClassPattern,
                $reflectionMethodFromFrameworkClass->getParameters(),
            );

            if ($methodReturnTypeIsExtended || $methodParameterTypeIsExtended) {
                $optionalStaticKeyword = $reflectionMethodFromFrameworkClass->isStatic() ? 'static ' : '';

                $replaceReturnType = $this->annotationsReplacer->replaceInMethodReturnType(
                    $reflectionMethodFromFrameworkClass,
                );

                $returnType = $replaceReturnType !== '' ? $replaceReturnType . ' ' : '';
                $parameterNamesWithTypes = $this->getMethodParameterNamesWithTypes(
                    $reflectionMethodFromFrameworkClass,
                );

                return sprintf(
                    " * @method %s%s%s(%s)\n",
                    $optionalStaticKeyword,
                    $returnType,
                    $methodName,
                    $parameterNamesWithTypes,
                );
            }
        }

        return '';
    }

    protected function isMethodImplementedInClass(string $methodName, ReflectionClass $reflectionClass): bool
    {
        try {
            $reflectionMethod = $reflectionClass->getMethod($methodName);

            return $reflectionMethod?->getDeclaringClass()->getName() === $reflectionClass->getName();
        } catch (OutOfBoundsException $ex) {
            return false;
        }
    }

    protected function getMethodParameterNamesWithTypes(ReflectionMethod $reflectionMethod): string
    {
        $methodParameterNamesWithTypes = [];

        foreach ($reflectionMethod->getParameters() as $methodParameter) {
            $defaultValue = '';

            if ($methodParameter->isDefaultValueAvailable()) {
                $defaultValue .= $methodParameter->isDefaultValueConstant()
                    ? ' = \\' . $methodParameter->getDefaultValueConstantName()
                    : ' = ' . json_encode($methodParameter->getDefaultValue());
            }

            $methodParameterNamesWithTypes[] = sprintf(
                '%s $%s%s',
                $this->annotationsReplacer->replaceInParameterType($methodParameter),
                $methodParameter->getName(),
                $defaultValue,
            );
        }

        return implode(', ', $methodParameterNamesWithTypes);
    }

    protected function methodReturningTypeIsExtendedInProject(
        string $frameworkClassPattern,
        ReflectionMethod $reflectionMethod,
    ): bool {
        try {
            $docBlockReturnTypes = $this->docBlockParser->getReturnTypes($reflectionMethod->getDocComment());
        } catch (InvalidArgumentException $exception) {
            $this->warningBag[] = $exception;
            $docBlockReturnTypes = [];
        }

        if ($docBlockReturnTypes !== []) {
            foreach ($docBlockReturnTypes as $docBlockReturnType) {
                if (preg_match($frameworkClassPattern, (string)$docBlockReturnType)) {
                    return true;
                }
            }

            return false;
        }

        // Fallback to typehint when no docblock annotation exists or when docblock parsing failed
        $typehintReturnType = $this->typehintHelper->getMethodReturnTypeFromTypehint($reflectionMethod);

        if ($typehintReturnType === null) {
            return false;
        }

        return (bool)preg_match($frameworkClassPattern, $typehintReturnType);
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionParameter[] $methodParameters
     */
    protected function methodParameterTypeIsExtendedInProject(
        string $frameworkClassPattern,
        array $methodParameters,
    ): bool {
        foreach ($methodParameters as $methodParameter) {
            $type = $this->docBlockParser->getParameterType($methodParameter);

            if ($type === null) {
                $type = $this->typehintHelper->getParameterTypeFromTypehint($methodParameter);
            }

            if ($type === null) {
                continue;
            }

            if (preg_match($frameworkClassPattern, (string)$type)) {
                return true;
            }
        }

        return false;
    }
}
