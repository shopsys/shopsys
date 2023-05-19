<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaginatedProductsTest extends GraphQlTestCase
{
    public function testFirstFivePaginatedProductsAreOrderedByOrderingMode(): void
    {
        foreach ($this->getPaginatedProductsDataProvider() as $dataSet) {
            $orderingMode = $dataSet['orderingMode'];
            $expectedOrderedProducts = $dataSet['expectedOrderedProducts'];

            $query = $this->getQueryWithOrderingMode($orderingMode);
            $response = $this->getResponseContentForQuery($query);

            $graphQlType = 'products';
            $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
            $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
            $this->assertArrayHasKey('edges', $responseData);

            $queryResult = [];

            foreach ($responseData['edges'] as $edge) {
                $this->assertArrayHasKey('node', $edge);
                $queryResult[] = $edge['node'];
            }

            $this->assertEquals($expectedOrderedProducts, $queryResult, $orderingMode . ' - ' . json_encode($queryResult));
        }
    }

    private function getPaginatedProductsDataProvider()
    {
        $firstDomainLocale = $this->getFirstDomainLocale();

        return [
            [
                'orderingMode' => 'NAME_ASC',
                'expectedOrderedProducts' => [
                    ['name' => t('100 Czech crowns ticket', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('27” Hyundai T27D590EY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('27” Hyundai T27D590EZ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('30” Hyundai 22MT44D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ],
            ],
            [
                'orderingMode' => 'NAME_DESC',
                'expectedOrderedProducts' => [
                    ['name' => t(
                        'ZN-8009 steam iron Ferrato stainless steel 2200 Watt Blue',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale
                    )],
                    ['name' => t('YENKEE YSP 1005WH white', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Xtreamer SW5', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Xtreamer SW4', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Winch throttle silver VP-9711/12', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ],
            ],
            [
                'orderingMode' => 'PRICE_ASC',
                'expectedOrderedProducts' => [
                    ['name' => t('CD-R VERBATIM 210MB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Prime flour 1 kg', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t(
                        'Aquila Aquagym non-carbonated spring water',
                        [],
                        Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                        $firstDomainLocale
                    )],
                    ['name' => t('Fluorescent laces, green', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('PRIMECOOLER PC-AD2 3D glasses', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ],
            ],
            [
                'orderingMode' => 'PRICE_DESC',
                'expectedOrderedProducts' => [
                    ['name' => t('Samsung UE75HU7500 (UHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('OKI MC861cdxm', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('OKI MC861cdxn+ (01318206)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('JURA Impressa J9 TFT Carbon', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Canon EOS 700D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ],
            ],
        ];
    }

    /**
     * @param string $orderingMode
     * @return string
     */
    private function getQueryWithOrderingMode(string $orderingMode): string
    {
        return '
            query {
                products (
                    first: 5
                    orderingMode: ' . $orderingMode . '
                ) {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';
    }
}
