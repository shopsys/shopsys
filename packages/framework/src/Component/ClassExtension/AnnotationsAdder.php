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
            $replacedClassDocBlock = str_replace(' */', $propertyAndMethodAnnotationsLines . ' */', $projectClassDocComment);
            $this->fileContentReplacer->replaceInFile($projectClassFileName, $projectClassDocComment, $replacedClassDocBlock);
        }
    }
}
