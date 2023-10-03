<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Shopsys\FrameworkBundle\Component\Translation\Translator;

class ProductsByCatnumsTest extends ProductsGraphQlTestCase
{
    public function testProductsByCatnums(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/productsByCatnums.graphql',
            [
                'catnums' => [
                    '9177759',
                    '532564',
                    'non-existing', // non-existing product – should be ignored
                    '9176544M', // main variant – should be present in the result
                    '5964035', // non-visible product – should be ignored
                    '9176522', // variant product – should be present in the result
                ],
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'productsByCatnums');

        $productsExpected = [
            ['name' => t('Canon EH-22L', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
            ['name' => t('24" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
        ];

        $this->assertSameSize($productsExpected, $data);

        foreach ($data as $resultProduct) {
            $this->assertContains($resultProduct, $productsExpected);
        }
    }
}
