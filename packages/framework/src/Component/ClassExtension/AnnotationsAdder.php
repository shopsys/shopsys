<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionClass;

class AnnotationsAdder
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\FileContentsReplacer $fileContentReplacer
     */
    public function __construct(protected readonly FileContentsReplacer $fileContentReplacer)
    {
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $betterReflectionClass
     * @param string $propertyAndMethodAnnotationsLines
     */
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
            $classKeywordWithName = 'class ' . $betterReflectionClass->getShortName();
            $this->fileContentReplacer->replaceInFile(
                $projectClassFileName,
                $classKeywordWithName,
                "/**\n" . $propertyAndMethodAnnotationsLines . " */\n" . $classKeywordWithName,
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
     * @param string $classDocBlock
     * @param string $propertyAndMethodAnnotationsLines
     * @return string
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
     * @param string $annotationLine
     * @return string
     */
    protected function extractPropertyOrMethodAnnotationName(string $annotationLine): string
    {
        if (preg_match('~@(property|method)\s+(?:\S+\s+)??(?:\$(\w+)|(\w+)\s*\()~', $annotationLine, $matches)) {
            return $matches[1] . '-' . $matches[2] . ($matches[3] ?? '');
        }

        return $annotationLine;
    }
}
