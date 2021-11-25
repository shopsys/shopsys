<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Country;

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
                        "name": "' . t('Czech republic', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
                        "code": "CZ"
                    },
                    {
                        "name": "' . t('Slovakia', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
                        "code": "SK"
                    }
                ]
            }
        }';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
