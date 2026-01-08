import { useCountriesQuery } from 'graphql/requests/countries/queries/CountriesQuery.generated';
import { useMemo } from 'react';
import { mapCountriesToSelectOptions } from 'utils/mappers/country';

export const useCountriesAsSelectOptions = () => {
    const [{ data: countriesData }] = useCountriesQuery();

    return useMemo(() => mapCountriesToSelectOptions(countriesData?.countries), [countriesData?.countries]);
};
