<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Brand;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BrandSearchTest extends GraphQlTestCase
{
    public function testBrandSearch(): void
    {
        $query = '
            query {
                brandSearch(search: "de") {
                    name
                }
            }
        ';

        $expected = [
            'data' => [
                'brandSearch' => [
                    ['name' => t('Defender', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
                    ['name' => t('DeLonghi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $expected);
    }
}
