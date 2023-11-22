<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Advert;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetAdvertPositionsTest extends GraphQlTestCase
{
    public function testGetAdvertPositions(): void
    {
        $expectedAdvertsData = $this->getExpectedAdvertPositions();

        $graphQlType = 'advertPositions';
        $response = $this->getResponseContentForQuery($this->getAllAdvertPositionsQuery());
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        self::assertCount(count($expectedAdvertsData), $responseData);

        foreach ($responseData as $advertPositionData) {
            self::assertSame(array_shift($expectedAdvertsData), $advertPositionData);
        }
    }

    /**
     * @return string
     */
    private function getAllAdvertPositionsQuery(): string
    {
        return '
            {
                advertPositions{
                    description
                    positionName
                }
            }
        ';
    }

    /**
     * @return array<int, array<'description'|'positionName', string>>
     */
    private function getExpectedAdvertPositions(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        return [
            [
                'description' => t('under heading', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                'positionName' => 'header',
            ],
            [
                'description' => t('above footer', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                'positionName' => 'footer',
            ],
            [
                'description' => t('in category (above the category name)', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                'positionName' => 'productList',
            ],
            [
                'description' => t('in category (above the product list)', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                'positionName' => 'productListMiddle',
            ],
            [
                'description' => t('in category (between first and second row of products)', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                'positionName' => 'productListSecondRow',
            ],
            [
                'description' => t('above order summary in cart', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                'positionName' => 'cartPreview',
            ],
        ];
    }
}
