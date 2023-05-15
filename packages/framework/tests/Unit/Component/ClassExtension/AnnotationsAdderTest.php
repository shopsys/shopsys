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
            "/**\n" . $propertyAndMethodAnnotationsLines . " */\n" . $classKeywordWithName,
        );

        $annotationsAdder = new AnnotationsAdder($fileContentsReplacerMock);
        $annotationsAdder->addAnnotationToClass(
            $betterReflectionClass,
            $propertyAndMethodAnnotationsLines,
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
            str_replace(' */', $propertyAndMethodAnnotationsLines . ' */', $docComment),
        );

        $annotationsAdder = new AnnotationsAdder($fileContentsReplacerMock);
        $annotationsAdder->addAnnotationToClass(
            $betterReflectionClass,
            $propertyAndMethodAnnotationsLines,
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
            "/**\n * @method void setCategory(\\App\\Model\\Category\\Category \$category)\n */",
        );

        $annotationsAdder = new AnnotationsAdder($fileContentsReplacerMock);
        $annotationsAdder->addAnnotationToClass(
            $betterReflectionClass,
            " * @method void setCategory(\\App\\Model\\Category\\Category \$category)\n",
        );
    }

    /**
     * @return string[][]
     */
    public function extractPropertyOrMethodAnnotationNameDataProvider(): array
    {
        return [
            ['property-test', '@property $test'],
            ['property-test', '@property int $test'],
            ['property-test', '@property int[] $test'],
            ['property-test', '@property int[]|null $test'],
            ['property-test', '@property array<int,array<string,string[]>> $test'],
            ['property-test', '@property int[]|null $test This is a testing property'],
            ['property-testDifferentName', '@property $testDifferentName'],
            ['property-test', ' * @property $test  '],
            ['method-test', '@method test()'],
            ['method-test', '@method void test($parameter)'],
            ['method-test', '@method test(array $parameter)'],
            ['method-test', '@method test(array $parameter = [], int $number = 0)'],
            ['method-test', '@method int test()'],
            ['method-test', '@method int[] test()'],
            ['method-test', '@method int[]|null test()'],
            ['method-test', '@method array<int,array<string,string[]>> test()'],
            ['method-test', '@method int[]|null test() This is a testing method'],
            ['method-testDifferentName', '@method testDifferentName()'],
            ['method-test', ' * @method test()  '],
            ['@property invalidIdentifier', '@property invalidIdentifier'],
            ['@method invalidIdentifier', '@method invalidIdentifier'],
            ['@author Aaron Aardvark', '@author Aaron Aardvark'],
            ['Any non-property and non-method string', 'Any non-property and non-method string'],
        ];
    }

    /**
     * @dataProvider extractPropertyOrMethodAnnotationNameDataProvider
     * @param string $expectedPropertyName
     * @param string $propertyLine
     */
    public function testExtractPropertyOrMethodAnnotationName(string $expectedPropertyName, string $propertyLine): void
    {
        $fileContentsReplacerMock = $this->createMock(FileContentsReplacer::class);
        $annotationsAdder = (new class($fileContentsReplacerMock) extends AnnotationsAdder {
            /**
             * Method overridden to make it public and thus testable
             *
             * @param string $annotationLine
             * @return string
             */
            public function extractPropertyOrMethodAnnotationName(string $annotationLine): string
            {
                return parent::extractPropertyOrMethodAnnotationName($annotationLine);
            }
        });

        $annotationName = $annotationsAdder->extractPropertyOrMethodAnnotationName($propertyLine);

        $this->assertSame($expectedPropertyName, $annotationName);
    }
}
