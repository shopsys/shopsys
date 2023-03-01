<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Country;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CountriesTest extends GraphQlTestCase
{
    public function testCountries(): void
    {
        $query = '
            query {
                countries {
                    name
                    code
                }
            }
        ';

        $jsonExpected = '{
            "data": {
                "countries": [
                    {
                        "name": "' . t('Czech republic', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                        "code": "CZ"
                    },
                    {
                        "name": "' . t('Slovakia', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                        "code": "SK"
                    }
                ]
            }
        }';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
