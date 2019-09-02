<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use phpDocumentor\Reflection\Type;
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
            foreach ($reflectionMethodFromFrameworkClass->getDocBlockReturnTypes() as $docBlockReturnType) {
                if (!$this->isMethodImplementedInClass($reflectionMethodFromFrameworkClass->getName(), $projectClassBetterReflection)
                    && $this->isMethodReturningTypeThatIsExtendedInProject($frameworkClassPattern, $docBlockReturnType)
                ) {
                    return sprintf(
                        " * @method %s%s %s(%s)\n",
                        $reflectionMethodFromFrameworkClass->isStatic() ? 'static ' : '',
                        $this->annotationsReplacer->replaceInMethodReturnType($reflectionMethodFromFrameworkClass),
                        $reflectionMethodFromFrameworkClass->getName(),
                        $this->getMethodParameterNames($reflectionMethodFromFrameworkClass)
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
    protected function getMethodParameterNames(ReflectionMethod $reflectionMethod): string
    {
        $methodParameterNames = [];
        foreach ($reflectionMethod->getParameters() as $methodParameter) {
            $methodParameterNames[] = '$' . $methodParameter->getName();
        }

        return implode(', ', $methodParameterNames);
    }

    /**
     * @param string $frameworkClassPattern
     * @param \phpDocumentor\Reflection\Type $docBlockReturnType
     * @return bool
     */
    protected function isMethodReturningTypeThatIsExtendedInProject(
        string $frameworkClassPattern,
        Type $docBlockReturnType
    ): bool {
        return (bool)preg_match($frameworkClassPattern, (string)$docBlockReturnType);
    }
}
