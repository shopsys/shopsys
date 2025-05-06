<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorterHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class FlagsTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ArraySorterHelper $arraySorterHelper;

    public function testFlags(): void
    {
        $query = '
            query {
                flags {
                    name
                }
            }
        ';

        $flags = [
            [
                'name' => t('Action', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Made in CZ', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Made in DE', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Made in SK', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('New', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Price hit', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('name', $flags, $this->getLocaleForFirstDomain());

        $arrayExpected = [
            'data' => [
                'flags' => $flags,
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
