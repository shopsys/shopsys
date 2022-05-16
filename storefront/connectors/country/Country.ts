import { useCountriesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useMemo } from 'react';
import { CountryType } from 'types/country';
import { SelectOptionType } from 'types/selectOptions';

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

    return useMemo(
        () =>
            countries.map((country) => {
                return {
                    label: country.name,
                    value: country.code,
                };
            }),
        [countries],
    );
};
