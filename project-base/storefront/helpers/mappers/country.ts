import { CountryFragmentApi } from 'graphql/requests/countries/fragments/CountryFragment.generated';
import { SelectOptionType } from 'types/selectOptions';

export const mapCountriesToSelectOptions = (countries: CountryFragmentApi[] | undefined): SelectOptionType[] =>
    countries?.map((country) => {
        return {
            label: country.name,
            value: country.code,
        };
    }) ?? [];
