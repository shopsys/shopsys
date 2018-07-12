<?php

namespace Shopsys\FrameworkBundle\Migrations;


class Version20180711104557CountryTransformator
{
    /**
     * @param array $countries from database
     * @return array
     */
    public function getMainCountries(array $countries): array
    {
        $mainCountries = [];
        foreach ($countries as $country) {
            $code = $country['code'];
            if (!isset($mainCountries[$code])) {
                $mainCountries[$code] = [
                    'id' => $country['id'],
                    'code' => $country['code'],
                ];
            }
        }

        return $mainCountries;
    }

    /**
     * @param array $countries from database
     * @param array $locales in form [domain_id => locale]
     * @return array
     */
    public function getTranslations(array $countries, array $locales): array
    {
        $mainCountries = $this->getMainCountries($countries);
        $preparedNames = [];
        foreach ($countries as $country) {
            $code = $country['code'];
            $domainId = $country['domain_id'];
            $name = $country['name'];
            $locale = $locales[$domainId];
            if (!isset($preparedNames[$code])) {
                $preparedNames[$code] = [];
            }
            $preparedNames[$code][$locale] = $name;
        }

        $translations = [];
        foreach ($mainCountries as $code => $mainCountry) {
            foreach ($locales as $locale) {
                $name = $preparedNames[$code][$locale] ?? $code;
                $translations[] = [
                    'translatable_id' => $mainCountry['id'],
                    'name' => $name,
                    'locale' => $locale,
                ];
            }
        }

        return $translations;
    }

    public function getCountryDomains(array $countries, array $domainIds): array
    {
        $preparedEnabled = [];
        foreach ($countries as $country) {
            $code = $country['code'];
            $domainId = $country['domain_id'];
            if (!isset($preparedEnabled[$code])) {
                $preparedEnabled[$code] = [];
            }
            $preparedEnabled[$code][$domainId] = 1;
        }

        $mainCountries = $this->getMainCountries($countries);
        $countryDomains = [];
        foreach ($mainCountries as $mainCountry) {
            $id = $mainCountry['id'];
            $code = $mainCountry['code'];
            foreach ($domainIds as $domainId) {
                $countryDomains[] = [
                    'country_id' => $id,
                    'domain_id' => $domainId,
                    'enabled' => $preparedEnabled[$code][$domainId] ?? 0,
                    'priority' => 0,
                ];
            }
        }

        return $countryDomains;
    }

    public function getOldToNewIdsMap(array $countries): array
    {
        $mainCountries = $this->getMainCountries($countries);
        $map = [];
        foreach ($countries as $country) {
            $id = $country['id'];
            $code = $country['code'];
            $map[$id] = $mainCountries[$code]['id'];
        }

        return $map;
    }
}
