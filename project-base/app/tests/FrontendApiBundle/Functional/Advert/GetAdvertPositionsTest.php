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
     * @return array
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
                'description' => t('v kategorii (nad výpisem produktů)', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                'positionName' => 'productListMiddle',
            ],
            [
                'description' => t('v kategorii (mezi prvním a druhým řádkem produktů)', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                'positionName' => 'productListSecondRow',
            ],
            [
                'description' => t('nad souhrnem objednávky v košíku', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $firstDomainLocale),
                'positionName' => 'cartPreview',
            ],
        ];
    }
}
