<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

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
                "name": "' . t('Akce', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Cenový hit', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Novinka', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Vyrobeno v DE', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Vyrobeno v SK', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Vyrobeno v ČR', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Výprodej', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            }
        ]
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
