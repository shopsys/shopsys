import { CountryFragment } from 'graphql/requests/countries/fragments/CountryFragment.generated';
import { SelectOptionType } from 'types/selectOptions';

export const mapCountriesToSelectOptions = (countries: CountryFragment[] | undefined): SelectOptionType[] =>
    countries?.map((country) => {
        return {
            label: country.name,
            value: country.code,
        };
    }) ?? [];
