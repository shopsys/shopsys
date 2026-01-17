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

            try {
                $docBlockReturnTypes = $this->docBlockParser
                    ->getReturnTypes($reflectionMethodFromFrameworkClass->getDocComment());
            } catch (InvalidArgumentException $exception) {
                $this->warningBag[] = $exception;

                continue;
            }

            $methodReturnTypeIsExtended = $this->methodReturningTypeIsExtendedInProject(
                $frameworkClassPattern,
                $docBlockReturnTypes,
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

    /**
     * @param \phpDocumentor\Reflection\Type[] $docBlockReturnTypes
     */
    protected function methodReturningTypeIsExtendedInProject(
        string $frameworkClassPattern,
        array $docBlockReturnTypes,
    ): bool {
        foreach ($docBlockReturnTypes as $docBlockReturnType) {
            if (preg_match($frameworkClassPattern, (string)$docBlockReturnType)) {
                return true;
            }
        }

        return false;
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
                return false;
            }

            if (preg_match($frameworkClassPattern, (string)$type)) {
                return true;
            }
        }

        return false;
    }
}
