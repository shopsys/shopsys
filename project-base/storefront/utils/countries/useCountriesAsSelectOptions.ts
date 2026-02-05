import { useCountriesQuery } from 'graphql/requests/countries/queries/CountriesQuery.generated';
import { mapCountriesToSelectOptions } from 'utils/mappers/country';

export const useCountriesAsSelectOptions = () => {
    const [{ data: countriesData }] = useCountriesQuery();

    return mapCountriesToSelectOptions(countriesData?.countries);
};
