<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\DummyClassForAnnotationsReplacer;

class AnnotationsReplacerTest extends TestCase
{
    private AnnotationsReplacer $annotationsReplacer;

    protected function setUp(): void
    {
        $replacementMap = new AnnotationsReplacementsMap([
            'Shopsys\FrameworkBundle\Model\Category\CategoryFacade' => 'App\Model\Category\CategoryFacade',
            'Shopsys\FrameworkBundle\Model\Product\ProductDataFactory' => 'App\Model\MyProduct\ProductDataFactory',
            'Shopsys\FrameworkBundle\Model\Article\ArticleData' => 'App\Model\Article\ArticleData',
            'Shopsys\FrontendApiBundle\Model\Product\ProductRepository' => 'App\FrontendApi\Model\Product\ProductRepository',
        ]);

        $this->annotationsReplacer = new AnnotationsReplacer($replacementMap, new DocBlockParser());
    }

    /**
     * @return array
     */
    public static function getTestReplaceAnnotationsDataProvider(): array
    {
        return [
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade',
                'output' => '@var \App\Model\Category\CategoryFacade',
            ],
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactory',
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
                'input' => '@var \Shopsys\FrontendApiBundle\Model\Product\ProductRepository',
                'output' => '@var \App\FrontendApi\Model\Product\ProductRepository',
            ],
            [
                'input' => '@param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade',
                'output' => '@param \App\Model\Category\CategoryFacade',
            ],
            [
                'input' => '@param \Shopsys\FrontendApiBundle\Model\Product\ProductRepository',
                'output' => '@param \App\FrontendApi\Model\Product\ProductRepository',
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
     * @param string $input
     * @param string $output
     */
    #[DataProvider('getTestReplaceAnnotationsDataProvider')]
    public function testReplaceIn(string $input, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceIn($input));
    }

    /**
     * @return array
     */
    public static function getTestReplaceInMethodReturnTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacer::class);

        return [
            [$reflectionClass->getMethod('returnsFrameworkCategoryFacade'), '\App\Model\Category\CategoryFacade'],
            [$reflectionClass->getMethod(
                'returnsFrameworkCategoryFacadeOrNull',
            ), '\App\Model\Category\CategoryFacade|null'],
            [$reflectionClass->getMethod('returnsFrameworkArticleDataArray'), '\App\Model\Article\ArticleData[]'],
            [$reflectionClass->getMethod('returnsInt'), 'int'],
            [$reflectionClass->getMethod('returnsFrontendApiProductRepository'), '\App\FrontendApi\Model\Product\ProductRepository'],
        ];
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod $reflectionMethod
     * @param string $output
     */
    #[DataProvider('getTestReplaceInMethodReturnTypeDataProvider')]
    public function testReplaceInMethodReturnType(ReflectionMethod $reflectionMethod, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceInMethodReturnType($reflectionMethod));
    }

    /**
     * @return array
     */
    public static function getTestReplaceInInPropertyTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacer::class);

        return [
            [$reflectionClass->getProperty('categoryFacadeOrNull'), '\App\Model\Category\CategoryFacade|null'],
            [$reflectionClass->getProperty('integer'), 'int'],
            [$reflectionClass->getProperty('articleDataArray'), '\App\Model\Article\ArticleData[]'],
        ];
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionProperty $reflectionProperty
     * @param string $output
     */
    #[DataProvider('getTestReplaceInInPropertyTypeDataProvider')]
    public function testReplaceInPropertyType(ReflectionProperty $reflectionProperty, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceInPropertyType($reflectionProperty));
    }

    /**
     * @return array
     */
    public static function testReplaceInParameterTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacer::class);
        $reflectionMethod = $reflectionClass->getMethod('acceptsVariousParameters');

        return [
            [$reflectionMethod->getParameter('categoryFacade'), '\App\Model\Category\CategoryFacade'],
            [$reflectionMethod->getParameter('categoryFacadeOrNull'), '\App\Model\Category\CategoryFacade|null'],
            [$reflectionMethod->getParameter('array'), '\App\Model\Article\ArticleData[]'],
            [$reflectionMethod->getParameter('frontendApiproductRepository'), '\App\FrontendApi\Model\Product\ProductRepository'],
            [$reflectionMethod->getParameter('integer'), 'int'],
        ];
    }

    /**
     * @param \Roave\BetterReflection\Reflection\ReflectionParameter $reflectionParameter
     * @param string $output
     */
    #[DataProvider('testReplaceInParameterTypeDataProvider')]
    public function testReplaceInParameterType(ReflectionParameter $reflectionParameter, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceInParameterType($reflectionParameter));
    }
}
