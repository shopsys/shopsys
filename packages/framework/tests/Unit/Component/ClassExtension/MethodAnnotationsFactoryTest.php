<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser;
use Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass2;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass3;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass4;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass5;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass2;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass3;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass4;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass5;

class MethodAnnotationsFactoryTest extends TestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\MethodAnnotationsFactory
     */
    private MethodAnnotationsFactory $methodAnnotationsFactory;

    protected function setUp(): void
    {
        $replacementMap = new AnnotationsReplacementsMap([
            'Shopsys\FrameworkBundle\Model\Category\CategoryFacade' => 'App\Model\Category\CategoryFacade',
            'Shopsys\FrameworkBundle\Model\Category\Category' => 'App\Model\Category\Category',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass2' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass2',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass3' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass3',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass4' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass4',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass5' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\ChildClass5',
        ]);

        $docBlockParser = new DocBlockParser();
        $this->methodAnnotationsFactory = new MethodAnnotationsFactory(
            $replacementMap,
            new AnnotationsReplacer($replacementMap, $docBlockParser),
            $docBlockParser,
        );
    }

    /**
     * @return array
     */
    public function getProjectClassNecessaryMethodAnnotationsLinesEmptyResultDataProvider(): array
    {
        return [
            'method redeclared in the child using annotation' => [ReflectionObject::createFromName(
                BaseClass::class
            ), ReflectionObject::createFromName(
                ChildClass::class
            )],
            'method not included in the extension map' => [ReflectionObject::createFromName(
                BaseClass2::class
            ), ReflectionObject::createFromName(
                ChildClass2::class
            )],
            'method redeclared in the child\'s source code' => [ReflectionObject::createFromName(
                BaseClass3::class
            ), ReflectionObject::createFromName(
                ChildClass3::class
            )],
        ];
    }

    /**
     * @dataProvider getProjectClassNecessaryMethodAnnotationsLinesEmptyResultDataProvider
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $frameworkReflectionClass
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectReflectionClass
     */
    public function testGetProjectClassNecessaryMethodAnnotationsLinesEmptyResult(
        ReflectionClass $frameworkReflectionClass,
        ReflectionClass $projectReflectionClass
    ): void {
        $annotationLines = $this->methodAnnotationsFactory->getProjectClassNecessaryMethodAnnotationsLines(
            $frameworkReflectionClass,
            $projectReflectionClass
        );

        $this->assertEmpty($annotationLines);
    }

    public function testGetProjectClassNecessaryMethodAnnotationsLines(): void
    {
        $annotationLines = $this->methodAnnotationsFactory->getProjectClassNecessaryMethodAnnotationsLines(
            ReflectionObject::createFromName(BaseClass4::class),
            ReflectionObject::createFromName(ChildClass4::class)
        );

        $this->assertStringContainsString(
            '@method \App\Model\Category\CategoryFacade getCategoryFacade()',
            $annotationLines
        );
        $this->assertStringContainsString(
            '@method setCategory(\App\Model\Category\Category $category)',
            $annotationLines
        );
    }

    public function testGetProjectClassNecessaryMethodWithDefaultValueAnnotationsLines(): void
    {
        $annotationLines = $this->methodAnnotationsFactory->getProjectClassNecessaryMethodAnnotationsLines(
            ReflectionObject::createFromName(BaseClass5::class),
            ReflectionObject::createFromName(ChildClass5::class)
        );

        $this->assertStringContainsString(
            '@method setCategory(\App\Model\Category\Category|null $category = null)',
            $annotationLines
        );
        $this->assertStringContainsString(
            '@method setCategoryWithStringWithDefaultParameters(\App\Model\Category\Category $category, string $string = "default", string $constant = \Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\MethodAnnotationsFactoryTest\BaseClass5::DEFAULT_VALUE, bool $true = true, bool $false = false, ?string $null = null, array $emptyArray = [])',
            $annotationLines
        );
    }
}
