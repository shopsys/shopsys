<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Advert;

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
     * @return array
     */
    private function getExpectedAdvertPositions(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        return [
            [
                'description' => t('under heading', [], 'messages', $firstDomainLocale),
                'positionName' => 'header',
            ],
            [
                'description' => t('above footer', [], 'messages', $firstDomainLocale),
                'positionName' => 'footer',
            ],
            [
                'description' => t('in category (above the category name)', [], 'messages', $firstDomainLocale),
                'positionName' => 'productList',
            ],
        ];
    }
}
