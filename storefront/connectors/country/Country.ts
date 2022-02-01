import { CountryType } from 'types/country';
import { SelectOptionType } from 'types/selectOptions';
import { useCountriesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const getCountries = (): CountryType[] => {
    const [{ data, error }] = useCountriesQueryApi();
    useQueryError(error);

    if (data?.countries === undefined) {
        return [];
    }

    return data.countries;
};

export const getCountriesAsSelectOptions = (): SelectOptionType[] => {
    const countries = getCountries();

    return countries.map((country) => {
        return {
            label: country.name,
            value: country.code,
        };
    });
};
