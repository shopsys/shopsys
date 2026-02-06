<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionClass;

class AnnotationsAdder
{
    public function __construct(protected readonly FileContentsReplacer $fileContentReplacer)
    {
    }

    public function addAnnotationToClass(
        ReflectionClass $betterReflectionClass,
        string $propertyAndMethodAnnotationsLines,
    ): void {
        $projectClassDocComment = $betterReflectionClass->getDocComment();
        $projectClassFileName = $betterReflectionClass->getFileName();

        if ($propertyAndMethodAnnotationsLines === '') {
            return;
        }

        if ($projectClassDocComment === '' || $projectClassDocComment === null) {
            $classDeclaration = $this->getClassDeclarationString($betterReflectionClass);

            $this->fileContentReplacer->replaceInFile(
                $projectClassFileName,
                $classDeclaration,
                "/**\n" . $propertyAndMethodAnnotationsLines . " */\n" . $classDeclaration,
            );
        } else {
            $replacedClassDocBlock = $this->replaceInClassDocBlock(
                $projectClassDocComment,
                $propertyAndMethodAnnotationsLines,
            );
            $this->fileContentReplacer->replaceInFile(
                $projectClassFileName,
                $projectClassDocComment,
                $replacedClassDocBlock,
            );
        }
    }

    /**
     * Appends annotations to a doc block, annotation lines with colliding "name" will get replaced instead
     *
     * @see extractPropertyOrMethodAnnotationName() for explanation of how the "name" works
     */
    protected function replaceInClassDocBlock(string $classDocBlock, string $propertyAndMethodAnnotationsLines): string
    {
        $annotationLinesByName = [];

        $annotationLines = explode("\n", $classDocBlock);
        $annotationStart = array_shift($annotationLines);
        $annotationEnd = array_pop($annotationLines);

        foreach ($annotationLines as $annotationLine) {
            $annotationLinesByName[$this->extractPropertyOrMethodAnnotationName($annotationLine)] = $annotationLine;
        }
        $annotationLinesToAdd = array_filter(explode("\n", $propertyAndMethodAnnotationsLines));

        foreach ($annotationLinesToAdd as $annotationLine) {
            $annotationLinesByName[$this->extractPropertyOrMethodAnnotationName($annotationLine)] = $annotationLine;
        }

        return implode("\n", [$annotationStart, ...array_values($annotationLinesByName), $annotationEnd]);
    }

    /**
     * For property or method annotations returns just their name, eg. "method-setName" or "property-annotationsAdder"
     * Otherwise it will return the whole annotation line, eg " * AnnotationsAdder constructor"
     *
     * @see \Tests\FrameworkBundle\Unit\Component\ClassExtension\AnnotationsAdderTest::testExtractPropertyOrMethodAnnotationName()
     */
    protected function extractPropertyOrMethodAnnotationName(string $annotationLine): string
    {
        if (preg_match('~@(property|method)\s+(?:\S+\s+)??(?:\$(\w+)|(\w+)\s*\()~', $annotationLine, $matches)) {
            return $matches[1] . '-' . $matches[2] . ($matches[3] ?? '');
        }

        return $annotationLine;
    }

    protected function getClassDeclarationString(ReflectionClass $reflectionClass): string
    {
        $parts = [];

        if ($reflectionClass->isFinal()) {
            $parts[] = 'final';
        }

        if ($reflectionClass->isAbstract()) {
            $parts[] = 'abstract';
        }

        if ($reflectionClass->isReadOnly()) {
            $parts[] = 'readonly';
        }

        $parts[] = 'class';
        $parts[] = $reflectionClass->getShortName();

        return implode(' ', $parts);
    }
}
