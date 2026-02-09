<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser;
use Shopsys\FrameworkBundle\Component\ClassExtension\PropertyAnnotationsFactory;
use Shopsys\FrameworkBundle\Component\ClassExtension\TypehintHelper;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass2;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass3;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass4;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass5;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass6;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass2;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass3;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass4;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass5;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass6;

class PropertyAnnotationsFactoryTest extends TestCase
{
    private PropertyAnnotationsFactory $propertyAnnotationsFactory;

    #[Override]
    protected function setUp(): void
    {
        $replacementMap = new AnnotationsReplacementsMap([
            'Shopsys\FrameworkBundle\Model\Category\CategoryFacade' => 'App\Model\Category\CategoryFacade',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass2' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass2',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass3' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass3',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass4' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass4',
            'Shopsys\FrameworkBundle\Model\Category\Category' => 'App\Model\Category\Category',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass5' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass5',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass6' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass6',
        ]);

        $typehintHelper = new TypehintHelper();
        $docBlockParser = new DocBlockParser();

        $this->propertyAnnotationsFactory = new PropertyAnnotationsFactory(
            $replacementMap,
            new AnnotationsReplacer($replacementMap, $docBlockParser, $typehintHelper),
            $typehintHelper,
            $docBlockParser,
        );
    }

    public static function getProjectClassNecessaryPropertyAnnotationsLinesEmptyResultDataProvider(): array
    {
        return [
            'property redeclared in the child using annotation' => [ReflectionObject::createFromName(
                BaseClass::class,
            ), ReflectionObject::createFromName(
                ChildClass::class,
            )],
            'property not included in the extension map' => [ReflectionObject::createFromName(
                BaseClass2::class,
            ), ReflectionObject::createFromName(
                ChildClass2::class,
            )],
            'property redeclared in the child\'s source code' => [ReflectionObject::createFromName(
                BaseClass3::class,
            ), ReflectionObject::createFromName(
                ChildClass3::class,
            )],
        ];
    }

    #[DataProvider('getProjectClassNecessaryPropertyAnnotationsLinesEmptyResultDataProvider')]
    public function testGetProjectClassNecessaryPropertyAnnotationsLinesEmptyResult(
        ReflectionClass $frameworkReflectionClass,
        ReflectionClass $projectReflectionClass,
    ): void {
        $annotationLines = $this->propertyAnnotationsFactory->getProjectClassNecessaryPropertyAnnotationsLines(
            $frameworkReflectionClass,
            $projectReflectionClass,
        );

        $this->assertEmpty($annotationLines);
    }

    public function testGetProjectClassNecessaryPropertyAnnotationsLines(): void
    {
        $annotationLines = $this->propertyAnnotationsFactory->getProjectClassNecessaryPropertyAnnotationsLines(
            ReflectionObject::createFromName(BaseClass4::class),
            ReflectionObject::createFromName(ChildClass4::class),
        );

        $this->assertStringContainsString(
            '@property \App\Model\Category\CategoryFacade $categoryFacade',
            $annotationLines,
        );
        $this->assertStringContainsString(
            '@property \Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass $class',
            $annotationLines,
        );
    }

    public function testAnnotationTakesPrecedenceOverTypehint(): void
    {
        $annotationLines = $this->propertyAnnotationsFactory->getProjectClassNecessaryPropertyAnnotationsLines(
            ReflectionObject::createFromName(BaseClass5::class),
            ReflectionObject::createFromName(ChildClass5::class),
        );

        // Annotation provides more specific type (Category[]) than typehint (array)
        // Should use annotation type, not fall back to typehint
        $this->assertStringContainsString(
            '@property \App\Model\Category\Category[] $categories',
            $annotationLines,
        );
    }

    public function testGetProjectClassNecessaryPropertyAnnotationsLinesWithTypehintOnly(): void
    {
        $annotationLines = $this->propertyAnnotationsFactory->getProjectClassNecessaryPropertyAnnotationsLines(
            ReflectionObject::createFromName(BaseClass6::class),
            ReflectionObject::createFromName(ChildClass6::class),
        );

        // Property with typehint only (no @var annotation)
        $this->assertStringContainsString(
            '@property \App\Model\Category\CategoryFacade $categoryFacade',
            $annotationLines,
        );

        // Property with nullable typehint only
        $this->assertStringContainsString(
            '@property \App\Model\Category\Category|null $nullableCategory',
            $annotationLines,
        );

        // Property with union typehint only
        $this->assertStringContainsString(
            '@property \App\Model\Category\Category|\App\Model\Category\CategoryFacade $unionType',
            $annotationLines,
        );
    }
}
