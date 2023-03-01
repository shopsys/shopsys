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
                products (first: 5, search: "' . t('book', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale) . '") {
                    edges {
                        node {
                            name
                        }
                    }
                }
            }
        ';

        $productsExpected = [
            ['name' => t('Book of traditional Czech fairy tales', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Book scoring system and traffic regulations', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t(
                'Book of procedures for dealing with traffic accidents',
                [],
                'dataFixtures',
                $firstDomainLocale
            )],
            ['name' => t('Book Computer for Dummies Digital Photography II', [], 'dataFixtures', $firstDomainLocale)],
            ['name' => t('Book 55 best programs for burning CDs and DVDs', [], 'dataFixtures', $firstDomainLocale)],
        ];

        $this->assertProducts($query, 'products', $productsExpected);
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
            ['name' => t('32" Philips 32PFL4308', [], 'dataFixtures', $this->getFirstDomainLocale())],
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
            ['name' => t('Canon PIXMA iP7250', [], 'dataFixtures', $this->getFirstDomainLocale())],
        ];

        $this->assertProducts($query, 'brand', $productsExpected);
    }
}
