<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class FlagsTest extends GraphQlTestCase
{
    public function testFlags(): void
    {
        $query = '
            query {
                flags {
                    name
                }
            }
        ';

        $jsonExpected = '{
    "data": {
        "flags": [
            {
                "name": "' . t('Akce', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Cenový hit', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Novinka', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Výprodej', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Vyrobeno v ČR', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Vyrobeno v DE', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Vyrobeno v SK', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            }
        ]
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
