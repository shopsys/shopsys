<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorter;
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

        $flags = [
            [
                'name' => t('Action', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Made in CZ', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Made in DE', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Made in SK', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('New', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Price hit', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Sale', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
        ];

        ArraySorter::sortArrayAlphabeticallyByValue('name', $flags, $this->getLocaleForFirstDomain());

        $arrayExpected = [
            'data' => [
                'flags' => $flags,
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
