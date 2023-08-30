<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class ProductsSearchTest extends ProductsGraphQlTestCase
{
    public function testSearchInAllProducts(): void
    {
        $firstDomainLocale = $this->getFirstDomainLocale();
        $query = '
            query {
                products (first: 5, search: "' . t('book', [], Translator::TESTS_TRANSLATION_DOMAIN, $firstDomainLocale) . '") {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';

        $productsExpected = [
            t('Book scoring system and traffic regulations', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('Book of traditional Czech fairy tales', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('Book Computer for Dummies Digital Photography II', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('Book of procedures for dealing with traffic accidents', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            t('Book 55 best programs for burning CDs and DVDs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
        ];

        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, 'products');
        $responseData = $this->getResponseDataForGraphQlType($response, 'products');
        $this->assertArrayHasKey('edges', $responseData);
        $this->assertCount(5, $responseData['edges']);

        foreach ($responseData['edges'] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $this->assertContains($edge['node']['name'], $productsExpected);
        }
    }

    public function testSearchInCategory(): void
    {
        /** @var \App\Model\Category\Category $categoryElectronics */
        $categoryElectronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);

        $query = '
            {
                category(uuid: "' . $categoryElectronics->getUuid() . '") { 
                    name
                    products(
                        first: 1,
                        search: "Philips"
                    ) {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                }
            }';

        $productsExpected = [
            ['name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
        ];

        $this->assertProducts($query, 'category', $productsExpected);
    }

    public function testSearchInBrand(): void
    {
        /** @var \App\Model\Product\Brand\Brand $canonBrand */
        $canonBrand = $this->getReference(BrandDataFixture::BRAND_CANON);

        $query = '
            {
                brand(uuid: "' . $canonBrand->getUuid() . '") { 
                    name
                    products(
                        first: 1,
                        search: "PIXMA"
                    ) {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                }
            }';

        $productsExpected = [
            ['name' => t('Canon PIXMA iP7250', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
        ];

        $this->assertProducts($query, 'brand', $productsExpected);
    }
}
