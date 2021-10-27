import { CountryApiType, CountryType } from './types';
import { SelectOptionType } from 'components/Forms/Select/Select';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';

const countriesQuery = `
    query {
        countries {
            name
            code
        }
    }
    ` as const;

const mapCountries = (apiData: CountryApiType[]): CountryType[] => {
    return apiData.map((country) => country);
};

export const getCountries = (): CountryType[] => {
    const result = useFetchQuery({ query: countriesQuery });
    const countryApiData = result?.data?.countries;

    if (countryApiData === undefined) {
        return [];
    }

    return mapCountries(countryApiData);
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
