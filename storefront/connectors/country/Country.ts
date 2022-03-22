import { CountryType } from 'types/country';
import { SelectOptionType } from 'types/selectOptions';
import { useCountriesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const useCountries = (): CountryType[] => {
    const [{ data, error }] = useCountriesQueryApi();
    useQueryError(error);

    if (data?.countries === undefined) {
        return [];
    }

    return data.countries;
};

export const useCountriesAsSelectOptions = (): SelectOptionType[] => {
    const countries = useCountries();

    return countries.map((country) => {
        return {
            label: country.name,
            value: country.code,
        };
    });
};
