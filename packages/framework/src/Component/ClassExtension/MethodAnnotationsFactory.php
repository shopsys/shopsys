<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

class MethodAnnotationsFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap
     */
    protected $annotationsReplacementsMap;

    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer
     */
    protected $annotationsReplacer;

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
            if (!$this->isMethodImplementedInClass($methodName, $projectClassBetterReflection)) {
                if ($this->methodReturningTypeIsExtendedInProject($frameworkClassPattern, $reflectionMethodFromFrameworkClass->getDocBlockReturnTypes())
                    || $this->methodParameterTypeIsExtendedInProject($frameworkClassPattern, $reflectionMethodFromFrameworkClass->getParameters())) {
                    $optionalStaticKeyword = $reflectionMethodFromFrameworkClass->isStatic() ? 'static ' : '';
                    $returnType = !empty($this->annotationsReplacer->replaceInMethodReturnType($reflectionMethodFromFrameworkClass)) ? $this->annotationsReplacer->replaceInMethodReturnType($reflectionMethodFromFrameworkClass) . ' ' : '';
                    $parameterNamesWithTypes = $this->getMethodParameterNamesWithTypes($reflectionMethodFromFrameworkClass);

                    return sprintf(
                        " * @method %s%s%s(%s)\n",
                        $optionalStaticKeyword,
                        $returnType,
                        $methodName,
                        $parameterNamesWithTypes
                    );
                }
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
        } catch (\OutOfBoundsException $ex) {
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
            $methodParameterNamesWithTypes[] = sprintf(
                '%s $%s',
                $this->annotationsReplacer->replaceInParameterType($methodParameter),
                $methodParameter->getName()
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
            foreach ($methodParameter->getDocBlockTypeStrings() as $typeString) {
                if (preg_match($frameworkClassPattern, $typeString)) {
                    return true;
                }
            }
        }

        return false;
    }
}
