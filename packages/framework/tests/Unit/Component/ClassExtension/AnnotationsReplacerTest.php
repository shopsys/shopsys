<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionObject;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;
use Shopsys\FrameworkBundle\Component\ClassExtension\DocBlockParser;
use Shopsys\FrameworkBundle\Component\ClassExtension\TypehintHelper;
use Tests\FrameworkBundle\Unit\Component\ClassExtension\Source\DummyClassForAnnotationsReplacer;

class AnnotationsReplacerTest extends TestCase
{
    private AnnotationsReplacer $annotationsReplacer;

    #[Override]
    protected function setUp(): void
    {
        $replacementMap = new AnnotationsReplacementsMap([
            'Shopsys\FrameworkBundle\Model\Category\CategoryFacade' => 'App\Model\Category\CategoryFacade',
            'Shopsys\FrameworkBundle\Model\Category\Category' => 'App\Model\Category\Category',
            'Shopsys\FrameworkBundle\Model\Product\ProductDataFactory' => 'App\Model\MyProduct\ProductDataFactory',
            'Shopsys\FrameworkBundle\Model\Article\ArticleData' => 'App\Model\Article\ArticleData',
            'Shopsys\FrontendApiBundle\Model\Product\ProductRepository' => 'App\FrontendApi\Model\Product\ProductRepository',
        ]);

        $this->annotationsReplacer = new AnnotationsReplacer($replacementMap, new DocBlockParser(), new TypehintHelper());
    }

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

    #[DataProvider('getTestReplaceAnnotationsDataProvider')]
    public function testReplaceIn(string $input, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceIn($input));
    }

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
            [$reflectionClass->getMethod('returnsArrayOfFrameworkCategories'), 'array<int, \App\Model\Category\Category>'],
        ];
    }

    #[DataProvider('getTestReplaceInMethodReturnTypeDataProvider')]
    public function testReplaceInMethodReturnType(ReflectionMethod $reflectionMethod, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceInMethodReturnType($reflectionMethod));
    }

    public static function getTestReplaceInInPropertyTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacer::class);

        return [
            [$reflectionClass->getProperty('categoryFacadeOrNull'), '\App\Model\Category\CategoryFacade|null'],
            [$reflectionClass->getProperty('integer'), 'int'],
            [$reflectionClass->getProperty('articleDataArray'), '\App\Model\Article\ArticleData[]'],
            [$reflectionClass->getProperty('arrayOfFrameworkCategories'), 'array<int, \App\Model\Category\Category>'],
        ];
    }

    #[DataProvider('getTestReplaceInInPropertyTypeDataProvider')]
    public function testReplaceInPropertyType(ReflectionProperty $reflectionProperty, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceInPropertyType($reflectionProperty));
    }

    public static function replaceInParameterTypeDataProvider(): array
    {
        $reflectionClass = ReflectionObject::createFromName(DummyClassForAnnotationsReplacer::class);
        $reflectionMethod = $reflectionClass->getMethod('acceptsVariousParameters');

        return [
            [$reflectionMethod->getParameter('categoryFacade'), '\App\Model\Category\CategoryFacade'],
            [$reflectionMethod->getParameter('categoryFacadeOrNull'), '\App\Model\Category\CategoryFacade|null'],
            [$reflectionMethod->getParameter('array'), '\App\Model\Article\ArticleData[]'],
            [$reflectionMethod->getParameter('frontendApiproductRepository'), '\App\FrontendApi\Model\Product\ProductRepository'],
            [$reflectionMethod->getParameter('integer'), 'int'],
            [$reflectionMethod->getParameter('arrayOfFrameworkCategories'), 'array<int, \App\Model\Category\Category>'],
        ];
    }

    #[DataProvider('replaceInParameterTypeDataProvider')]
    public function testReplaceInParameterType(ReflectionParameter $reflectionParameter, string $output): void
    {
        $this->assertEquals($output, $this->annotationsReplacer->replaceInParameterType($reflectionParameter));
    }
}
