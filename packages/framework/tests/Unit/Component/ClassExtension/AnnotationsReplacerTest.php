<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\DummyClassForAnnotationsReplacerTest;

class AnnotationsReplacerTest extends TestCase
{
    private AnnotationsReplacer $annotationsReplacer;

    protected function setUp(): void
    {
        $replacementMap = new AnnotationsReplacementsMap([
            'Shopsys\FrameworkBundle\Model\Category\CategoryFacade' => 'App\Model\Category\CategoryFacade',
            'Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface' => 'App\Model\MyProduct\ProductDataFactory',
            'Shopsys\FrameworkBundle\Model\Article\ArticleData' => 'App\Model\Article\ArticleData',
        ]);

        $this->annotationsReplacer = new AnnotationsReplacer($replacementMap, new DocBlockParser());
    }

    /**
     * @return array
     */
    public function getTestReplaceAnnotationsDataProvider(): array
    {
        return [
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade',
                'output' => '@var \App\Model\Category\CategoryFacade',
            ],
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface',
                'output' => '@var \App\Model\MyProduct\ProductDataFactory',
            ],
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Article\ArticleData',
                'output' => '@var \App\Model\Article\ArticleData',
            ],
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory',
                'output' => '@var \Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory',
            ],
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Article\ArticleDataInterface',
                'output' => '@var \Shopsys\FrameworkBundle\Model\Article\ArticleDataInterface',
            ],
            [
                'input' => '@param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade',
                'output' => '@param \App\Model\Category\CategoryFacade',
            ],
            [
                'input' => '@return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade',
                'output' => '@return \App\Model\Category\CategoryFacade',
            ],
            [
                'input' => '@return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade[]',
                'output' => '@return \App\Model\Category\CategoryFacade[]',
            ],
            [
                'input' => '@return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null',
                'output' => '@return \App\Model\Category\CategoryFacade|null',
            ],
            [
                'input' => '@return int',
                'output' => '@return int',
            ],
        ];
    }

    /**
     * @dataProvider getTestReplaceAnnotationsDataProvider
     * @param string $input
     * @param string $output
     */
    public function testReplaceIn(string $input, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceIn($input));
    }

    /**
     * @return array
     */
    public function getTestReplaceInMethodReturnTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacerTest::class);

        return [
            [$reflectionClass->getMethod('returnsFrameworkCategoryFacade'), '\App\Model\Category\CategoryFacade'],
            [$reflectionClass->getMethod(
                'returnsFrameworkCategoryFacadeOrNull',
            ), '\App\Model\Category\CategoryFacade|null'],
            [$reflectionClass->getMethod('returnsFrameworkArticleDataArray'), '\App\Model\Article\ArticleData[]'],
            [$reflectionClass->getMethod('returnsInt'), 'int'],
        ];
    }

    /**
     * @dataProvider getTestReplaceInMethodReturnTypeDataProvider
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @param string $output
     */
    public function testReplaceInMethodReturnType(ReflectionMethod $reflectionMethod, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceInMethodReturnType($reflectionMethod));
    }

    /**
     * @return array
     */
    public function getTestReplaceInInPropertyTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacerTest::class);

        return [
            [$reflectionClass->getProperty('categoryFacadeOrNull'), '\App\Model\Category\CategoryFacade|null'],
            [$reflectionClass->getProperty('integer'), 'int'],
            [$reflectionClass->getProperty('articleDataArray'), '\App\Model\Article\ArticleData[]'],
        ];
    }

    /**
     * @dataProvider getTestReplaceInInPropertyTypeDataProvider
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @param string $output
     */
    public function testReplaceInPropertyType(ReflectionProperty $reflectionProperty, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceInPropertyType($reflectionProperty));
    }

    /**
     * @return array
     */
    public function testReplaceInParameterTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacerTest::class);
        $reflectionMethod = $reflectionClass->getMethod('acceptsVariousParameters');

        return [
            [$reflectionMethod->getParameter('categoryFacade'), '\App\Model\Category\CategoryFacade'],
            [$reflectionMethod->getParameter('categoryFacadeOrNull'), '\App\Model\Category\CategoryFacade|null'],
            [$reflectionMethod->getParameter('array'), '\App\Model\Article\ArticleData[]'],
            [$reflectionMethod->getParameter('integer'), 'int'],
        ];
    }

    /**
     * @dataProvider testReplaceInParameterTypeDataProvider
     * @param \Roave\BetterReflection\Reflection\ReflectionParameter $reflectionParameter
     * @param string $output
     */
    public function testReplaceInParameterType(ReflectionParameter $reflectionParameter, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceInParameterType($reflectionParameter));
    }
}
