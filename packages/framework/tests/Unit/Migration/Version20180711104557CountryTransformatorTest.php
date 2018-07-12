<?php

namespace Tests\FrameworkBundle\Unit\Migration;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Migrations\Version20180711104557CountryTransformator;

class Version20180711104557CountryTransformatorTest extends TestCase
{
    public function testMainCountries(): void
    {
        $transformator = new Version20180711104557CountryTransformator();
        $mainCountries = $transformator->getMainCountries($this->createData());

        $expected = [
            'CZ' => [
                'id' => 1,
                'code' => 'CZ',
            ],
            'SK' => [
                'id' => 2,
                'code' => 'SK',
            ],
        ];
        $this->assertSame($expected, $mainCountries);
    }

    public function testOldToNewIdsMap(): void
    {
        $transformator = new Version20180711104557CountryTransformator();
        $map = $transformator->getOldToNewIdsMap($this->createData());

        $expected = [
            1 => 1,
            2 => 2,
            3 => 1,
            4 => 2,
        ];
        $this->assertSame($expected, $map);
    }

    public function testTranslations(): void
    {
        $transformator = new Version20180711104557CountryTransformator();
        $locales = [
            1 => 'en',
            2 => 'cs',
            3 => 'de',
        ];
        $translations = $transformator->getTranslations($this->createData(), $locales);

        $expected = [
            [
                'translatable_id' => 1,
                'name' => 'Czech republic',
                'locale' => 'en',
            ],
            [
                'translatable_id' => 1,
                'name' => 'Česká republika',
                'locale' => 'cs',
            ],
            [
                'translatable_id' => 1,
                'name' => 'CZ',
                'locale' => 'de',
            ],
            [
                'translatable_id' => 2,
                'name' => 'Slovakia',
                'locale' => 'en',
            ],
            [
                'translatable_id' => 2,
                'name' => 'Slovenská republika',
                'locale' => 'cs',
            ],
            [
                'translatable_id' => 2,
                'name' => 'SK',
                'locale' => 'de',
            ],
        ];
        $this->assertSame($expected, $translations);
    }

    public function testDomainSettings(): void
    {
        $transformator = new Version20180711104557CountryTransformator();
        $countryDomains = $transformator->getCountryDomains($this->createData(), [1, 2, 3]);

        $expected = [
            [
                'country_id' => 1,
                'domain_id' => 1,
                'enabled' => 1,
                'priority' => 0,
            ],
            [
                'country_id' => 1,
                'domain_id' => 2,
                'enabled' => 1,
                'priority' => 0,
            ],
            [
                'country_id' => 1,
                'domain_id' => 3,
                'enabled' => 0,
                'priority' => 0,
            ],
            [
                'country_id' => 2,
                'domain_id' => 1,
                'enabled' => 1,
                'priority' => 0,
            ],
            [
                'country_id' => 2,
                'domain_id' => 2,
                'enabled' => 1,
                'priority' => 0,
            ],
            [
                'country_id' => 2,
                'domain_id' => 3,
                'enabled' => 0,
                'priority' => 0,
            ],
        ];
        $this->assertSame($expected, $countryDomains);
    }

    private function createData(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Czech republic',
                'domain_id' => 1,
                'code' => 'CZ',
            ],
            [
                'id' => 2,
                'name' => 'Slovakia',
                'domain_id' => 1,
                'code' => 'SK',
            ],
            [
                'id' => 3,
                'name' => 'Česká republika',
                'domain_id' => 2,
                'code' => 'CZ',
            ],
            [
                'id' => 4,
                'name' => 'Slovenská republika',
                'domain_id' => 2,
                'code' => 'SK',
            ],
        ];
    }
}
