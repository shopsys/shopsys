<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Brand;

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
                    ['name' => t('Defender', [], 'dataFixtures', $this->getFirstDomainLocale())],
                    ['name' => t('DeLonghi', [], 'dataFixtures', $this->getFirstDomainLocale())],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $expected);
    }
}
