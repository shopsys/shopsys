<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Brand;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BrandsTest extends GraphQlTestCase
{
    public function testBrands(): void
    {
        $query = '
            query {
                brands {
                    name
                }
            }
        ';

        $jsonExpected = '{
    "data": {
        "brands": [
            {
                "name": "' . t('A4tech', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Apple', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Brother', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Canon', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('DeLonghi', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Defender', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Dlink', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Genius', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Gigabyte', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('HP', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('HTC', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Hyundai', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('JURA', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('LG', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Logitech', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Microsoft', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Nikon', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Olympus', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Orava', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Philips', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('SONY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Samsung', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Sencor', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Verbatim', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
            }
        ]
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
