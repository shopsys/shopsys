import { CountryFragment } from 'graphql/requests/countries/fragments/CountryFragment.generated';
import { useCountriesQuery } from 'graphql/requests/countries/queries/CountriesQuery.generated';
import { useMemo } from 'react';
import { SelectOptionType } from 'types/selectOptions';

export const useCountriesAsSelectOptions = () => {
    const [{ data: countriesData }] = useCountriesQuery();

    return useMemo(() => mapCountriesToSelectOptions(countriesData?.countries), [countriesData?.countries]);
};

const mapCountriesToSelectOptions = (countries: CountryFragment[] | undefined): SelectOptionType[] =>
    countries?.map((country) => ({ label: country.name, value: country.code })) ?? [];
