<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PromotedCategoriesTest extends GraphQlTestCase
{
    public function testPromotedCategoriesWithName(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedCategories = [
            ['name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Toys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
        ];

        $graphQlType = 'promotedCategories';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PromotedCategoriesQuery.graphql', [
            'firstProducts' => 1,
        ]);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertCount(count($expectedCategories), $responseData);

        foreach ($expectedCategories as $key => $expectedCategory) {
            $this->assertSame($expectedCategory['name'], $responseData[$key]['name']);
        }
    }

    public function testPromotedCategoriesReturnsSameCategoryAsCategoryDetail(): void
    {
        $graphQlType = 'promotedCategories';
        $responsePromotedCategories = $this->getResponseContentForGql(__DIR__ . '/graphql/PromotedCategoriesQuery.graphql', [
            'firstProducts' => 1,
        ]);

        $this->assertResponseContainsArrayOfDataForGraphQlType($responsePromotedCategories, $graphQlType);
        $responseDataPromotedCategories = $this->getResponseDataForGraphQlType($responsePromotedCategories, $graphQlType);

        self::assertArrayHasKey(0, $responseDataPromotedCategories, 'Response does not contain expected data');
        self::assertArrayHasKey('uuid', $responseDataPromotedCategories[0], 'Response does not contain expected data');

        $categoryUuid = $responseDataPromotedCategories[0]['uuid'];

        $graphQlType = 'category';
        $responseCategoryDetail = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryQuery.graphql', [
            'categoryUuid' => $categoryUuid,
            'firstProducts' => 1,
        ]);

        $this->assertResponseContainsArrayOfDataForGraphQlType($responseCategoryDetail, $graphQlType);
        $responseDataCategoryDetail = $this->getResponseDataForGraphQlType($responseCategoryDetail, $graphQlType);

        self::assertArrayHasKey(0, $responseDataPromotedCategories, 'Response does not contain expected data');
        self::assertEquals($responseDataPromotedCategories[0], $responseDataCategoryDetail);
    }
}
