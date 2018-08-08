<?php

namespace Shopsys\FrameworkBundle\Model\Localization;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class Localization
{
    const DEFAULT_COLLATION = 'en_US';

    /**
     * @var string[]
     */
    private $languageNamesByLocale = [
        'cs' => 'Čeština',
        'de' => 'Deutsch',
        'en' => 'English',
        'hu' => 'Magyar',
        'pl' => 'Polski',
        'sk' => 'Slovenčina',
    ];

    /**
     * @var string[]
     */
    private $collationsByLocale = [
        'cs' => 'cs_CZ',
        'de' => 'de_DE',
        'en' => 'en_US',
        'hu' => 'hu_HU',
        'pl' => 'pl_PL',
        'sk' => 'sk_SK',
    ];

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var array
     */
    private $allLocales;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    public function getLocale(): string
    {
        return $this->domain->getLocale();
    }

    public function getAdminLocale(): string
    {
        return 'en';
    }

    public function getLocalesOfAllDomains()
    {
        if ($this->allLocales === null) {
            $this->allLocales = [];
            foreach ($this->domain->getAll() as $domainConfig) {
                $this->allLocales[$domainConfig->getLocale()] = $domainConfig->getLocale();
            }
        }

        return $this->allLocales;
    }

    /**
     * @return string[]
     */
    public function getAllDefinedCollations(): array
    {
        return $this->collationsByLocale;
    }
    
    public function getLanguageName(string $locale): string
    {
        if (!array_key_exists($locale, $this->languageNamesByLocale)) {
            throw new \Shopsys\FrameworkBundle\Model\Localization\Exception\InvalidLocaleException(
                sprintf('Locale "%s" is not valid', $locale)
            );
        }

        return $this->languageNamesByLocale[$locale];
    }
    
    public function getCollationByLocale(string $locale): string
    {
        if (array_key_exists($locale, $this->collationsByLocale)) {
            return $this->collationsByLocale[$locale];
        } else {
            return self::DEFAULT_COLLATION;
        }
    }
}
