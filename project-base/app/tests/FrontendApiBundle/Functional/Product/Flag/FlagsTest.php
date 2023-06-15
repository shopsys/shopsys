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
                "name": "' . t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Made in CZ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Made in DE', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Made in SK', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('New', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Price hit', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            }
        ]
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
