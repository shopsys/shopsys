import { TypeCountryFragment } from 'graphql/requests/countries/fragments/CountryFragment.generated';
import { SelectOptionType } from 'types/selectOptions';

export const mapCountriesToSelectOptions = (countries: TypeCountryFragment[] | undefined): SelectOptionType[] =>
    countries?.map((country) => {
        return {
            label: country.name,
            value: country.code,
        };
    }) ?? [];
