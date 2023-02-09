import { CountryFragmentApi, useCountriesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const useCountries = (): CountryFragmentApi[] | undefined => {
    const [{ data, error }] = useCountriesQueryApi();
    useQueryError(error);

    return data?.countries;
};
