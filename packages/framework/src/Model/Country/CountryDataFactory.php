<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Country;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CountryDataFactory
{
    public function __construct(protected readonly Domain $domain)
    {
    }

    protected function createInstance(): CountryData
    {
        return new CountryData();
    }

    public function create(): CountryData
    {
        $countryData = $this->createInstance();
        $this->fillNew($countryData);

        return $countryData;
    }

    public function createFromCountry(Country $country): CountryData
    {
        $countryData = $this->createInstance();
        $this->fillFromCountry($countryData, $country);

        return $countryData;
    }

    protected function fillFromCountry(CountryData $countryData, Country $country): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Country\CountryTranslation[] $translations */
        $translations = $country->getTranslations();

        foreach ($translations as $translation) {
            $countryData->names[$translation->getLocale()] = $translation->getName();
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $countryData->enabled[$domainId] = $country->isEnabled($domainId);
            $countryData->priority[$domainId] = $country->getPriority($domainId);
        }

        $countryData->code = $country->getCode();
    }

    protected function fillNew(CountryData $countryData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $countryData->enabled[$domainId] = true;
            $countryData->priority[$domainId] = null;
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $countryData->names[$locale] = null;
        }
    }
}
