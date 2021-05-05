<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsAdder;
use Shopsys\FrameworkBundle\Component\ClassExtension\FileContentsReplacer;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\AnnotationsAdderTest\DummyClassWithAnAnnotation;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\AnnotationsAdderTest\DummyClassWithMethodAnnotation;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\AnnotationsAdderTest\DummyClassWithNoAnnotation;

class AnnotationsAdderTest extends TestCase
{
    public function testAddAnnotationToClassThatHasNoAnnotationYet(): void
    {
        $betterReflectionClass = ReflectionObject::createFromName(DummyClassWithNoAnnotation::class);
        /** @var \Shopsys\FrameworkBundle\Component\ClassExtension\FileContentsReplacer|\PHPUnit\Framework\MockObject\MockObject $fileContentsReplacerMock */
        $fileContentsReplacerMock = $this->getMockBuilder(FileContentsReplacer::class)
            ->setMethods(['replaceInFile'])
            ->getMock();
        $classKeywordWithName = 'class ' . $betterReflectionClass->getShortName();
        $propertyAndMethodAnnotationsLines = "@property CategoryFacade \$categoryFacade\n@method CategoryFacade getCategoryFacade()\n";
        $fileContentsReplacerMock->expects($this->once())->method('replaceInFile')->with(
            $betterReflectionClass->getFileName(),
            $classKeywordWithName,
            "/**\n" . $propertyAndMethodAnnotationsLines . " */\n" . $classKeywordWithName
        );

        $annotationsAdder = new AnnotationsAdder($fileContentsReplacerMock);
        $annotationsAdder->addAnnotationToClass(
            $betterReflectionClass,
            $propertyAndMethodAnnotationsLines
        );
    }

    public function testAddAnnotationToClassThatHasAlreadyAnAnnotation(): void
    {
        $betterReflectionClass = ReflectionObject::createFromName(DummyClassWithAnAnnotation::class);
        /** @var \Shopsys\FrameworkBundle\Component\ClassExtension\FileContentsReplacer|\PHPUnit\Framework\MockObject\MockObject $fileContentsReplacerMock */
        $fileContentsReplacerMock = $this->getMockBuilder(FileContentsReplacer::class)
            ->setMethods(['replaceInFile'])
            ->getMock();
        $propertyAndMethodAnnotationsLines = "@property CategoryFacade \$categoryFacade\n@method CategoryFacade getCategoryFacade()\n";
        $docComment = $betterReflectionClass->getDocComment();
        $fileContentsReplacerMock->expects($this->once())->method('replaceInFile')->with(
            $betterReflectionClass->getFileName(),
            $docComment,
            str_replace(' */', $propertyAndMethodAnnotationsLines . ' */', $docComment)
        );

        $annotationsAdder = new AnnotationsAdder($fileContentsReplacerMock);
        $annotationsAdder->addAnnotationToClass(
            $betterReflectionClass,
            $propertyAndMethodAnnotationsLines
        );
    }

    public function testAddAnnotationToClassEmptyAnnotation(): void
    {
        $betterReflectionClass = ReflectionObject::createFromName(DummyClassWithAnAnnotation::class);
        /** @var \Shopsys\FrameworkBundle\Component\ClassExtension\FileContentsReplacer|\PHPUnit\Framework\MockObject\MockObject $fileContentsReplacerMock */
        $fileContentsReplacerMock = $this->getMockBuilder(FileContentsReplacer::class)
            ->setMethods(['replaceInFile'])
            ->getMock();
        $fileContentsReplacerMock->expects($this->never())->method('replaceInFile');

        $annotationsAdder = new AnnotationsAdder($fileContentsReplacerMock);
        $annotationsAdder->addAnnotationToClass($betterReflectionClass, '');
    }

    public function testAddMethodAnnotationToClassReplacesPrevious(): void
    {
        $betterReflectionClass = ReflectionObject::createFromName(DummyClassWithMethodAnnotation::class);
        $fileContentsReplacerMock = $this->createMock(FileContentsReplacer::class);
        $fileContentsReplacerMock->expects($this->once())->method('replaceInFile')->with(
            $betterReflectionClass->getFileName(),
            "/**\n * @method void setCategory(\\Shopsys\\FrameworkBundle\\Model\\Category\\Category \$category)\n */",
            "/**\n * @method void setCategory(\\App\\Model\\Category\\Category \$category)\n */"
        );

        $annotationsAdder = new AnnotationsAdder($fileContentsReplacerMock);
        $annotationsAdder->addAnnotationToClass(
            $betterReflectionClass,
            " * @method void setCategory(\\App\\Model\\Category\\Category \$category)\n"
        );
    }
}
