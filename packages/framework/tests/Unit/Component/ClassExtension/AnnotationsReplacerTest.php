<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\ClassExtension;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap;
use Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacer;

class AnnotationsReplacerTest extends TestCase
{
    /**
     * @return array
     */
    public function getTestReplaceAnnotationsDataProvider(): array
    {
        return [
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade',
                'output' => '@var \Shopsys\ShopBundle\Model\Category\CategoryFacade',
            ],
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface',
                'output' => '@var \Shopsys\ShopBundle\Model\MyProduct\ProductDataFactory',
            ],
            [
                'input' => '@var \Shopsys\FrameworkBundle\Model\Article\ArticleData',
                'output' => '@var \Shopsys\ShopBundle\Model\Article\ArticleData',
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
                'output' => '@param \Shopsys\ShopBundle\Model\Category\CategoryFacade',
            ],
            [
                'input' => '@return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade',
                'output' => '@return \Shopsys\ShopBundle\Model\Category\CategoryFacade',
            ],
            [
                'input' => '@return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade[]',
                'output' => '@return \Shopsys\ShopBundle\Model\Category\CategoryFacade[]',
            ],
            [
                'input' => '@return \Shopsys\FrameworkBundle\Model\Category\CategoryFacade|null',
                'output' => '@return \Shopsys\ShopBundle\Model\Category\CategoryFacade|null',
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
        $replacementMap = new AnnotationsReplacementsMap([
            'Shopsys\FrameworkBundle\Model\Category\CategoryFacade' => 'Shopsys\ShopBundle\Model\Category\CategoryFacade',
            'Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface' => 'Shopsys\ShopBundle\Model\MyProduct\ProductDataFactory',
            'Shopsys\FrameworkBundle\Model\Article\ArticleData' => 'Shopsys\ShopBundle\Model\Article\ArticleData',
        ]);

        $propertyReplacer = new AnnotationsReplacer($replacementMap);

        $this->assertEquals($output, $propertyReplacer->replaceIn($input));
    }
}
