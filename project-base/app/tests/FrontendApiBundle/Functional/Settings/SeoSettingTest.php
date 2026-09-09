<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SeoSettingTest extends GraphQlTestCase
{
    public function testGetSeoSettings(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SeoSettingsQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'settings');

        $expectedTitleAddOn = t('| Demo eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain());

        self::assertSame($expectedTitleAddOn, $data['seo']['titleAddOn']);
    }
}
