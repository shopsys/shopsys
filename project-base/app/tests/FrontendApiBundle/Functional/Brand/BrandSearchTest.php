<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Brand;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BrandSearchTest extends GraphQlTestCase
{
    public function testBrandSearch(): void
    {
        $userIdentifier = Uuid::uuid4()->toString();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/BrandSearch.graphql', [
            'search' => 'de',
            'userIdentifier' => $userIdentifier,
        ]);

        $expected = [
            'data' => [
                'brandSearch' => [
                    ['name' => t('Defender', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
                    ['name' => t('DeLonghi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale())],
                ],
            ],
        ];

        $this->assertEquals($expected, $response);
    }
}
