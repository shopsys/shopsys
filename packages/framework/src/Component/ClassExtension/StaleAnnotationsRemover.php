<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionClass;

class StaleAnnotationsRemover
{
    public function __construct(
        protected readonly FileContentsReplacer $fileContentsReplacer,
        protected readonly AnnotationsAdder $annotationsAdder,
    ) {
    }

    /**
     * @return string[]
     */
    public function getStaleAnnotationLines(
        ReflectionClass $frameworkClassBetterReflection,
        ReflectionClass $projectClassBetterReflection,
    ): array {
        $projectClassDocComment = $projectClassBetterReflection->getDocComment();

        if ($projectClassDocComment === '' || $projectClassDocComment === null) {
            return [];
        }

        $validFrameworkMethodNames = $this->getMethodNames($frameworkClassBetterReflection);
        $validFrameworkPropertyNames = $this->getPropertyNames($frameworkClassBetterReflection);
        [
            'methods' => $validFrameworkMethodNamesFromDocBlock,
            'properties' => $validFrameworkPropertyNamesFromDocBlock,
        ] = $this->getPropertyAndMethodNamesFromDocComment($frameworkClassBetterReflection->getDocComment());
        $projectMethodNames = $this->getMethodNames($projectClassBetterReflection);
        $projectPropertyNames = $this->getPropertyNames($projectClassBetterReflection);

        $validFrameworkMethodNames += $validFrameworkMethodNamesFromDocBlock;
        $validFrameworkPropertyNames += $validFrameworkPropertyNamesFromDocBlock;

        $staleLines = [];
        $annotationLines = explode("\n", $projectClassDocComment);

        array_shift($annotationLines);
        array_pop($annotationLines);

        foreach ($annotationLines as $annotationLine) {
            $key = $this->annotationsAdder->extractPropertyOrMethodAnnotationName($annotationLine);

            if ($this->isAnnotationStale(
                $key,
                $validFrameworkMethodNames,
                $validFrameworkPropertyNames,
                $projectMethodNames,
                $projectPropertyNames,
            )) {
                $staleLines[] = $annotationLine;
            }
        }

        return $staleLines;
    }

    public function removeStaleAnnotationsFromClass(
        ReflectionClass $frameworkClassBetterReflection,
        ReflectionClass $projectClassBetterReflection,
    ): void {
        $projectClassDocComment = $projectClassBetterReflection->getDocComment();

        if ($projectClassDocComment === '' || $projectClassDocComment === null) {
            return;
        }

        $staleLines = $this->getStaleAnnotationLines($frameworkClassBetterReflection, $projectClassBetterReflection);

        if ($staleLines === []) {
            return;
        }

        $staleLinesSet = array_flip($staleLines);
        $allLines = explode("\n", $projectClassDocComment);
        $filteredLines = [];

        foreach ($allLines as $line) {
            if (!isset($staleLinesSet[$line])) {
                $filteredLines[] = $line;
            }
        }

        if (count($filteredLines) === 2) {
            $newDocComment = '';
            $projectClassDocComment .= "\n";
        } else {
            $newDocComment = implode("\n", $filteredLines);
        }

        $this->fileContentsReplacer->replaceInFile(
            $projectClassBetterReflection->getFileName(),
            $projectClassDocComment,
            $newDocComment,
        );
    }

    /**
     * @return array<string, true>
     */
    protected function getMethodNames(ReflectionClass $reflectionClass): array
    {
        $names = [];

        foreach ($reflectionClass->getMethods() as $method) {
            $names[$method->getName()] = true;
        }

        return $names;
    }

    /**
     * @return array<string, true>
     */
    protected function getPropertyNames(ReflectionClass $reflectionClass): array
    {
        $names = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $names[$property->getName()] = true;
        }

        return $names;
    }

    /**
     * @return array{methods: array<string, true>, properties: array<string, true>}
     */
    protected function getPropertyAndMethodNamesFromDocComment(?string $docComment): array
    {
        if ($docComment === null || $docComment === '') {
            return [
                'methods' => [],
                'properties' => [],
            ];
        }

        $methodNames = [];
        $propertyNames = [];

        foreach (explode("\n", $docComment) as $docCommentLine) {
            $annotationKey = $this->annotationsAdder->extractPropertyOrMethodAnnotationName($docCommentLine);

            if (str_starts_with($annotationKey, 'method-')) {
                $methodNames[substr($annotationKey, 7)] = true;
            }

            if (str_starts_with($annotationKey, 'property-')) {
                $propertyNames[substr($annotationKey, 9)] = true;
            }
        }

        return [
            'methods' => $methodNames,
            'properties' => $propertyNames,
        ];
    }

    /**
     * @param array<string, true> $validFrameworkMethodNames
     * @param array<string, true> $validFrameworkPropertyNames
     * @param array<string, true> $projectMethodNames
     * @param array<string, true> $projectPropertyNames
     */
    protected function isAnnotationStale(
        string $annotationKey,
        array $validFrameworkMethodNames,
        array $validFrameworkPropertyNames,
        array $projectMethodNames,
        array $projectPropertyNames,
    ): bool {
        if (str_starts_with($annotationKey, 'method-')) {
            $methodName = substr($annotationKey, 7);

            return !isset($validFrameworkMethodNames[$methodName])
                && !isset($projectMethodNames[$methodName]);
        }

        if (str_starts_with($annotationKey, 'property-')) {
            $propertyName = substr($annotationKey, 9);

            return !isset($validFrameworkPropertyNames[$propertyName])
                && !isset($projectPropertyNames[$propertyName]);
        }

        return false;
    }
}
