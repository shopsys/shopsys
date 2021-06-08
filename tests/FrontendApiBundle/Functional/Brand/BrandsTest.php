<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Brand;

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
                "name": "' . t('A4tech', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Apple', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Brother', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Canon', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('DeLonghi', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Defender', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Dlink', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Genius', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Gigabyte', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('HP', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('HTC', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Hyundai', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('JURA', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('LG', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Logitech', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Microsoft', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Nikon', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Olympus', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Orava', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Philips', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('SONY', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Samsung', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Sencor', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            },
            {
                "name": "' . t('Verbatim', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
            }
        ]
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
