<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SeoSettingTest extends GraphQlTestCase
{
    public function testGetSeoSettings(): void
    {
        $query = '
            query {
                settings {
                    seo {
                        title
                        titleAddOn
                        metaDescription
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $data = $this->getResponseDataForGraphQlType($response, 'settings');

        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $expectedTitle = t('Shopsys Platform - Title page', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale);
        $expectedTitleAddOn = t('| Demo eshop', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale);
        $expectedDescription = t('Shopsys Platform - the best solution for your eshop.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale);

        self::assertEquals($expectedTitle, $data['seo']['title']);
        self::assertEquals($expectedTitleAddOn, $data['seo']['titleAddOn']);
        self::assertEquals($expectedDescription, $data['seo']['metaDescription']);
    }
}
