<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

use Roave\BetterReflection\Reflection\ReflectionClass;

class AnnotationsAdder
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\FileContentsReplacer
     */
    protected $fileContentReplacer;

    /**
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\FileContentsReplacer $fileContentReplacer
     */
    public function __construct(FileContentsReplacer $fileContentReplacer)
    {
        $this->fileContentReplacer = $fileContentReplacer;
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $betterReflectionClass
     * @param string $propertyAndMethodAnnotationsLines
     */
    public function addAnnotationToClass(ReflectionClass $betterReflectionClass, string $propertyAndMethodAnnotationsLines): void
    {
        $projectClassDocComment = $betterReflectionClass->getDocComment();
        $projectClassFileName = $betterReflectionClass->getFileName();
        if ($propertyAndMethodAnnotationsLines === '') {
            return;
        }
        if ($projectClassDocComment === '') {
            $classKeywordWithName = 'class ' . $betterReflectionClass->getShortName();
            $this->fileContentReplacer->replaceInFile(
                $projectClassFileName,
                $classKeywordWithName,
                "/**\n" . $propertyAndMethodAnnotationsLines . " */\n" . $classKeywordWithName
            );
        } else {
            $replacedClassDocBlock = $this->replaceAnnotationsInExistingDocBlock(
                $projectClassDocComment,
                $propertyAndMethodAnnotationsLines
            );
            $this->fileContentReplacer->replaceInFile(
                $projectClassFileName,
                $projectClassDocComment,
                $replacedClassDocBlock
            );
        }
    }

    /**
     * Appends second annotation block, annotation lines with colliding "name" will get replaced instead
     *
     * @see extractPropertyOrMethodAnnotationName() for explanation of how the "name" works
     * @param string $annotation
     * @param string $annotationToAdd
     * @return string
     */
    protected function replaceAnnotationsInExistingDocBlock(string $annotation, string $annotationToAdd): string
    {
        $annotationLinesByName = [];

        $annotationLines = explode("\n", $annotation);
        $annotationStart = array_shift($annotationLines);
        $annotationEnd = array_pop($annotationLines);
        foreach ($annotationLines as $annotationLine) {
            $annotationLinesByName[$this->extractPropertyOrMethodAnnotationName($annotationLine)] = $annotationLine;
        }
        $annotationLinesToAdd = array_filter(explode("\n", $annotationToAdd));
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
