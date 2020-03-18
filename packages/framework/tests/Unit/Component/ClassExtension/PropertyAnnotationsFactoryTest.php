<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\PropertyAnnotationsFactory;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass2;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass3;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass4;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass2;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass3;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass4;

class PropertyAnnotationsFactoryTest extends TestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\PropertyAnnotationsFactory
     */
    private $propertyAnnotationsFactory;

    protected function setUp(): void
    {
        $replacementMap = new AnnotationsReplacementsMap([
            'Shopsys\FrameworkBundle\Model\Category\CategoryFacade' => 'App\Model\Category\CategoryFacade',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass2' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass2',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass3' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass3',
            'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\BaseClass4' => 'Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\PropertyAnnotationsFactoryTest\ChildClass4',
        ]);

        $this->propertyAnnotationsFactory = new PropertyAnnotationsFactory($replacementMap, new AnnotationsReplacer($replacementMap));
    }

    /**
     * @return array
     */
    public function testGetProjectClassNecessaryPropertyAnnotationsLinesEmptyResultDataProvider(): array
    {
        return [
            'property redeclared in the child using annotation' => [ReflectionObject::createFromName(BaseClass::class), ReflectionObject::createFromName(ChildClass::class)],
            'property not included in the extension map' => [ReflectionObject::createFromName(BaseClass2::class), ReflectionObject::createFromName(ChildClass2::class)],
            'property redeclared in the child\'s source code' => [ReflectionObject::createFromName(BaseClass3::class), ReflectionObject::createFromName(ChildClass3::class)],
        ];
    }

    /**
     * @dataProvider testGetProjectClassNecessaryPropertyAnnotationsLinesEmptyResultDataProvider
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $frameworkReflectionClass
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $projectReflectionClass
     */
    public function testGetProjectClassNecessaryPropertyAnnotationsLinesEmptyResult(
        ReflectionClass $frameworkReflectionClass,
        ReflectionClass $projectReflectionClass
    ): void {
        $annotationLines = $this->propertyAnnotationsFactory->getProjectClassNecessaryPropertyAnnotationsLines(
            $frameworkReflectionClass,
            $projectReflectionClass
        );

        $this->assertEmpty($annotationLines);
    }

    public function testGetProjectClassNecessaryPropertyAnnotationsLines(): void
    {
        $annotationLines = $this->propertyAnnotationsFactory->getProjectClassNecessaryPropertyAnnotationsLines(
            ReflectionObject::createFromName(BaseClass4::class),
            ReflectionObject::createFromName(ChildClass4::class)
        );

        $this->assertStringContainsString('@property \App\Model\Category\CategoryFacade $categoryFacade', $annotationLines);
    }
}
