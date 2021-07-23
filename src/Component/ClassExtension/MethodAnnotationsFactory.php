<?php

declare(strict_types=1);

namespace App\Component\ClassExtension;

use InvalidArgumentException;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory as BaseMethodAnnotationsFactory;

class MethodAnnotationsFactory extends BaseMethodAnnotationsFactory
{
    /**
     * @var \InvalidArgumentException[]
     */
    private array $warningBag = [];

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
                $docBlockReturnTypes = $reflectionMethodFromFrameworkClass->getDocBlockReturnTypes();
            } catch (InvalidArgumentException $exception) {
                $this->warningBag[md5($exception->getMessage())] = $exception;
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
     * @return \InvalidArgumentException[]
     */
    public function getWarningBag(): array
    {
        return $this->warningBag;
    }
}
