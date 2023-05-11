<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use InvalidArgumentException;
use OutOfBoundsException;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

class MethodAnnotationsFactory
{
    protected AnnotationsReplacementsMap $annotationsReplacementsMap;

    protected AnnotationsReplacer $annotationsReplacer;

    protected DocBlockParser $docBlockParser;

    /**
     * @var \InvalidArgumentException[]
     */
    protected array $warningBag = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer $annotationsReplacer
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser $docBlockParser
     */
    public function __construct(
        AnnotationsReplacementsMap $annotationsReplacementsMap,
        AnnotationsReplacer $annotationsReplacer,
        DocBlockParser $docBlockParser
    ) {
        $this->annotationsReplacementsMap = $annotationsReplacementsMap;
        $this->annotationsReplacer = $annotationsReplacer;
        $this->docBlockParser = $docBlockParser;
    }

    /**
     * @return \InvalidArgumentException[]
     */
    public function getWarnings(): array
    {
        return $this->warningBag;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $frameworkClassBetterReflection
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     * @return string
     */
    public function getProjectClassNecessaryMethodAnnotationsLines(
        ReflectionClass $frameworkClassBetterReflection,
        ReflectionClass $projectClassBetterReflection
    ): string {
        $projectClassDocBlock = $projectClassBetterReflection->getDocComment();
        $methodAnnotationsLines = '';

        foreach ($frameworkClassBetterReflection->getMethods() as $method) {
            $methodAnnotationLine = $this->getMethodAnnotationLine($method, $projectClassBetterReflection);

            if ($methodAnnotationLine !== '' && strpos($projectClassDocBlock, $methodAnnotationLine) === false) {
                $methodAnnotationsLines .= $methodAnnotationLine;
            }
        }

        return $methodAnnotationsLines;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethodFromFrameworkClass
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectClassBetterReflection
     * @return string
     */
    public function getMethodAnnotationLine(
        ReflectionMethod $reflectionMethodFromFrameworkClass,
        ReflectionClass $projectClassBetterReflection
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
                $docBlockReturnTypes
            );

            $methodParameterTypeIsExtended = $this->methodParameterTypeIsExtendedInProject(
                $frameworkClassPattern,
                $reflectionMethodFromFrameworkClass->getParameters()
            );

            if ($methodReturnTypeIsExtended || $methodParameterTypeIsExtended) {
                $optionalStaticKeyword = $reflectionMethodFromFrameworkClass->isStatic() ? 'static ' : '';

                $replaceReturnType = $this->annotationsReplacer->replaceInMethodReturnType(
                    $reflectionMethodFromFrameworkClass
                );

                $returnType = $replaceReturnType !== '' ? $replaceReturnType . ' ' : '';
                $parameterNamesWithTypes = $this->getMethodParameterNamesWithTypes(
                    $reflectionMethodFromFrameworkClass
                );

                return sprintf(
                    " * @method %s%s%s(%s)\n",
                    $optionalStaticKeyword,
                    $returnType,
                    $methodName,
                    $parameterNamesWithTypes
                );
            }
        }

        return '';
    }

    /**
     * @param string $methodName
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $reflectionClass
     * @return bool
     */
    protected function isMethodImplementedInClass(string $methodName, ReflectionClass $reflectionClass): bool
    {
        try {
            $reflectionMethod = $reflectionClass->getMethod($methodName);

            return $reflectionMethod->getDeclaringClass()->getName() === $reflectionClass->getName();
        } catch (OutOfBoundsException $ex) {
            return false;
        }
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @return string
     */
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
     * @param string $frameworkClassPattern
     * @param \phpDocumentor\Reflection\Type[] $docBlockReturnTypes
     * @return bool
     */
    protected function methodReturningTypeIsExtendedInProject(
        string $frameworkClassPattern,
        array $docBlockReturnTypes
    ): bool {
        foreach ($docBlockReturnTypes as $docBlockReturnType) {
            if (preg_match($frameworkClassPattern, (string)$docBlockReturnType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $frameworkClassPattern
     * @param \Roave\BetterReflection\Reflection\ReflectionParameter[] $methodParameters
     * @return bool
     */
    protected function methodParameterTypeIsExtendedInProject(
        string $frameworkClassPattern,
        array $methodParameters
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
