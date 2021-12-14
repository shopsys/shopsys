import { CountryApiType, CountryType } from 'types/country';
import { SelectOptionType } from 'components/Forms/Select/Select';
import { useCountriesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

const mapCountries = (apiData: CountryApiType[]): CountryType[] => {
    return apiData.map((country) => country);
};

export const getCountries = (): CountryType[] => {
    const [{ data, error }] = useCountriesQueryApi();
    useQueryError(error);

    if (data?.countries === undefined) {
        return [];
    }

    return mapCountries(data.countries);
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
